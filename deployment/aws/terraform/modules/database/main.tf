# RDS Database Module for Purrr.love

# Random password for database master user
resource "random_password" "db_password" {
  count   = var.db_password == "" ? 1 : 0
  length  = 32
  special = true
}

# KMS key for RDS encryption
resource "aws_kms_key" "rds" {
  count                   = var.enable_encryption ? 1 : 0
  description             = "${var.environment}-${var.project_name}-rds-key"
  deletion_window_in_days = var.environment == "production" ? 30 : 7

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-rds-key"
      Environment = var.environment
      Service     = "RDS"
    }
  )
}

resource "aws_kms_alias" "rds" {
  count         = var.enable_encryption ? 1 : 0
  name          = "alias/${var.environment}-${var.project_name}-rds"
  target_key_id = aws_kms_key.rds[0].key_id
}

# Parameter group for database optimization
resource "aws_db_parameter_group" "main" {
  family = var.db_engine == "mysql" ? "mysql8.0" : "postgres15"
  name   = "${var.environment}-${var.project_name}-${var.db_engine}-params"

  dynamic "parameter" {
    for_each = var.db_engine == "mysql" ? var.mysql_parameters : var.postgres_parameters
    content {
      name  = parameter.value.name
      value = parameter.value.value
    }
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-${var.db_engine}-params"
      Environment = var.environment
      Service     = "RDS"
    }
  )

  lifecycle {
    create_before_destroy = true
  }
}

# Option group for additional features
resource "aws_db_option_group" "main" {
  count                    = var.db_engine == "mysql" ? 1 : 0
  name                     = "${var.environment}-${var.project_name}-mysql-options"
  option_group_description = "Option group for ${var.project_name} MySQL database"
  engine_name              = "mysql"
  major_engine_version     = "8.0"

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-mysql-options"
      Environment = var.environment
      Service     = "RDS"
    }
  )

  lifecycle {
    create_before_destroy = true
  }
}

# CloudWatch log groups for database logs
resource "aws_cloudwatch_log_group" "database_logs" {
  for_each          = toset(var.enabled_cloudwatch_logs_exports)
  name              = "/aws/rds/instance/${var.environment}-${var.project_name}-db/${each.key}"
  retention_in_days = var.log_retention_days

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-db-${each.key}-logs"
      Environment = var.environment
      Service     = "CloudWatch"
    }
  )
}

# RDS instance
resource "aws_db_instance" "main" {
  # Basic Configuration
  identifier     = "${var.environment}-${var.project_name}-db"
  engine         = var.db_engine
  engine_version = var.db_engine_version
  instance_class = var.db_instance_class

  # Database Configuration
  allocated_storage     = var.db_allocated_storage
  max_allocated_storage = var.db_max_allocated_storage
  storage_type          = var.storage_type
  storage_encrypted     = var.enable_encryption
  kms_key_id            = var.enable_encryption ? aws_kms_key.rds[0].arn : null

  # Database Details
  db_name  = var.db_name
  username = var.db_username
  password = var.db_password != "" ? var.db_password : random_password.db_password[0].result

  # Network Configuration
  db_subnet_group_name   = var.db_subnet_group_name
  vpc_security_group_ids = var.security_group_ids
  publicly_accessible    = var.publicly_accessible
  port                   = var.database_port

  # Parameter and Option Groups
  parameter_group_name = aws_db_parameter_group.main.name
  option_group_name    = var.db_engine == "mysql" ? aws_db_option_group.main[0].name : null

  # Backup Configuration
  backup_retention_period   = var.backup_retention_period
  backup_window            = var.backup_window
  delete_automated_backups = var.environment != "production"
  copy_tags_to_snapshot    = true

  # Maintenance Configuration
  maintenance_window         = var.maintenance_window
  auto_minor_version_upgrade = var.auto_minor_version_upgrade
  allow_major_version_upgrade = false

  # Monitoring Configuration
  monitoring_interval = var.enhanced_monitoring_interval
  monitoring_role_arn = var.enhanced_monitoring_interval > 0 ? aws_iam_role.rds_enhanced_monitoring[0].arn : null

  # Performance Insights
  performance_insights_enabled          = var.enable_performance_insights
  performance_insights_retention_period = var.enable_performance_insights ? var.performance_insights_retention_period : null

  # Logging
  enabled_cloudwatch_logs_exports = var.enabled_cloudwatch_logs_exports

  # Deletion Protection
  deletion_protection = var.environment == "production" ? true : var.enable_deletion_protection
  skip_final_snapshot = var.environment == "production" ? false : var.skip_final_snapshot
  final_snapshot_identifier = var.skip_final_snapshot ? null : "${var.environment}-${var.project_name}-final-snapshot-${formatdate("YYYY-MM-DD-hhmm", timestamp())}"

  # Multi-AZ for production
  multi_az = var.environment == "production" ? true : var.multi_az

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-database"
      Environment = var.environment
      Service     = "RDS"
      Engine      = var.db_engine
      Version     = var.db_engine_version
    }
  )

  lifecycle {
    prevent_destroy = true
    ignore_changes = [
      password,
      final_snapshot_identifier,
    ]
  }

  depends_on = [
    aws_cloudwatch_log_group.database_logs
  ]
}

