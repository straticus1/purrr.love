# Security Groups Module Variables

variable "environment" {
  description = "Environment name (production, staging, development)"
  type        = string
  validation {
    condition     = contains(["production", "staging", "development"], var.environment)
    error_message = "Environment must be production, staging, or development."
  }
}

variable "project_name" {
  description = "Name of the project"
  type        = string
  default     = "purrr"
}

variable "vpc_id" {
  description = "ID of the VPC where security groups will be created"
  type        = string
}

variable "vpc_cidr_block" {
  description = "CIDR block of the VPC"
  type        = string
  default     = "10.0.0.0/16"
}

variable "application_port" {
  description = "Port on which the application runs"
  type        = number
  default     = 80
  validation {
    condition     = var.application_port > 0 && var.application_port <= 65535
    error_message = "Application port must be between 1 and 65535."
  }
}

variable "database_port" {
  description = "Port for database connections"
  type        = number
  default     = 3306
  validation {
    condition     = contains([3306, 5432, 1433, 1521], var.database_port)
    error_message = "Database port must be a common database port (3306, 5432, 1433, 1521)."
  }
}

variable "admin_cidr_blocks" {
  description = "CIDR blocks allowed admin access (SSH, database)"
  type        = list(string)
  default     = []
  validation {
    condition = alltrue([
      for cidr in var.admin_cidr_blocks :
      can(cidrhost(cidr, 0))
    ])
    error_message = "All admin CIDR blocks must be valid CIDR notation."
  }
}

variable "enable_redis" {
  description = "Enable Redis security group"
  type        = bool
  default     = true
}

variable "enable_bastion" {
  description = "Enable bastion host security group"
  type        = bool
  default     = false
}

variable "enable_efs" {
  description = "Enable EFS security group for shared storage"
  type        = bool
  default     = false
}

variable "enable_vpc_endpoints" {
  description = "Enable VPC endpoints security group"
  type        = bool
  default     = true
}

variable "common_tags" {
  description = "Common tags to apply to all security groups"
  type        = map(string)
  default = {
    ManagedBy = "terraform"
    Project   = "purrr"
  }
}

# Additional security rules for custom applications
variable "additional_web_ingress_rules" {
  description = "Additional ingress rules for web security group"
  type = list(object({
    description = string
    from_port   = number
    to_port     = number
    protocol    = string
    cidr_blocks = list(string)
  }))
  default = []
}

variable "additional_web_egress_rules" {
  description = "Additional egress rules for web security group"
  type = list(object({
    description = string
    from_port   = number
    to_port     = number
    protocol    = string
    cidr_blocks = list(string)
  }))
  default = []
}

# Allowed security groups for cross-reference
variable "trusted_security_group_ids" {
  description = "List of trusted security group IDs for additional access"
  type        = list(string)
  default     = []
}
