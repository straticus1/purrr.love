# Security Groups Module for Purrr.love

# Application Load Balancer Security Group
resource "aws_security_group" "alb" {
  name_prefix = "${var.environment}-${var.project_name}-alb-"
  vpc_id      = var.vpc_id

  # HTTP from anywhere
  ingress {
    description = "HTTP"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # HTTPS from anywhere
  ingress {
    description = "HTTPS"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # All outbound traffic
  egress {
    description = "All outbound traffic"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(
    var.common_tags,
    {
      Name = "${var.environment}-${var.project_name}-alb-sg"
      Type = "LoadBalancer"
    }
  )
}

# ECS/Web Application Security Group
resource "aws_security_group" "web" {
  name_prefix = "${var.environment}-${var.project_name}-web-"
  vpc_id      = var.vpc_id

  # HTTP from ALB
  ingress {
    description     = "HTTP from ALB"
    from_port       = 80
    to_port         = 80
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id]
  }

  # HTTPS from ALB
  ingress {
    description     = "HTTPS from ALB"
    from_port       = 443
    to_port         = 443
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id]
  }

  # Custom application port from ALB
  ingress {
    description     = "Application port from ALB"
    from_port       = var.application_port
    to_port         = var.application_port
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id]
  }

  # SSH from admin CIDRs (if provided)
  dynamic "ingress" {
    for_each = length(var.admin_cidr_blocks) > 0 ? [1] : []
    content {
      description = "SSH from admin networks"
      from_port   = 22
      to_port     = 22
      protocol    = "tcp"
      cidr_blocks = var.admin_cidr_blocks
    }
  }

  # All outbound traffic
  egress {
    description = "All outbound traffic"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(
    var.common_tags,
    {
      Name = "${var.environment}-${var.project_name}-web-sg"
      Type = "WebApplication"
    }
  )
}

# RDS Database Security Group
resource "aws_security_group" "database" {
  name_prefix = "${var.environment}-${var.project_name}-db-"
  vpc_id      = var.vpc_id

  # MySQL/PostgreSQL from web servers
  ingress {
    description     = "Database access from web"
    from_port       = var.database_port
    to_port         = var.database_port
    protocol        = "tcp"
    security_groups = [aws_security_group.web.id]
  }

  # Database access from bastion (if enabled)
  dynamic "ingress" {
    for_each = var.enable_bastion && length(var.admin_cidr_blocks) > 0 ? [1] : []
    content {
      description = "Database access from bastion"
      from_port   = var.database_port
      to_port     = var.database_port
      protocol    = "tcp"
      cidr_blocks = var.admin_cidr_blocks
    }
  }

  # All outbound traffic (for updates, etc.)
  egress {
    description = "All outbound traffic"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(
    var.common_tags,
    {
      Name = "${var.environment}-${var.project_name}-db-sg"
      Type = "Database"
    }
  )
}

# ElastiCache Redis Security Group
resource "aws_security_group" "redis" {
  count       = var.enable_redis ? 1 : 0
  name_prefix = "${var.environment}-${var.project_name}-redis-"
  vpc_id      = var.vpc_id

  # Redis from web servers
  ingress {
    description     = "Redis access from web"
    from_port       = 6379
    to_port         = 6379
    protocol        = "tcp"
    security_groups = [aws_security_group.web.id]
  }

  # All outbound traffic
  egress {
    description = "All outbound traffic"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(
    var.common_tags,
    {
      Name = "${var.environment}-${var.project_name}-redis-sg"
      Type = "Cache"
    }
  )
}

# Bastion Host Security Group (optional)
resource "aws_security_group" "bastion" {
  count       = var.enable_bastion ? 1 : 0
  name_prefix = "${var.environment}-${var.project_name}-bastion-"
  vpc_id      = var.vpc_id

  # SSH from admin networks
  ingress {
    description = "SSH from admin networks"
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = var.admin_cidr_blocks
  }

  # All outbound traffic
  egress {
    description = "All outbound traffic"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(
    var.common_tags,
    {
      Name = "${var.environment}-${var.project_name}-bastion-sg"
      Type = "BastionHost"
    }
  )
}

# EFS Security Group (for shared storage)
resource "aws_security_group" "efs" {
  count       = var.enable_efs ? 1 : 0
  name_prefix = "${var.environment}-${var.project_name}-efs-"
  vpc_id      = var.vpc_id

  # NFS from web servers
  ingress {
    description     = "NFS access from web"
    from_port       = 2049
    to_port         = 2049
    protocol        = "tcp"
    security_groups = [aws_security_group.web.id]
  }

  # All outbound traffic
  egress {
    description = "All outbound traffic"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(
    var.common_tags,
    {
      Name = "${var.environment}-${var.project_name}-efs-sg"
      Type = "SharedStorage"
    }
  )
}

# VPC Endpoints Security Group
resource "aws_security_group" "vpc_endpoints" {
  count       = var.enable_vpc_endpoints ? 1 : 0
  name_prefix = "${var.environment}-${var.project_name}-vpce-"
  vpc_id      = var.vpc_id

  # HTTPS from VPC CIDR
  ingress {
    description = "HTTPS from VPC"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = [var.vpc_cidr_block]
  }

  # All outbound traffic
  egress {
    description = "All outbound traffic"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = merge(
    var.common_tags,
    {
      Name = "${var.environment}-${var.project_name}-vpce-sg"
      Type = "VPCEndpoints"
    }
  )
}
