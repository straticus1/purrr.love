# Database Module Outputs

# RDS Instance Outputs
output "db_instance_id" {
  description = "RDS instance ID"
  value       = aws_db_instance.main.id
}

output "db_instance_arn" {
  description = "RDS instance ARN"
  value       = aws_db_instance.main.arn
}

output "db_instance_endpoint" {
  description = "RDS instance endpoint"
  value       = aws_db_instance.main.endpoint
  sensitive   = true
}

output "db_instance_hosted_zone_id" {
  description = "RDS instance hosted zone ID"
  value       = aws_db_instance.main.hosted_zone_id
}

output "db_instance_port" {
  description = "RDS instance port"
  value       = aws_db_instance.main.port
}

output "db_instance_status" {
  description = "RDS instance status"
  value       = aws_db_instance.main.status
}

# Database Configuration
output "db_instance_name" {
  description = "Database name"
  value       = aws_db_instance.main.db_name
}

output "db_instance_username" {
  description = "Database username"
  value       = aws_db_instance.main.username
  sensitive   = true
}

output "db_instance_engine" {
  description = "Database engine"
  value       = aws_db_instance.main.engine
}

output "db_instance_engine_version" {
  description = "Database engine version"
  value       = aws_db_instance.main.engine_version
}

output "db_instance_class" {
  description = "Database instance class"
  value       = aws_db_instance.main.instance_class
}

# Connection Information
output "db_connection_string" {
  description = "Database connection string template"
  value       = "${var.db_engine}://${aws_db_instance.main.username}:PASSWORD@${aws_db_instance.main.endpoint}/${aws_db_instance.main.db_name}"
  sensitive   = true
}

# Read Replica Outputs
output "db_replica_id" {
  description = "RDS read replica instance ID"
  value       = var.create_read_replica ? aws_db_instance.read_replica[0].id : null
}

output "db_replica_arn" {
  description = "RDS read replica instance ARN"
  value       = var.create_read_replica ? aws_db_instance.read_replica[0].arn : null
}

output "db_replica_endpoint" {
  description = "RDS read replica endpoint"
  value       = var.create_read_replica ? aws_db_instance.read_replica[0].endpoint : null
  sensitive   = true
}

# Subnet Group
output "db_subnet_group_id" {
  description = "DB subnet group ID"
  value       = var.db_subnet_group_name != "" ? var.db_subnet_group_name : aws_db_subnet_group.main[0].id
}

output "db_subnet_group_arn" {
  description = "DB subnet group ARN"
  value       = var.db_subnet_group_name != "" ? null : aws_db_subnet_group.main[0].arn
}

# Parameter and Option Groups
output "db_parameter_group_id" {
  description = "DB parameter group ID"
  value       = aws_db_parameter_group.main.id
}

output "db_parameter_group_arn" {
  description = "DB parameter group ARN"
  value       = aws_db_parameter_group.main.arn
}

output "db_option_group_id" {
  description = "DB option group ID"
  value       = var.db_engine == "mysql" ? aws_db_option_group.main[0].id : null
}

output "db_option_group_arn" {
  description = "DB option group ARN"
  value       = var.db_engine == "mysql" ? aws_db_option_group.main[0].arn : null
}

# Security
output "db_kms_key_id" {
  description = "KMS key ID for RDS encryption"
  value       = var.enable_encryption ? aws_kms_key.rds[0].id : null
}

output "db_kms_key_arn" {
  description = "KMS key ARN for RDS encryption"
  value       = var.enable_encryption ? aws_kms_key.rds[0].arn : null
}

# Monitoring
output "db_enhanced_monitoring_arn" {
  description = "Enhanced monitoring IAM role ARN"
  value       = var.enhanced_monitoring_interval > 0 ? aws_iam_role.rds_enhanced_monitoring[0].arn : null
}

output "db_cloudwatch_log_groups" {
  description = "CloudWatch log groups for database logs"
  value       = { for k, v in aws_cloudwatch_log_group.database_logs : k => v.arn }
}

# Storage Information
output "db_allocated_storage" {
  description = "Allocated storage size"
  value       = aws_db_instance.main.allocated_storage
}

output "db_storage_type" {
  description = "Storage type"
  value       = aws_db_instance.main.storage_type
}

output "db_storage_encrypted" {
  description = "Whether storage is encrypted"
  value       = aws_db_instance.main.storage_encrypted
}

# Backup Information
output "db_backup_retention_period" {
  description = "Backup retention period"
  value       = aws_db_instance.main.backup_retention_period
}

output "db_backup_window" {
  description = "Backup window"
  value       = aws_db_instance.main.backup_window
}

output "db_maintenance_window" {
  description = "Maintenance window"
  value       = aws_db_instance.main.maintenance_window
}

# High Availability
output "db_multi_az" {
  description = "Whether Multi-AZ is enabled"
  value       = aws_db_instance.main.multi_az
}

output "db_availability_zone" {
  description = "Availability zone of the instance"
  value       = aws_db_instance.main.availability_zone
}

# Performance Insights
output "db_performance_insights_enabled" {
  description = "Whether Performance Insights is enabled"
  value       = aws_db_instance.main.performance_insights_enabled
}

# Systems Manager Parameters
output "db_ssm_password_parameter" {
  description = "SSM parameter name for database password"
  value       = var.store_credentials_in_ssm ? aws_ssm_parameter.db_password[0].name : null
}

output "db_ssm_endpoint_parameter" {
  description = "SSM parameter name for database endpoint"
  value       = var.store_credentials_in_ssm ? aws_ssm_parameter.db_endpoint[0].name : null
}

# Summary Information
output "database_info" {
  description = "Database information summary"
  value = {
    instance_id       = aws_db_instance.main.id
    endpoint         = aws_db_instance.main.endpoint
    port             = aws_db_instance.main.port
    database_name    = aws_db_instance.main.db_name
    engine           = aws_db_instance.main.engine
    engine_version   = aws_db_instance.main.engine_version
    instance_class   = aws_db_instance.main.instance_class
    multi_az         = aws_db_instance.main.multi_az
    storage_encrypted = aws_db_instance.main.storage_encrypted
    backup_retention = aws_db_instance.main.backup_retention_period
    has_read_replica = var.create_read_replica
  }
  sensitive = true
}
