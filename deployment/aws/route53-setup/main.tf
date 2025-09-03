# Standalone Route53 Configuration for purrr.love
# Use this to create the hosted zone and get DNS servers for registrar

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
      Component   = "dns"
    }
  }
}

# Create hosted zone for purrr.love
resource "aws_route53_zone" "purrr_love" {
  name    = "purrr.love"
  comment = "Hosted zone for Purrr.love production environment"

  tags = {
    Name        = "purrr-love-hosted-zone"
    Environment = "production"
    Service     = "Route53"
    Project     = "purrr"
  }
}

# Placeholder A record for main domain (will be updated when ALB is deployed)
resource "aws_route53_record" "main" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "purrr.love"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# WWW CNAME record
resource "aws_route53_record" "www" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "www.purrr.love"
  type    = "CNAME"
  ttl     = 300
  records = ["purrr.love"]
}

# API subdomain
resource "aws_route53_record" "api" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "api.purrr.love"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# App subdomain
resource "aws_route53_record" "app" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "app.purrr.love"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# Admin subdomain
resource "aws_route53_record" "admin" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "admin.purrr.love"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# Webhooks subdomain
resource "aws_route53_record" "webhooks" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "webhooks.purrr.love"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# CDN subdomain
resource "aws_route53_record" "cdn" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "cdn.purrr.love"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with CloudFront alias later
}

# Static assets subdomain
resource "aws_route53_record" "static" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "static.purrr.love"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with S3/CloudFront alias later
}

# Assets subdomain
resource "aws_route53_record" "assets" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "assets.purrr.love"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with S3/CloudFront alias later
}

# TXT records for domain verification and security
resource "aws_route53_record" "spf" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "purrr.love"
  type    = "TXT"
  ttl     = 300
  records = ["v=spf1 include:_spf.google.com ~all"]
}

resource "aws_route53_record" "dmarc" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "_dmarc.purrr.love"
  type    = "TXT"
  ttl     = 300
  records = ["v=DMARC1; p=quarantine; rua=mailto:dmarc@purrr.love"]
}

# CAA records for certificate authority authorization
resource "aws_route53_record" "caa" {
  zone_id = aws_route53_zone.purrr_love.zone_id
  name    = "purrr.love"
  type    = "CAA"
  ttl     = 300
  records = [
    "0 issue \"amazon.com\"",
    "0 issue \"letsencrypt.org\""
  ]
}

# Outputs
output "hosted_zone_id" {
  description = "The hosted zone ID"
  value       = aws_route53_zone.purrr_love.zone_id
}

output "name_servers" {
  description = "The authoritative name servers for the hosted zone"
  value       = aws_route53_zone.purrr_love.name_servers
}

output "domain_delegation_info" {
  description = "Information needed to configure domain registrar"
  value = {
    domain_name    = "purrr.love"
    name_servers   = aws_route53_zone.purrr_love.name_servers
    hosted_zone_id = aws_route53_zone.purrr_love.zone_id
  }
}

output "configured_subdomains" {
  description = "List of all configured subdomains"
  value = [
    "purrr.love",
    "www.purrr.love",
    "api.purrr.love",
    "app.purrr.love", 
    "admin.purrr.love",
    "webhooks.purrr.love",
    "cdn.purrr.love",
    "static.purrr.love",
    "assets.purrr.love"
  ]
}
