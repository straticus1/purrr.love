# Application Load Balancer Module for Purrr.love

# Application Load Balancer
resource "aws_lb" "main" {
  name               = "${var.environment}-${var.project_name}-alb"
  internal           = var.internal
  load_balancer_type = "application"
  security_groups    = var.security_group_ids
  subnets           = var.subnet_ids

  enable_deletion_protection       = var.enable_deletion_protection
  enable_cross_zone_load_balancing = var.enable_cross_zone_load_balancing
  enable_http2                     = var.enable_http2
  enable_waf_fail_open            = var.enable_waf_fail_open

  # Access logs
  dynamic "access_logs" {
    for_each = var.enable_access_logs ? [1] : []
    content {
      bucket  = var.access_logs_bucket
      prefix  = var.access_logs_prefix
      enabled = true
    }
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-alb"
      Environment = var.environment
      Service     = "ALB"
    }
  )
}

# Target Group
resource "aws_lb_target_group" "app" {
  name     = "${var.environment}-${var.project_name}-tg"
  port     = var.target_port
  protocol = "HTTP"
  vpc_id   = var.vpc_id

  target_type = var.target_type

  # Health check configuration
  health_check {
    enabled             = true
    healthy_threshold   = var.health_check_healthy_threshold
    interval            = var.health_check_interval
    matcher             = var.health_check_matcher
    path                = var.health_check_path
    port                = var.health_check_port
    protocol            = "HTTP"
    timeout             = var.health_check_timeout
    unhealthy_threshold = var.health_check_unhealthy_threshold
  }

  # Stickiness configuration
  dynamic "stickiness" {
    for_each = var.enable_stickiness ? [1] : []
    content {
      type            = "lb_cookie"
      cookie_duration = var.stickiness_cookie_duration
      enabled         = true
    }
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-target-group"
      Environment = var.environment
      Service     = "ALB"
    }
  )

  lifecycle {
    create_before_destroy = true
  }
}

# HTTP Listener (redirects to HTTPS)
resource "aws_lb_listener" "http" {
  load_balancer_arn = aws_lb.main.arn
  port              = "80"
  protocol          = "HTTP"

  default_action {
    type = var.certificate_arn != "" ? "redirect" : "forward"

    dynamic "redirect" {
      for_each = var.certificate_arn != "" ? [1] : []
      content {
        port        = "443"
        protocol    = "HTTPS"
        status_code = "HTTP_301"
      }
    }

    dynamic "forward" {
      for_each = var.certificate_arn == "" ? [1] : []
      content {
        target_group {
          arn = aws_lb_target_group.app.arn
        }
      }
    }
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-http-listener"
      Environment = var.environment
      Service     = "ALB"
    }
  )
}

# HTTPS Listener (if certificate is provided)
resource "aws_lb_listener" "https" {
  count             = var.certificate_arn != "" ? 1 : 0
  load_balancer_arn = aws_lb.main.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = var.ssl_policy
  certificate_arn   = var.certificate_arn

  default_action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.app.arn
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-https-listener"
      Environment = var.environment
      Service     = "ALB"
    }
  )
}

# Additional certificate attachments
resource "aws_lb_listener_certificate" "additional" {
  count           = length(var.additional_certificate_arns)
  listener_arn    = aws_lb_listener.https[0].arn
  certificate_arn = var.additional_certificate_arns[count.index]
}

# Listener Rules for path-based routing
resource "aws_lb_listener_rule" "api" {
  count        = var.enable_api_routing ? 1 : 0
  listener_arn = var.certificate_arn != "" ? aws_lb_listener.https[0].arn : aws_lb_listener.http.arn
  priority     = 100

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.app.arn
  }

  condition {
    path_pattern {
      values = ["/api/*"]
    }
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-api-rule"
      Environment = var.environment
      Service     = "ALB"
    }
  )
}

# Listener Rules for host-based routing
resource "aws_lb_listener_rule" "host" {
  count        = length(var.host_based_routing)
  listener_arn = var.certificate_arn != "" ? aws_lb_listener.https[0].arn : aws_lb_listener.http.arn
  priority     = 200 + count.index

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.app.arn
  }

  condition {
    host_header {
      values = [var.host_based_routing[count.index]]
    }
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-host-rule-${count.index}"
      Environment = var.environment
      Service     = "ALB"
    }
  )
}
