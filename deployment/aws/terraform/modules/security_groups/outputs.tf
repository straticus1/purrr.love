# Security Groups Module Outputs

# ALB Security Group
output "alb_security_group_id" {
  description = "ID of the ALB security group"
  value       = aws_security_group.alb.id
}

output "alb_security_group_arn" {
  description = "ARN of the ALB security group"
  value       = aws_security_group.alb.arn
}

# Web Application Security Group
output "web_security_group_id" {
  description = "ID of the web application security group"
  value       = aws_security_group.web.id
}

output "web_security_group_arn" {
  description = "ARN of the web application security group"
  value       = aws_security_group.web.arn
}

# Database Security Group
output "database_security_group_id" {
  description = "ID of the database security group"
  value       = aws_security_group.database.id
}

output "database_security_group_arn" {
  description = "ARN of the database security group"
  value       = aws_security_group.database.arn
}

# Redis Security Group
output "redis_security_group_id" {
  description = "ID of the Redis security group"
  value       = var.enable_redis ? aws_security_group.redis[0].id : null
}

output "redis_security_group_arn" {
  description = "ARN of the Redis security group"
  value       = var.enable_redis ? aws_security_group.redis[0].arn : null
}

# Bastion Security Group
output "bastion_security_group_id" {
  description = "ID of the bastion security group"
  value       = var.enable_bastion ? aws_security_group.bastion[0].id : null
}

output "bastion_security_group_arn" {
  description = "ARN of the bastion security group"
  value       = var.enable_bastion ? aws_security_group.bastion[0].arn : null
}

# EFS Security Group
output "efs_security_group_id" {
  description = "ID of the EFS security group"
  value       = var.enable_efs ? aws_security_group.efs[0].id : null
}

output "efs_security_group_arn" {
  description = "ARN of the EFS security group"
  value       = var.enable_efs ? aws_security_group.efs[0].arn : null
}

# VPC Endpoints Security Group
output "vpc_endpoints_security_group_id" {
  description = "ID of the VPC endpoints security group"
  value       = var.enable_vpc_endpoints ? aws_security_group.vpc_endpoints[0].id : null
}

output "vpc_endpoints_security_group_arn" {
  description = "ARN of the VPC endpoints security group"
  value       = var.enable_vpc_endpoints ? aws_security_group.vpc_endpoints[0].arn : null
}

# All Security Group IDs (for convenience)
output "all_security_group_ids" {
  description = "Map of all security group IDs"
  value = {
    alb           = aws_security_group.alb.id
    web           = aws_security_group.web.id
    database      = aws_security_group.database.id
    redis         = var.enable_redis ? aws_security_group.redis[0].id : null
    bastion       = var.enable_bastion ? aws_security_group.bastion[0].id : null
    efs           = var.enable_efs ? aws_security_group.efs[0].id : null
    vpc_endpoints = var.enable_vpc_endpoints ? aws_security_group.vpc_endpoints[0].id : null
  }
}

# Security Group Names (for reference)
output "security_group_names" {
  description = "Map of security group names"
  value = {
    alb           = aws_security_group.alb.name
    web           = aws_security_group.web.name
    database      = aws_security_group.database.name
    redis         = var.enable_redis ? aws_security_group.redis[0].name : null
    bastion       = var.enable_bastion ? aws_security_group.bastion[0].name : null
    efs           = var.enable_efs ? aws_security_group.efs[0].name : null
    vpc_endpoints = var.enable_vpc_endpoints ? aws_security_group.vpc_endpoints[0].name : null
  }
}
