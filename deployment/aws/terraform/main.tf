# Simplified Terraform Configuration for Purrr.love ALB Setup

terraform {
  required_version = ">= 1.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

provider "aws" {
  region = "us-east-1"
  
  default_tags {
    tags = {
      Project     = "purrr"
      Environment = "production"
      ManagedBy   = "terraform"
      Owner       = "purrr-love-team"
    }
  }
}

# Data sources for existing Route53 zones
data "aws_route53_zone" "purrr_love" {
  name = "purrr.love"
}

data "aws_route53_zone" "purrr_me" {
  name = "purrr.me"
}

# Get default VPC and subnets for quick deployment
data "aws_vpc" "default" {
  default = true
}

data "aws_subnets" "default" {
  filter {
    name   = "vpc-id"
    values = [data.aws_vpc.default.id]
  }
}

# Security group for ALB
resource "aws_security_group" "alb" {
  name_prefix = "purrr-alb-"
  vpc_id      = data.aws_vpc.default.id

  # HTTP
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
    description = "HTTP"
  }

  # HTTPS
  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
    description = "HTTPS"
  }

  # All outbound traffic
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
    description = "All outbound traffic"
  }

  tags = {
    Name = "purrr-alb-security-group"
  }
}

# Security group for ECS tasks
resource "aws_security_group" "ecs" {
  name_prefix = "purrr-ecs-"
  vpc_id      = data.aws_vpc.default.id

  # HTTP from ALB
  ingress {
    from_port       = 80
    to_port         = 80
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id]
    description     = "HTTP from ALB"
  }

  # All outbound traffic
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
    description = "All outbound traffic"
  }

  tags = {
    Name = "purrr-ecs-security-group"
  }
}

# ACM Certificate for purrr.love
resource "aws_acm_certificate" "purrr_love" {
  domain_name = "purrr.love"
  subject_alternative_names = [
    "*.purrr.love"
  ]
  validation_method = "DNS"

  lifecycle {
    create_before_destroy = true
  }

  tags = {
    Name = "purrr.love-certificate"
  }
}

# ACM Certificate for purrr.me
resource "aws_acm_certificate" "purrr_me" {
  domain_name = "purrr.me"
  subject_alternative_names = [
    "*.purrr.me"
  ]
  validation_method = "DNS"

  lifecycle {
    create_before_destroy = true
  }

  tags = {
    Name = "purrr.me-certificate"
  }
}

# Certificate validation records for purrr.love
resource "aws_route53_record" "purrr_love_cert_validation" {
  for_each = {
    for dvo in aws_acm_certificate.purrr_love.domain_validation_options : dvo.domain_name => {
      name   = dvo.resource_record_name
      record = dvo.resource_record_value
      type   = dvo.resource_record_type
    }
  }

  allow_overwrite = true
  name            = each.value.name
  records         = [each.value.record]
  ttl             = 60
  type            = each.value.type
  zone_id         = data.aws_route53_zone.purrr_love.zone_id
}

# Certificate validation records for purrr.me
resource "aws_route53_record" "purrr_me_cert_validation" {
  for_each = {
    for dvo in aws_acm_certificate.purrr_me.domain_validation_options : dvo.domain_name => {
      name   = dvo.resource_record_name
      record = dvo.resource_record_value
      type   = dvo.resource_record_type
    }
  }

  allow_overwrite = true
  name            = each.value.name
  records         = [each.value.record]
  ttl             = 60
  type            = each.value.type
  zone_id         = data.aws_route53_zone.purrr_me.zone_id
}

# Certificate validation
resource "aws_acm_certificate_validation" "purrr_love" {
  certificate_arn         = aws_acm_certificate.purrr_love.arn
  validation_record_fqdns = [for record in aws_route53_record.purrr_love_cert_validation : record.fqdn]

  timeouts {
    create = "10m"
  }
}

resource "aws_acm_certificate_validation" "purrr_me" {
  certificate_arn         = aws_acm_certificate.purrr_me.arn
  validation_record_fqdns = [for record in aws_route53_record.purrr_me_cert_validation : record.fqdn]

  timeouts {
    create = "10m"
  }
}

# Application Load Balancer
resource "aws_lb" "main" {
  name               = "purrr-alb"
  internal           = false
  load_balancer_type = "application"
  security_groups    = [aws_security_group.alb.id]
  subnets           = data.aws_subnets.default.ids

  enable_deletion_protection = false

  tags = {
    Name = "purrr-application-load-balancer"
  }
}

# Target Group for ECS
resource "aws_lb_target_group" "app" {
  name        = "purrr-app-tg"
  port        = 80
  protocol    = "HTTP"
  vpc_id      = data.aws_vpc.default.id
  target_type = "ip"

  health_check {
    enabled             = true
    healthy_threshold   = 2
    interval            = 30
    matcher             = "200"
    path                = "/"
    port                = "traffic-port"
    protocol            = "HTTP"
    timeout             = 5
    unhealthy_threshold = 3
  }

  tags = {
    Name = "purrr-app-target-group"
  }
}