# Enhanced monitoring IAM role
resource "aws_iam_role" "rds_enhanced_monitoring" {
  count = var.enhanced_monitoring_interval > 0 ? 1 : 0
  name  = "${var.environment}-${var.project_name}-rds-enhanced-monitoring"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "monitoring.rds.amazonaws.com"
        }
      }
    ]
  })

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-rds-enhanced-monitoring"
      Environment = var.environment
      Service     = "IAM"
    }
  )
}

resource "aws_iam_role_policy_attachment" "rds_enhanced_monitoring" {
  count      = var.enhanced_monitoring_interval > 0 ? 1 : 0
  role       = aws_iam_role.rds_enhanced_monitoring[0].name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonRDSEnhancedMonitoringRole"
}

# Read replica (optional)
resource "aws_db_instance" "read_replica" {
  count = var.create_read_replica ? 1 : 0

  identifier             = "${var.environment}-${var.project_name}-db-replica"
  replicate_source_db    = aws_db_instance.main.id
  instance_class         = var.read_replica_instance_class
  publicly_accessible    = false
  auto_minor_version_upgrade = var.auto_minor_version_upgrade

  # Monitoring
  monitoring_interval = var.enhanced_monitoring_interval
  monitoring_role_arn = var.enhanced_monitoring_interval > 0 ? aws_iam_role.rds_enhanced_monitoring[0].arn : null

  # Performance Insights
  performance_insights_enabled          = var.enable_performance_insights
  performance_insights_retention_period = var.enable_performance_insights ? var.performance_insights_retention_period : null

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-database-replica"
      Environment = var.environment
      Service     = "RDS"
      Type        = "ReadReplica"
    }
  )
}

# Database subnet group (if not provided)
resource "aws_db_subnet_group" "main" {
  count      = var.db_subnet_group_name == "" ? 1 : 0
  name       = "${var.environment}-${var.project_name}-db-subnet-group"
  subnet_ids = var.subnet_ids

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-db-subnet-group"
      Environment = var.environment
      Service     = "RDS"
    }
  )
}

# Store database credentials in AWS Systems Manager Parameter Store
resource "aws_ssm_parameter" "db_password" {
  count = var.store_credentials_in_ssm ? 1 : 0
  name  = "/${var.environment}/${var.project_name}/database/password"
  type  = "SecureString"
  value = var.db_password != "" ? var.db_password : random_password.db_password[0].result

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-db-password"
      Environment = var.environment
      Service     = "SSM"
    }
  )
}

resource "aws_ssm_parameter" "db_endpoint" {
  count = var.store_credentials_in_ssm ? 1 : 0
  name  = "/${var.environment}/${var.project_name}/database/endpoint"
  type  = "String"
  value = aws_db_instance.main.endpoint

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-db-endpoint"
      Environment = var.environment
      Service     = "SSM"
    }
  )
}
