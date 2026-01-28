# Purrr.love Terraform Configuration - References Shared AfterDark Systems Infrastructure

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

# Reference shared AfterDark Systems infrastructure
data "aws_vpc" "afterdarksys" {
  id = "vpc-0c1b813880b3982a5"  # afterdarksys-vpc
}

data "aws_lb" "afterdarksys" {
  name = "afterdarksys-alb"
}

data "aws_lb_listener" "afterdarksys_https" {
  load_balancer_arn = data.aws_lb.afterdarksys.arn
  port              = 443
}

# Reference purrr.love subnets in afterdarksys VPC
data "aws_subnets" "purrr_private" {
  filter {
    name   = "vpc-id"
    values = [data.aws_vpc.afterdarksys.id]
  }
  filter {
    name   = "tag:Name"
    values = ["purrr-private-subnet-*"]
  }
}

# Reference purrr.love security group in afterdarksys VPC
data "aws_security_group" "purrr_ecs" {
  id = "sg-0cd1a6d29ed7899fd"  # purrr-ecs-afterdark
}

data "aws_security_group" "purrr_rds" {
  id = "sg-0c0dec2e4f117bd37"  # purrr-rds-afterdark
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

# ECS Cluster (already exists, managed here)
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

# Target Group for purrr.love in shared afterdarksys VPC
resource "aws_lb_target_group" "purrr_love" {
  name        = "purrr-love-tg-ip"
  port        = 80
  protocol    = "HTTP"
  vpc_id      = data.aws_vpc.afterdarksys.id
  target_type = "ip"

  health_check {
    enabled             = true
    healthy_threshold   = 2
    interval            = 30
    matcher             = "200,301"
    path                = "/"
    port                = "traffic-port"
    protocol            = "HTTP"
    timeout             = 5
    unhealthy_threshold = 3
  }

  tags = {
    Name = "purrr-love-target-group"
  }
}

# Listener rules for purrr.love on shared ALB
resource "aws_lb_listener_rule" "purrr_love" {
  listener_arn = data.aws_lb_listener.afterdarksys_https.arn
  priority     = 20

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.purrr_love.arn
  }

  condition {
    host_header {
      values = ["purrr.love"]
    }
  }

  tags = {
    Name = "purrr-love-listener-rule"
  }
}

resource "aws_lb_listener_rule" "www_purrr_love" {
  listener_arn = data.aws_lb_listener.afterdarksys_https.arn
  priority     = 21

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.purrr_love.arn
  }

  condition {
    host_header {
      values = ["www.purrr.love"]
    }
  }

  tags = {
    Name = "www-purrr-love-listener-rule"
  }
}

# Add purrr.love certificate to shared ALB HTTPS listener
resource "aws_lb_listener_certificate" "purrr_love" {
  listener_arn    = data.aws_lb_listener.afterdarksys_https.arn
  certificate_arn = aws_acm_certificate_validation.purrr_love.certificate_arn
}

resource "aws_lb_listener_certificate" "purrr_me" {
  listener_arn    = data.aws_lb_listener.afterdarksys_https.arn
  certificate_arn = aws_acm_certificate_validation.purrr_me.certificate_arn
}

# Update DNS records to point to shared afterdarksys ALB
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
    name                   = data.aws_lb.afterdarksys.dns_name
    zone_id               = data.aws_lb.afterdarksys.zone_id
    evaluate_target_health = true
  }
}

# CNAME record for www.purrr.love
resource "aws_route53_record" "www_purrr_love" {
  zone_id = data.aws_route53_zone.purrr_love.zone_id
  name    = "www.purrr.love"
  type    = "CNAME"
  ttl     = 300
  records = ["purrr.love"]
}

# Outputs
output "shared_alb_dns_name" {
  description = "DNS name of the shared AfterDark Systems ALB"
  value       = data.aws_lb.afterdarksys.dns_name
}

output "purrr_love_certificate_arn" {
  description = "ARN of the purrr.love certificate"
  value       = aws_acm_certificate_validation.purrr_love.certificate_arn
}

output "purrr_me_certificate_arn" {
  description = "ARN of the purrr.me certificate"
  value       = aws_acm_certificate_validation.purrr_me.certificate_arn
}

output "purrr_love_target_group_arn" {
  description = "ARN of the purrr.love target group"
  value       = aws_lb_target_group.purrr_love.arn
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

output "infrastructure_notes" {
  description = "Important notes about the infrastructure setup"
  value = {
    shared_infrastructure = "This configuration references shared AfterDark Systems infrastructure"
    vpc_id               = data.aws_vpc.afterdarksys.id
    alb_shared          = "ALB is managed by afterdarksys.com terraform, not this configuration"
    target_registration = "ECS tasks must be manually registered with target group until ECS service is added to terraform"
  }
}
