# Route53 DNS Module for Purrr.love

# Get existing hosted zone or create new one
data "aws_route53_zone" "main" {
  count = var.domain_name != "" && !var.create_hosted_zone ? 1 : 0
  name  = var.domain_name
}

# Create hosted zone if requested
resource "aws_route53_zone" "main" {
  count = var.domain_name != "" && var.create_hosted_zone ? 1 : 0
  name  = var.domain_name

  # Hosted zone configuration
  comment = "Hosted zone for ${var.project_name} ${var.environment} environment"

  dynamic "vpc" {
    for_each = var.private_zone && length(var.vpc_ids) > 0 ? var.vpc_ids : []
    content {
      vpc_id     = vpc.value.vpc_id
      vpc_region = vpc.value.vpc_region
    }
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-hosted-zone"
      Environment = var.environment
      Service     = "Route53"
    }
  )
}

locals {
  hosted_zone_id = var.domain_name != "" ? (
    var.create_hosted_zone ? aws_route53_zone.main[0].zone_id : data.aws_route53_zone.main[0].zone_id
  ) : ""
}

# A Record for main domain
resource "aws_route53_record" "main" {
  count   = var.domain_name != "" && var.load_balancer_dns_name != "" ? 1 : 0
  zone_id = local.hosted_zone_id
  name    = var.domain_name
  type    = "A"

  alias {
    name                   = var.load_balancer_dns_name
    zone_id               = var.load_balancer_zone_id
    evaluate_target_health = var.enable_health_checks
  }
}

# AAAA Record for IPv6
resource "aws_route53_record" "main_ipv6" {
  count   = var.domain_name != "" && var.load_balancer_dns_name != "" && var.enable_ipv6 ? 1 : 0
  zone_id = local.hosted_zone_id
  name    = var.domain_name
  type    = "AAAA"

  alias {
    name                   = var.load_balancer_dns_name
    zone_id               = var.load_balancer_zone_id
    evaluate_target_health = var.enable_health_checks
  }
}

# CNAME Record for www subdomain
resource "aws_route53_record" "www" {
  count   = var.domain_name != "" && var.create_www_record ? 1 : 0
  zone_id = local.hosted_zone_id
  name    = "www.${var.domain_name}"
  type    = "CNAME"
  ttl     = var.cname_ttl
  records = [var.domain_name]
}

# Additional subdomains
resource "aws_route53_record" "subdomains" {
  for_each = var.subdomains

  zone_id = local.hosted_zone_id
  name    = "${each.key}.${var.domain_name}"
  type    = each.value.type
  ttl     = each.value.ttl

  # For A/AAAA records pointing to ALB
  dynamic "alias" {
    for_each = each.value.type == "A" || each.value.type == "AAAA" ? [1] : []
    content {
      name                   = var.load_balancer_dns_name
      zone_id               = var.load_balancer_zone_id
      evaluate_target_health = var.enable_health_checks
    }
  }

  # For other record types
  records = each.value.type != "A" && each.value.type != "AAAA" ? each.value.records : null
}

# MX Records for email
resource "aws_route53_record" "mx" {
  count   = var.domain_name != "" && length(var.mx_records) > 0 ? 1 : 0
  zone_id = local.hosted_zone_id
  name    = var.domain_name
  type    = "MX"
  ttl     = var.mx_ttl
  records = var.mx_records
}

# TXT Records (SPF, DKIM, DMARC, etc.)
resource "aws_route53_record" "txt" {
  for_each = var.txt_records

  zone_id = local.hosted_zone_id
  name    = each.key != "@" ? "${each.key}.${var.domain_name}" : var.domain_name
  type    = "TXT"
  ttl     = var.txt_ttl
  records = each.value
}

# CAA Records for certificate authority authorization
resource "aws_route53_record" "caa" {
  count   = var.domain_name != "" && length(var.caa_records) > 0 ? 1 : 0
  zone_id = local.hosted_zone_id
  name    = var.domain_name
  type    = "CAA"
  ttl     = var.caa_ttl
  records = var.caa_records
}

# Health Check for main domain
resource "aws_route53_health_check" "main" {
  count                           = var.enable_health_checks && var.domain_name != "" ? 1 : 0
  fqdn                           = var.domain_name
  port                           = var.health_check_port
  type                           = var.health_check_type
  resource_path                  = var.health_check_path
  failure_threshold              = var.health_check_failure_threshold
  request_interval               = var.health_check_request_interval
  measure_latency                = var.health_check_measure_latency
  invert_healthcheck             = var.health_check_invert
  disabled                       = false
  enable_sni                     = var.health_check_enable_sni
  cloudwatch_alarm_region        = var.health_check_cloudwatch_alarm_region
  insufficient_data_health_status = var.health_check_insufficient_data_status

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-health-check"
      Environment = var.environment
      Service     = "Route53"
    }
  )
}

# CloudWatch Alarm for health check
resource "aws_cloudwatch_metric_alarm" "health_check_alarm" {
  count               = var.enable_health_checks && var.domain_name != "" ? 1 : 0
  alarm_name          = "${var.environment}-${var.project_name}-health-check-alarm"
  comparison_operator = "LessThanThreshold"
  evaluation_periods  = "2"
  metric_name         = "HealthCheckStatus"
  namespace           = "AWS/Route53"
  period              = "60"
  statistic           = "Minimum"
  threshold           = "1"
  alarm_description   = "This metric monitors Route53 health check for ${var.domain_name}"
  alarm_actions       = var.health_check_alarm_actions

  dimensions = {
    HealthCheckId = aws_route53_health_check.main[0].id
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-health-check-alarm"
      Environment = var.environment
      Service     = "CloudWatch"
    }
  )
}

# Certificate validation records (if using ACM)
resource "aws_route53_record" "cert_validation" {
  for_each = var.certificate_validation_options

  zone_id = local.hosted_zone_id
  name    = each.value.resource_record_name
  type    = each.value.resource_record_type
  ttl     = 60
  records = [each.value.resource_record_value]

  allow_overwrite = true
}