# HTTP Listener (redirects to HTTPS)
resource "aws_lb_listener" "http" {
  load_balancer_arn = aws_lb.main.arn
  port              = "80"
  protocol          = "HTTP"

  default_action {
    type = "redirect"

    redirect {
      port        = "443"
      protocol    = "HTTPS"
      status_code = "HTTP_301"
    }
  }
}

# HTTPS Listener for purrr.love
resource "aws_lb_listener" "https" {
  load_balancer_arn = aws_lb.main.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS13-1-2-2021-06"
  certificate_arn   = aws_acm_certificate_validation.purrr_love.certificate_arn

  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.app.arn
  }
}

# Additional certificate for purrr.me
resource "aws_lb_listener_certificate" "purrr_me" {
  listener_arn    = aws_lb_listener.https.arn
  certificate_arn = aws_acm_certificate_validation.purrr_me.certificate_arn
}

# ECS Cluster
resource "aws_ecs_cluster" "main" {
  name = "purrr-cluster"

  setting {
    name  = "containerInsights"
    value = "enabled"
  }

  tags = {
    Name = "purrr-ecs-cluster"
  }
}

# ECS Task Definition
resource "aws_ecs_task_definition" "app" {
  family                   = "purrr-app"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 256
  memory                   = 512
  execution_role_arn       = aws_iam_role.ecs_execution.arn

  container_definitions = jsonencode([
    {
      name  = "app"
      image = var.container_image
      portMappings = [
        {
          containerPort = 80
          hostPort      = 80
        }
      ]
      logConfiguration = {
        logDriver = "awslogs"
        options = {
          "awslogs-group"         = aws_cloudwatch_log_group.app.name
          "awslogs-region"        = "us-east-1"
          "awslogs-stream-prefix" = "ecs"
        }
      }
    }
  ])

  tags = {
    Name = "purrr-app-task-definition"
  }
}

# CloudWatch Log Group
resource "aws_cloudwatch_log_group" "app" {
  name              = "/ecs/purrr-app"
  retention_in_days = 7

  tags = {
    Name = "purrr-app-logs"
  }
}

# ECS Execution Role
resource "aws_iam_role" "ecs_execution" {
  name = "purrr-ecs-execution-role"

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

  tags = {
    Name = "purrr-ecs-execution-role"
  }
}

resource "aws_iam_role_policy_attachment" "ecs_execution" {
  role       = aws_iam_role.ecs_execution.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"
}

# ECS Service
resource "aws_ecs_service" "app" {
  name            = "purrr-app"
  cluster         = aws_ecs_cluster.main.id
  task_definition = aws_ecs_task_definition.app.arn
  desired_count   = 2
  launch_type     = "FARGATE"

  network_configuration {
    security_groups = [aws_security_group.ecs.id]
    subnets         = data.aws_subnets.default.ids
    assign_public_ip = true
  }

  load_balancer {
    target_group_arn = aws_lb_target_group.app.arn
    container_name   = "app"
    container_port   = 80
  }

  depends_on = [aws_lb_listener.https]

  tags = {
    Name = "purrr-app-service"
  }
}

# Update DNS records to point to ALB for purrr.love
resource "aws_route53_record" "purrr_love_domains" {
  for_each = toset([
    "purrr.love",
    "api.purrr.love", 
    "app.purrr.love",
    "admin.purrr.love",
    "webhooks.purrr.love",
    "cdn.purrr.love",
    "static.purrr.love",
    "assets.purrr.love"
  ])

  zone_id = data.aws_route53_zone.purrr_love.zone_id
  name    = each.key
  type    = "A"

  alias {
    name                   = aws_lb.main.dns_name
    zone_id               = aws_lb.main.zone_id
    evaluate_target_health = true
  }
}

# Update DNS records to point to ALB for purrr.me
resource "aws_route53_record" "purrr_me_domains" {
  for_each = toset([
    "purrr.me",
    "api.purrr.me",
    "app.purrr.me", 
    "admin.purrr.me",
    "webhooks.purrr.me",
    "cdn.purrr.me",
    "static.purrr.me",
    "assets.purrr.me"
  ])

  zone_id = data.aws_route53_zone.purrr_me.zone_id
  name    = each.key
  type    = "A"

  alias {
    name                   = aws_lb.main.dns_name
    zone_id               = aws_lb.main.zone_id
    evaluate_target_health = true
  }
}

# Outputs
output "alb_dns_name" {
  description = "DNS name of the load balancer"
  value       = aws_lb.main.dns_name
}

output "alb_zone_id" {
  description = "Zone ID of the load balancer"
  value       = aws_lb.main.zone_id
}

output "purrr_love_certificate_arn" {
  description = "ARN of the purrr.love certificate"
  value       = aws_acm_certificate_validation.purrr_love.certificate_arn
}

output "purrr_me_certificate_arn" {
  description = "ARN of the purrr.me certificate"
  value       = aws_acm_certificate_validation.purrr_me.certificate_arn
}

output "test_urls" {
  description = "URLs to test the deployment"
  value = {
    purrr_love_main = "https://purrr.love"
    purrr_love_api  = "https://api.purrr.love"
    purrr_love_app  = "https://app.purrr.love"
    purrr_me_main   = "https://purrr.me"
    purrr_me_api    = "https://api.purrr.me"
    purrr_me_app    = "https://app.purrr.me"
  }
}
