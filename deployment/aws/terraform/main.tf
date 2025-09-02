# 🚀 Purrr.love AWS Infrastructure
# Terraform configuration for containerized deployment

terraform {
  required_version = ">= 1.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
  
  backend "s3" {
    bucket = "purrr-love-terraform-state"
    key    = "terraform.tfstate"
    region = "us-east-1"
  }
}

provider "aws" {
  region = var.aws_region
  
  default_tags {
    tags = {
      Project     = "purrr-love"
      Environment = var.environment
      ManagedBy   = "terraform"
      Owner       = "purrr-love-team"
    }
  }
}

# VPC and Networking
module "vpc" {
  source = "./modules/vpc"
  
  environment = var.environment
  vpc_cidr   = var.vpc_cidr
  azs         = var.availability_zones
  
  public_subnets  = var.public_subnet_cidrs
  private_subnets = var.private_subnet_cidrs
  
  enable_nat_gateway = true
  single_nat_gateway = var.environment != "production"
}

# Security Groups
module "security_groups" {
  source = "./modules/security_groups"
  
  vpc_id = module.vpc.vpc_id
  environment = var.environment
}

# RDS Database
module "database" {
  source = "./modules/database"
  
  environment     = var.environment
  vpc_id         = module.vpc.vpc_id
  private_subnets = module.vpc.private_subnets
  security_group_id = module.security_groups.database_security_group_id
  
  db_instance_class = var.database_instance_class
  db_allocated_storage = var.database_allocated_storage
  db_username = var.database_username
  db_password = var.database_password
}

# ECS Cluster
module "ecs" {
  source = "./modules/ecs"
  
  environment = var.environment
  vpc_id     = module.vpc.vpc_id
  
  public_subnets  = module.vpc.public_subnets
  private_subnets = module.vpc.private_subnets
  
  security_group_id = module.security_groups.ecs_security_group_id
  
  app_image = var.app_image
  app_port  = var.app_port
  app_count = var.app_count
  
  depends_on = [module.database]
}

# Application Load Balancer
module "alb" {
  source = "./modules/alb"
  
  environment = var.environment
  vpc_id     = module.vpc.vpc_id
  
  public_subnets = module.vpc.public_subnets
  security_group_id = module.security_groups.alb_security_group_id
  
  app_port = var.app_port
}

# S3 and CloudFront
module "storage" {
  source = "./modules/storage"
  
  environment = var.environment
  domain_name = var.domain_name
}

# ElastiCache Redis
module "redis" {
  source = "./modules/redis"
  
  environment = var.environment
  vpc_id     = module.vpc.vpc_id
  
  private_subnets = module.vpc.private_subnets
  security_group_id = module.security_groups.redis_security_group_id
  
  node_type = var.redis_node_type
  num_cache_nodes = var.redis_num_cache_nodes
}

# CloudWatch Monitoring
module "monitoring" {
  source = "./modules/monitoring"
  
  environment = var.environment
  ecs_cluster_name = module.ecs.cluster_name
  rds_instance_id = module.database.instance_id
}

# Route53 DNS
module "dns" {
  source = "./modules/dns"
  
  environment = var.environment
  domain_name = var.domain_name
  
  alb_dns_name = module.alb.dns_name
  alb_zone_id = module.alb.zone_id
  
  cloudfront_domain_name = module.storage.cloudfront_domain_name
  cloudfront_zone_id = module.storage.cloudfront_zone_id
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
