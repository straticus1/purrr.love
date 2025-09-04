# ECS Module for Purrr.love

# ECS Cluster
resource "aws_ecs_cluster" "main" {
  name = "${var.environment}-${var.project_name}-cluster"

  # Cluster settings
  setting {
    name  = "containerInsights"
    value = var.enable_container_insights ? "enabled" : "disabled"
  }

  # Capacity providers configuration
  dynamic "capacity_providers" {
    for_each = var.enable_fargate ? [1] : []
    content {
      capacity_providers = ["FARGATE", "FARGATE_SPOT"]

      default_capacity_provider_strategy {
        capacity_provider = var.use_fargate_spot ? "FARGATE_SPOT" : "FARGATE"
        weight           = 1
        base             = 1
      }
    }
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-cluster"
      Environment = var.environment
      Service     = "ECS"
    }
  )
}

# ECS Cluster Capacity Providers (for Fargate)
resource "aws_ecs_cluster_capacity_providers" "main" {
  count        = var.enable_fargate ? 1 : 0
  cluster_name = aws_ecs_cluster.main.name

  capacity_providers = ["FARGATE", "FARGATE_SPOT"]

  default_capacity_provider_strategy {
    base              = 1
    weight            = 100
    capacity_provider = var.use_fargate_spot ? "FARGATE_SPOT" : "FARGATE"
  }
}

# CloudWatch Log Group for ECS
resource "aws_cloudwatch_log_group" "ecs" {
  name              = "/ecs/${var.environment}-${var.project_name}"
  retention_in_days = var.log_retention_days

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-ecs-logs"
      Environment = var.environment
      Service     = "CloudWatch"
    }
  )
}

# ECS Task Execution Role
resource "aws_iam_role" "ecs_task_execution_role" {
  name = "${var.environment}-${var.project_name}-ecs-task-execution"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "ecs-tasks.amazonaws.com"
        }
      }
    ]
  })

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-ecs-task-execution"
      Environment = var.environment
      Service     = "IAM"
    }
  )
}

resource "aws_iam_role_policy_attachment" "ecs_task_execution_role" {
  role       = aws_iam_role.ecs_task_execution_role.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"
}

# ECS Task Role
resource "aws_iam_role" "ecs_task_role" {
  name = "${var.environment}-${var.project_name}-ecs-task"

  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action = "sts:AssumeRole"
        Effect = "Allow"
        Principal = {
          Service = "ecs-tasks.amazonaws.com"
        }
      }
    ]
  })

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-ecs-task"
      Environment = var.environment
      Service     = "IAM"
    }
  )
}

# Custom IAM Policy for ECS Tasks
resource "aws_iam_role_policy" "ecs_task_policy" {
  name = "${var.environment}-${var.project_name}-ecs-task-policy"
  role = aws_iam_role.ecs_task_role.id

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Effect = "Allow"
        Action = [
          "s3:GetObject",
          "s3:PutObject",
          "s3:DeleteObject",
          "s3:ListBucket"
        ]
        Resource = [
          "arn:aws:s3:::${var.environment}-${var.project_name}-*",
          "arn:aws:s3:::${var.environment}-${var.project_name}-*/*"
        ]
      },
      {
        Effect = "Allow"
        Action = [
          "ssm:GetParameter",
          "ssm:GetParameters",
          "ssm:GetParametersByPath"
        ]
        Resource = "arn:aws:ssm:*:*:parameter/${var.environment}/${var.project_name}/*"
      },
      {
        Effect = "Allow"
        Action = [
          "secretsmanager:GetSecretValue"
        ]
        Resource = "arn:aws:secretsmanager:*:*:secret:${var.environment}/${var.project_name}/*"
      }
    ]
  })
}

# ECS Task Definition
resource "aws_ecs_task_definition" "app" {
  family                   = "${var.environment}-${var.project_name}-app"
  network_mode             = "awsvpc"
  requires_compatibilities = ["FARGATE"]
  cpu                      = var.task_cpu
  memory                   = var.task_memory
  execution_role_arn       = aws_iam_role.ecs_task_execution_role.arn
  task_role_arn           = aws_iam_role.ecs_task_role.arn

  container_definitions = jsonencode([
    {
      name  = "${var.project_name}-app"
      image = var.container_image
      
      essential = true
      
      cpu    = var.container_cpu
      memory = var.container_memory
      
      portMappings = [
        {
          containerPort = var.container_port
          protocol      = "tcp"
        }
      ]
      
      environment = [
        {
          name  = "APP_ENV"
          value = var.environment
        },
        {
          name  = "APP_NAME"
          value = var.project_name
        },
        {
          name  = "LOG_LEVEL"
          value = var.environment == "production" ? "info" : "debug"
        },
        {
          name  = "METAVERSE_AUTOMATION_ENABLED"
          value = "true"
        },
        {
          name  = "METAVERSE_AI_NPCS_ENABLED"
          value = "true"
        },
        {
          name  = "METAVERSE_WEATHER_SYSTEM_ENABLED"
          value = "true"
        },
        {
          name  = "METAVERSE_ANALYTICS_ENABLED"
          value = "true"
        },
        {
          name  = "METAVERSE_ENGAGEMENT_THRESHOLD"
          value = "0.4"
        },
        {
          name  = "METAVERSE_LOG_LEVEL"
          value = var.environment == "production" ? "info" : "debug"
        }
      ]
      
      secrets = [
        {
          name      = "DB_PASSWORD"
          valueFrom = "arn:aws:ssm:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:parameter/${var.environment}/${var.project_name}/database/password"
        }
      ]
      
      logConfiguration = {
        logDriver = "awslogs"
        options = {
          awslogs-group         = aws_cloudwatch_log_group.ecs.name
          awslogs-region        = data.aws_region.current.name
          awslogs-stream-prefix = "ecs"
        }
      }
      
      healthCheck = var.enable_health_check ? {
        command = [
          "CMD-SHELL",
          "curl -f http://localhost:${var.container_port}${var.health_check_path} || exit 1"
        ]
        interval    = var.health_check_interval
        timeout     = var.health_check_timeout
        retries     = var.health_check_retries
        startPeriod = var.health_check_start_period
      } : null
    }
  ])

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-app-task"
      Environment = var.environment
      Service     = "ECS"
    }
  )
}

