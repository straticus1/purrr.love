# ACM Certificate Module for Purrr.love and Purrr.me

# Certificate for purrr.love and all subdomains
resource "aws_acm_certificate" "purrr_love" {
  domain_name               = var.domain_name
  subject_alternative_names = var.subject_alternative_names
  validation_method         = "DNS"

  lifecycle {
    create_before_destroy = true
  }

  tags = merge(
    var.common_tags,
    {
      Name        = "${var.environment}-${var.project_name}-cert"
      Environment = var.environment
      Service     = "ACM"
      Domain      = var.domain_name
    }
  )
}

# Certificate validation using Route53
resource "aws_acm_certificate_validation" "purrr_love" {
  certificate_arn         = aws_acm_certificate.purrr_love.arn
  validation_record_fqdns = [for record in aws_route53_record.cert_validation : record.fqdn]

  timeouts {
    create = "10m"
  }
}

# Route53 records for certificate validation
resource "aws_route53_record" "cert_validation" {
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
  zone_id         = var.route53_zone_id
}
