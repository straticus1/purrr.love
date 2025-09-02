# 🚀 Purrr.love AWS Infrastructure Variables

# Environment Configuration
variable "environment" {
  description = "Environment name (dev, staging, production)"
  type        = string
  default     = "production"
  
  validation {
    condition     = contains(["dev", "staging", "production"], var.environment)
    error_message = "Environment must be dev, staging, or production."
  }
}

# AWS Configuration
variable "aws_region" {
  description = "AWS region for deployment"
  type        = string
  default     = "us-east-1"
}

# VPC Configuration
variable "vpc_cidr" {
  description = "CIDR block for VPC"
  type        = string
  default     = "10.0.0.0/16"
}

variable "availability_zones" {
  description = "Availability zones"
  type        = list(string)
  default     = ["us-east-1a", "us-east-1b", "us-east-1c"]
}

variable "public_subnet_cidrs" {
  description = "CIDR blocks for public subnets"
  type        = list(string)
  default     = ["10.0.1.0/24", "10.0.2.0/24", "10.0.3.0/24"]
}

variable "private_subnet_cidrs" {
  description = "CIDR blocks for private subnets"
  type        = list(string)
  default     = ["10.0.11.0/24", "10.0.12.0/24", "10.0.13.0/24"]
}

# Application Configuration
variable "app_image" {
  description = "Docker image for the application"
  type        = string
  default     = "purrr-love/app:latest"
}

variable "app_port" {
  description = "Port the application listens on"
  type        = number
  default     = 80
}

variable "app_count" {
  description = "Number of application instances"
  type        = number
  default     = 2
  
  validation {
    condition     = var.app_count >= 1 && var.app_count <= 10
    error_message = "App count must be between 1 and 10."
  }
}

# Database Configuration
variable "database_instance_class" {
  description = "RDS instance class"
  type        = string
  default     = "db.t3.micro"
}

variable "database_allocated_storage" {
  description = "RDS allocated storage in GB"
  type        = number
  default     = 20
  
  validation {
    condition     = var.database_allocated_storage >= 20 && var.database_allocated_storage <= 1000
    error_message = "Database storage must be between 20 and 1000 GB."
  }
}

variable "database_username" {
  description = "Database master username"
  type        = string
  default     = "purrr_admin"
  
  validation {
    condition     = length(var.database_username) >= 3 && length(var.database_username) <= 16
    error_message = "Database username must be between 3 and 16 characters."
  }
}

variable "database_password" {
  description = "Database master password"
  type        = string
  sensitive   = true
  
  validation {
    condition     = length(var.database_password) >= 8
    error_message = "Database password must be at least 8 characters long."
  }
}

# Redis Configuration
variable "redis_node_type" {
  description = "ElastiCache node type"
  type        = string
  default     = "cache.t3.micro"
}

variable "redis_num_cache_nodes" {
  description = "Number of cache nodes"
  type        = number
  default     = 1
  
  validation {
    condition     = var.redis_num_cache_nodes >= 1 && var.redis_num_cache_nodes <= 5
    error_message = "Redis cache nodes must be between 1 and 5."
  }
}

# Domain Configuration
variable "domain_name" {
  description = "Domain name for the application"
  type        = string
  default     = "purrr.love"
  
  validation {
    condition     = can(regex("^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\\.[a-zA-Z]{2,}$", var.domain_name))
    error_message = "Domain name must be a valid domain."
  }
}

# Scaling Configuration
variable "min_capacity" {
  description = "Minimum ECS service capacity"
  type        = number
  default     = 1
  
  validation {
    condition     = var.min_capacity >= 1
    error_message = "Minimum capacity must be at least 1."
  }
}

variable "max_capacity" {
  description = "Maximum ECS service capacity"
  type        = number
  default     = 10
  
  validation {
    condition     = var.max_capacity >= var.min_capacity
    error_message = "Maximum capacity must be greater than or equal to minimum capacity."
  }
}

# Monitoring Configuration
variable "enable_monitoring" {
  description = "Enable CloudWatch monitoring"
  type        = bool
  default     = true
}

variable "enable_logging" {
  description = "Enable CloudWatch logging"
  type        = bool
  default     = true
}

# Backup Configuration
variable "enable_backups" {
  description = "Enable automated backups"
  type        = bool
  default     = true
}

variable "backup_retention_period" {
  description = "Backup retention period in days"
  type        = number
  default     = 7
  
  validation {
    condition     = var.backup_retention_period >= 1 && var.backup_retention_period <= 35
    error_message = "Backup retention period must be between 1 and 35 days."
  }
}

# Security Configuration
variable "enable_ssl" {
  description = "Enable SSL/TLS encryption"
  type        = bool
  default     = true
}

variable "enable_waf" {
  description = "Enable AWS WAF protection"
  type        = bool
  default     = var.environment == "production"
}

# Cost Optimization
variable "enable_spot_instances" {
  description = "Enable spot instances for cost optimization"
  type        = bool
  default     = var.environment != "production"
}

variable "enable_auto_scaling" {
  description = "Enable auto-scaling based on demand"
  type        = bool
  default     = true
}
