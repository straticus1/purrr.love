# ECS Module Variables

variable "environment" {
  description = "Environment name (production, staging, development)"
  type        = string
}

variable "project_name" {
  description = "Name of the project"
  type        = string
  default     = "purrr"
}

# Container Configuration
variable "container_image" {
  description = "Docker image for the application"
  type        = string
  default     = "nginx:alpine"
}

variable "container_port" {
  description = "Port exposed by the container"
  type        = number
  default     = 80
}

variable "container_cpu" {
  description = "CPU units for the container"
  type        = number
  default     = 256
}

variable "container_memory" {
  description = "Memory for the container (MB)"
  type        = number
  default     = 512
}

# Task Configuration
variable "task_cpu" {
  description = "CPU units for the task"
  type        = string
  default     = "256"
}

variable "task_memory" {
  description = "Memory for the task (MB)"
  type        = string
  default     = "512"
}

# Service Configuration
variable "desired_count" {
  description = "Desired number of running tasks"
  type        = number
  default     = 2
}

variable "deployment_maximum_percent" {
  description = "Maximum percentage of tasks that can be running during deployment"
  type        = number
  default     = 200
}

variable "deployment_minimum_healthy_percent" {
  description = "Minimum percentage of tasks that must remain healthy during deployment"
  type        = number
  default     = 100
}

# Network Configuration
variable "subnet_ids" {
  description = "List of subnet IDs for ECS service"
  type        = list(string)
}

variable "security_group_ids" {
  description = "List of security group IDs for ECS service"
  type        = list(string)
}

variable "assign_public_ip" {
  description = "Whether to assign public IP to ECS tasks"
  type        = bool
  default     = false
}

# Load Balancer Integration
variable "target_group_arn" {
  description = "ARN of the load balancer target group"
  type        = string
  default     = ""
}

# Service Discovery
variable "service_discovery_registry_arn" {
  description = "ARN of service discovery registry"
  type        = string
  default     = ""
}

# Fargate Configuration
variable "enable_fargate" {
  description = "Enable Fargate capacity provider"
  type        = bool
  default     = true
}

variable "use_fargate_spot" {
  description = "Use Fargate Spot instances"
  type        = bool
  default     = false
}

# Container Insights
variable "enable_container_insights" {
  description = "Enable Container Insights"
  type        = bool
  default     = true
}

# Health Check Configuration
variable "enable_health_check" {
  description = "Enable container health check"
  type        = bool
  default     = true
}

variable "health_check_path" {
  description = "Health check path"
  type        = string
  default     = "/health"
}

variable "health_check_interval" {
  description = "Health check interval in seconds"
  type        = number
  default     = 30
}

variable "health_check_timeout" {
  description = "Health check timeout in seconds"
  type        = number
  default     = 5
}

variable "health_check_retries" {
  description = "Health check retry count"
  type        = number
  default     = 3
}

variable "health_check_start_period" {
  description = "Health check start period in seconds"
  type        = number
  default     = 60
}

# Auto Scaling Configuration
variable "enable_auto_scaling" {
  description = "Enable auto scaling"
  type        = bool
  default     = true
}

variable "autoscaling_min_capacity" {
  description = "Minimum number of tasks"
  type        = number
  default     = 1
}

variable "autoscaling_max_capacity" {
  description = "Maximum number of tasks"
  type        = number
  default     = 10
}

variable "autoscaling_target_cpu" {
  description = "Target CPU utilization percentage for auto scaling"
  type        = number
  default     = 70
}

variable "autoscaling_target_memory" {
  description = "Target memory utilization percentage for auto scaling"
  type        = number
  default     = 80
}

# Logging Configuration
variable "log_retention_days" {
  description = "Number of days to retain CloudWatch logs"
  type        = number
  default     = 7
}

# Common Tags
variable "common_tags" {
  description = "Common tags to apply to all resources"
  type        = map(string)
  default = {
    ManagedBy = "terraform"
    Project   = "purrr"
  }
}
