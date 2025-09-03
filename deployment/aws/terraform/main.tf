# 🚀 Purrr.love AWS Infrastructure
# Production-ready Terraform configuration for containerized deployment

terraform {
  required_version = ">= 1.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
    random = {
      source  = "hashicorp/random"
      version = "~> 3.1"
    }
  }
  
  backend "s3" {
    bucket = "purrr-love-terraform-state"
    key    = "terraform.tfstate"
    region = "us-east-1"
  }
}

# Default AWS provider
provider "aws" {
  region = var.aws_region
  
  default_tags {
    tags = {
      Project     = "purrr"
      Environment = var.environment
      ManagedBy   = "terraform"
      Owner       = "purrr-love-team"
      CreatedBy   = "terraform"
    }
  }
}

# US East 1 provider for CloudFront and ACM certificates
provider "aws" {
  alias  = "us_east_1"
  region = "us-east-1"
  
  default_tags {
    tags = {
      Project     = "purrr"
      Environment = var.environment
      ManagedBy   = "terraform"
      Owner       = "purrr-love-team"
      CreatedBy   = "terraform"
    }
  }
}

# Common tags for all resources
locals {
  common_tags = {
    Project     = "purrr"
    Environment = var.environment
    ManagedBy   = "terraform"
    Owner       = "purrr-love-team"
    CreatedBy   = "terraform"
  }
}

# VPC and Networking
module "vpc" {
  source = "./modules/vpc"
  
  environment    = var.environment
  project_name   = var.project_name
  vpc_cidr_block = var.vpc_cidr_block
  availability_zones = var.availability_zones
  
  public_subnet_cidrs   = var.public_subnet_cidrs
  private_subnet_cidrs  = var.private_subnet_cidrs
  database_subnet_cidrs = var.database_subnet_cidrs
  
  enable_nat_gateway = var.enable_nat_gateway
  single_nat_gateway = var.single_nat_gateway
  enable_vpc_endpoints = var.enable_vpc_endpoints
  enable_flow_logs   = var.enable_flow_logs
  
  common_tags = local.common_tags
}

# Security Groups
module "security_groups" {
  source = "./modules/security_groups"
  
  environment      = var.environment
  project_name     = var.project_name
  vpc_id          = module.vpc.vpc_id
  vpc_cidr_block  = var.vpc_cidr_block
  
  application_port     = var.application_port
  database_port        = var.database_port
  admin_cidr_blocks    = var.admin_cidr_blocks
  
  enable_redis         = var.enable_redis
  enable_bastion       = var.enable_bastion
  enable_efs           = var.enable_efs
  enable_vpc_endpoints = var.enable_vpc_endpoints
  
  common_tags = local.common_tags
}

# RDS Database
module "database" {
  source = "./modules/database"
  
  environment   = var.environment
  project_name  = var.project_name
  
  # Database Configuration
  db_engine         = var.db_engine
  db_engine_version = var.db_engine_version
  db_instance_class = var.db_instance_class
  db_name          = var.db_name
  db_username      = var.db_username
  db_password      = var.db_password
  database_port    = var.database_port
  
  # Storage
  db_allocated_storage     = var.db_allocated_storage
  db_max_allocated_storage = var.db_max_allocated_storage
  enable_encryption        = var.db_enable_encryption
  
  # Network
  subnet_ids         = module.vpc.database_subnet_ids
  security_group_ids = [module.security_groups.database_security_group_id]
  
  # Backup and Maintenance
  backup_retention_period = var.db_backup_retention_period
  backup_window          = var.db_backup_window
  maintenance_window     = var.db_maintenance_window
  
  # High Availability
  multi_az             = var.environment == "production" ? true : var.db_multi_az
  create_read_replica  = var.db_create_read_replica
  
  # Monitoring
  enhanced_monitoring_interval     = var.db_enhanced_monitoring_interval
  enable_performance_insights      = var.db_enable_performance_insights
  
  common_tags = local.common_tags
}

# ECS Cluster and Service
module "ecs" {
  source = "./modules/ecs"
  
  environment  = var.environment
  project_name = var.project_name
  
  # Container Configuration
  container_image  = var.container_image
  container_port   = var.container_port
  container_cpu    = var.container_cpu
  container_memory = var.container_memory
  
  # Task Configuration
  task_cpu    = var.task_cpu
  task_memory = var.task_memory
  
  # Service Configuration
  desired_count = var.ecs_desired_count
  
  # Network Configuration
  subnet_ids         = var.ecs_use_public_subnets ? module.vpc.public_subnet_ids : module.vpc.private_subnet_ids
  security_group_ids = [module.security_groups.web_security_group_id]
  assign_public_ip   = var.ecs_assign_public_ip
  
  # Load Balancer Integration
  target_group_arn = module.alb.target_group_arn
  
  # Auto Scaling
  enable_auto_scaling      = var.ecs_enable_auto_scaling
  autoscaling_min_capacity = var.ecs_autoscaling_min_capacity
  autoscaling_max_capacity = var.ecs_autoscaling_max_capacity
  
  # Fargate Configuration
  enable_fargate     = var.ecs_enable_fargate
  use_fargate_spot   = var.ecs_use_fargate_spot
  
  # Monitoring
  enable_container_insights = var.ecs_enable_container_insights
  log_retention_days       = var.log_retention_days
  
  common_tags = local.common_tags
  
  depends_on = [module.database]
}

# Application Load Balancer
module "alb" {
  source = "./modules/alb"
  
  environment  = var.environment
  project_name = var.project_name
  
  # Network Configuration
  vpc_id             = module.vpc.vpc_id
  subnet_ids         = module.vpc.public_subnet_ids
  security_group_ids = [module.security_groups.alb_security_group_id]
  
  # Target Configuration
  target_port = var.container_port
  target_type = "ip"
  
  # SSL Configuration
  certificate_arn = var.certificate_arn
  ssl_policy     = var.ssl_policy
  
  # Health Check
  health_check_path = var.health_check_path
  
  # Load Balancer Settings
  enable_deletion_protection = var.environment == "production" ? true : var.alb_enable_deletion_protection
  enable_access_logs        = var.alb_enable_access_logs
  access_logs_bucket        = var.alb_access_logs_bucket
  
  common_tags = local.common_tags
}

# Outputs
output "vpc_id" {
  description = "VPC ID"
  value       = module.vpc.vpc_id
}

output "public_subnets" {
  description = "Public subnet IDs"
  value       = module.vpc.public_subnets
}

output "private_subnets" {
  description = "Private subnet IDs"
  value       = module.vpc.private_subnets
}

output "ecs_cluster_name" {
  description = "ECS cluster name"
  value       = module.ecs.cluster_name
}

output "alb_dns_name" {
  description = "ALB DNS name"
  value       = module.alb.dns_name
}

output "rds_endpoint" {
  description = "RDS endpoint"
  value       = module.database.endpoint
}

output "cloudfront_domain" {
  description = "CloudFront domain name"
  value       = module.storage.cloudfront_domain_name
}

output "app_url" {
  description = "Application URL"
  value       = "https://${var.domain_name}"
}
