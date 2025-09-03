# Standalone Route53 Configuration for purrr.me
# Mirror configuration of purrr.love for interchangeable domain usage

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
      Component   = "dns-alternate"
      Domain      = "purrr.me"
    }
  }
}

# Create hosted zone for purrr.me
resource "aws_route53_zone" "purrr_me" {
  name    = "purrr.me"
  comment = "Hosted zone for Purrr.me production environment - Mirror of purrr.love"

  tags = {
    Name        = "purrr-me-hosted-zone"
    Environment = "production"
    Service     = "Route53"
    Project     = "purrr"
    Domain      = "purrr.me"
  }
}

# Placeholder A record for main domain (will be updated when ALB is deployed)
resource "aws_route53_record" "main" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "purrr.me"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# WWW CNAME record
resource "aws_route53_record" "www" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "www.purrr.me"
  type    = "CNAME"
  ttl     = 300
  records = ["purrr.me"]
}

# API subdomain - main API endpoints
resource "aws_route53_record" "api" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "api.purrr.me"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# App subdomain - application interface
resource "aws_route53_record" "app" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "app.purrr.me"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# Admin subdomain - administrative dashboard
resource "aws_route53_record" "admin" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "admin.purrr.me"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# Webhooks subdomain - enterprise webhook endpoints
resource "aws_route53_record" "webhooks" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "webhooks.purrr.me"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with ALB alias later
}

# CDN subdomain - content delivery network
resource "aws_route53_record" "cdn" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "cdn.purrr.me"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with CloudFront alias later
}

# Static assets subdomain
resource "aws_route53_record" "static" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "static.purrr.me"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with S3/CloudFront alias later
}

# Assets subdomain for media content
resource "aws_route53_record" "assets" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "assets.purrr.me"
  type    = "A"
  ttl     = 300
  records = ["1.2.3.4"]  # Placeholder - will be replaced with S3/CloudFront alias later
}

# TXT records for domain verification and security
resource "aws_route53_record" "spf" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "purrr.me"
  type    = "TXT"
  ttl     = 300
  records = ["v=spf1 include:_spf.google.com ~all"]
}

resource "aws_route53_record" "dmarc" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "_dmarc.purrr.me"
  type    = "TXT"
  ttl     = 300
  records = ["v=DMARC1; p=quarantine; rua=mailto:dmarc@purrr.me"]
}

# CAA records for certificate authority authorization
resource "aws_route53_record" "caa" {
  zone_id = aws_route53_zone.purrr_me.zone_id
  name    = "purrr.me"
  type    = "CAA"
  ttl     = 300
  records = [
    "0 issue \"amazon.com\"",
    "0 issue \"letsencrypt.org\""
  ]
}

# Cross-domain CNAME records for interchangeability (optional)
# Uncomment these if you want explicit cross-domain aliases
# resource "aws_route53_record" "cross_api" {
#   zone_id = aws_route53_zone.purrr_me.zone_id
#   name    = "love.purrr.me"
#   type    = "CNAME"
#   ttl     = 300
#   records = ["api.purrr.love"]
# }

# Outputs
output "hosted_zone_id" {
  description = "The hosted zone ID for purrr.me"
  value       = aws_route53_zone.purrr_me.zone_id
}

output "name_servers" {
  description = "The authoritative name servers for purrr.me hosted zone"
  value       = aws_route53_zone.purrr_me.name_servers
}

output "domain_delegation_info" {
  description = "Information needed to configure purrr.me domain registrar"
  value = {
    domain_name    = "purrr.me"
    name_servers   = aws_route53_zone.purrr_me.name_servers
    hosted_zone_id = aws_route53_zone.purrr_me.zone_id
  }
}

output "configured_subdomains" {
  description = "List of all configured subdomains for purrr.me"
  value = [
    "purrr.me",
    "www.purrr.me",
    "api.purrr.me",
    "app.purrr.me", 
    "admin.purrr.me",
    "webhooks.purrr.me",
    "cdn.purrr.me",
    "static.purrr.me",
    "assets.purrr.me"
  ]
}

# Summary comparison output
output "domain_comparison" {
  description = "Summary showing both domains are configured identically"
  value = {
    primary_domain   = "purrr.love"
    alternate_domain = "purrr.me"
    status          = "identical-configuration"
    interchangeable = true
    subdomains_count = 9
  }
}
