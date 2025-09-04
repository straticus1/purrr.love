# Purrr.love Production Configuration
# Terraform variables for AWS deployment

# Project Configuration
project_name = "purrr"
environment  = "production"
aws_region   = "us-east-1"

# Domain Configuration
domain_name = "purrr.love"

# Network Configuration
vpc_cidr_block = "10.0.0.0/16"
availability_zones = ["us-east-1a", "us-east-1b", "us-east-1c"]

public_subnet_cidrs   = ["10.0.1.0/24", "10.0.2.0/24", "10.0.3.0/24"]
private_subnet_cidrs  = ["10.0.11.0/24", "10.0.12.0/24", "10.0.13.0/24"]
database_subnet_cidrs = ["10.0.21.0/24", "10.0.22.0/24", "10.0.23.0/24"]

# VPC Settings
enable_nat_gateway   = true
single_nat_gateway   = false  # Use multiple NAT gateways for production HA
enable_vpc_endpoints = true
enable_flow_logs     = true

# Database Configuration
db_engine           = "postgres"
db_engine_version   = "15.4"
db_instance_class   = "db.t3.small"  # Production appropriate size
db_name            = "purrr_love"
db_username        = "purrr_admin"
# db_password should be set via environment variable TF_VAR_db_password
database_port      = 5432

db_allocated_storage     = 100
db_max_allocated_storage = 1000
db_enable_encryption     = true

# Backup settings
db_backup_retention_period = 7
db_backup_window          = "03:00-04:00"
db_maintenance_window     = "sun:04:00-sun:05:00"

# High Availability
db_multi_az           = true
db_create_read_replica = false  # Enable if needed for read scaling

# Monitoring
db_enhanced_monitoring_interval = 60
db_enable_performance_insights  = true

# ECS Configuration
container_image  = "purrr-love/app:latest"
container_port   = 80
container_cpu    = 256
container_memory = 512

task_cpu    = 512
task_memory = 1024

ecs_desired_count = 2

# Network settings for ECS
ecs_use_public_subnets = false  # Use private subnets for security
ecs_assign_public_ip   = false

# Auto Scaling
ecs_enable_auto_scaling      = true
ecs_autoscaling_min_capacity = 2
ecs_autoscaling_max_capacity = 10

# Fargate Configuration
ecs_enable_fargate   = true
ecs_use_fargate_spot = false  # Use regular Fargate for production stability

# Monitoring
ecs_enable_container_insights = true
log_retention_days           = 30

# ALB Configuration
# certificate_arn should be set after creating ACM certificate
ssl_policy = "ELBSecurityPolicy-TLS-1-2-2017-01"

# Health Check
health_check_path = "/health"

# ALB Settings
alb_enable_deletion_protection = true
alb_enable_access_logs        = true
# alb_access_logs_bucket should be set to S3 bucket for ALB logs

# Security
application_port = 80
admin_cidr_blocks = ["10.0.0.0/16"]  # Restrict admin access to VPC

# Optional services
enable_redis         = true
enable_bastion       = false  # Disable for production unless needed
enable_efs           = false

# Route53 and DNS
create_route53_hosted_zone    = true
enable_route53_health_checks  = true
enable_email                  = false  # Enable if email service is needed