# ECS Service
resource "aws_ecs_service" "app" {
  name            = "${var.environment}-${var.project_name}-app"
  cluster         = aws_ecs_cluster.main.id
  task_definition = aws_ecs_task_definition.app.arn
  desired_count   = var.desired_count

  # Capacity provider strategy
  dynamic "capacity_provider_strategy" {
    for_each = var.enable_fargate ? [1] : []
    content {
      capacity_provider = var.use_fargate_spot ? "FARGATE_SPOT" : "FARGATE"
      weight           = 100
      base             = 1
    }
  }

  # Network configuration
  network_configuration {
    security_groups  = var.security_group_ids
    subnets         = var.subnet_ids
    assign_public_ip = var.assign_public_ip
  }

  # Load balancer configuration
  dynamic "load_balancer" {
    for_each = var.target_group_arn != "" ? [1] : []
    content {
      target_group_arn = var.target_group_arn
      container_name   = "${var.project_name}-app"
      container_port   = var.container_port
    }
  }

  # Service discovery
  dynamic "service_registries" {
    for_each = var.service_discovery_registry_arn != "" ? [1] : []
    content {
      registry_arn = var.service_discovery_registry_arn
    }
  }

  # Deployment configuration
  deployment_configuration {
    maximum_percent         = var.deployment_maximum_percent
    minimum_healthy_percent = var.deployment_minimum_healthy_percent
  }

  # Auto scaling configuration
  depends_on = [
    aws_iam_role_policy_attachment.ecs_task_execution_role,
    aws_ecs_cluster_capacity_providers.main
  ]

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-app-service"
      Environment = var.environment
      Service     = "ECS"
    }
  )
}

# Application Auto Scaling Target
resource "aws_appautoscaling_target" "ecs_target" {
  count              = var.enable_auto_scaling ? 1 : 0
  max_capacity       = var.autoscaling_max_capacity
  min_capacity       = var.autoscaling_min_capacity
  resource_id        = "service/${aws_ecs_cluster.main.name}/${aws_ecs_service.app.name}"
  scalable_dimension = "ecs:service:DesiredCount"
  service_namespace  = "ecs"

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-autoscaling-target"
      Environment = var.environment
      Service     = "ApplicationAutoScaling"
    }
  )
}

# Auto Scaling Policy - Scale Up
resource "aws_appautoscaling_policy" "scale_up_policy" {
  count              = var.enable_auto_scaling ? 1 : 0
  name               = "${var.environment}-${var.project_name}-scale-up"
  policy_type        = "TargetTrackingScaling"
  resource_id        = aws_appautoscaling_target.ecs_target[0].resource_id
  scalable_dimension = aws_appautoscaling_target.ecs_target[0].scalable_dimension
  service_namespace  = aws_appautoscaling_target.ecs_target[0].service_namespace

  target_tracking_scaling_policy_configuration {
    predefined_metric_specification {
      predefined_metric_type = "ECSServiceAverageCPUUtilization"
    }
    target_value = var.autoscaling_target_cpu
  }
}

# Auto Scaling Policy - Memory
resource "aws_appautoscaling_policy" "memory_policy" {
  count              = var.enable_auto_scaling ? 1 : 0
  name               = "${var.environment}-${var.project_name}-memory-scaling"
  policy_type        = "TargetTrackingScaling"
  resource_id        = aws_appautoscaling_target.ecs_target[0].resource_id
  scalable_dimension = aws_appautoscaling_target.ecs_target[0].scalable_dimension
  service_namespace  = aws_appautoscaling_target.ecs_target[0].service_namespace

  target_tracking_scaling_policy_configuration {
    predefined_metric_specification {
      predefined_metric_type = "ECSServiceAverageMemoryUtilization"
    }
    target_value = var.autoscaling_target_memory
  }
}

# Data sources
data "aws_caller_identity" "current" {}
data "aws_region" "current" {}
