# ECS Module Outputs

# Cluster Outputs
output "cluster_id" {
  description = "ECS cluster ID"
  value       = aws_ecs_cluster.main.id
}

output "cluster_arn" {
  description = "ECS cluster ARN"
  value       = aws_ecs_cluster.main.arn
}

output "cluster_name" {
  description = "ECS cluster name"
  value       = aws_ecs_cluster.main.name
}

# Service Outputs
output "service_id" {
  description = "ECS service ID"
  value       = aws_ecs_service.app.id
}

output "service_name" {
  description = "ECS service name"
  value       = aws_ecs_service.app.name
}

output "service_arn" {
  description = "ECS service ARN"
  value       = aws_ecs_service.app.id
}

# Task Definition Outputs
output "task_definition_arn" {
  description = "Task definition ARN"
  value       = aws_ecs_task_definition.app.arn
}

output "task_definition_family" {
  description = "Task definition family"
  value       = aws_ecs_task_definition.app.family
}

output "task_definition_revision" {
  description = "Task definition revision"
  value       = aws_ecs_task_definition.app.revision
}

# IAM Roles
output "task_execution_role_arn" {
  description = "Task execution role ARN"
  value       = aws_iam_role.ecs_task_execution_role.arn
}

output "task_role_arn" {
  description = "Task role ARN"
  value       = aws_iam_role.ecs_task_role.arn
}

# CloudWatch Log Group
output "log_group_name" {
  description = "CloudWatch log group name"
  value       = aws_cloudwatch_log_group.ecs.name
}

output "log_group_arn" {
  description = "CloudWatch log group ARN"
  value       = aws_cloudwatch_log_group.ecs.arn
}

# Auto Scaling
output "autoscaling_target_id" {
  description = "Auto scaling target ID"
  value       = var.enable_auto_scaling ? aws_appautoscaling_target.ecs_target[0].id : null
}

output "autoscaling_policy_cpu_arn" {
  description = "CPU-based auto scaling policy ARN"
  value       = var.enable_auto_scaling ? aws_appautoscaling_policy.scale_up_policy[0].arn : null
}

output "autoscaling_policy_memory_arn" {
  description = "Memory-based auto scaling policy ARN"
  value       = var.enable_auto_scaling ? aws_appautoscaling_policy.memory_policy[0].arn : null
}
