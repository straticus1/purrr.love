# Route53 DNS Module Variables

variable "domain_name" {
  description = "The domain name to create DNS records for"
  type        = string
  default     = ""
}

variable "project_name" {
  description = "Project name for resource naming"
  type        = string
  default     = "purrr"
}

variable "environment" {
  description = "Environment name (production, staging, development)"
  type        = string
  default     = "production"
}

variable "create_hosted_zone" {
  description = "Whether to create a new hosted zone or use existing one"
  type        = bool
  default     = true
}

variable "private_zone" {
  description = "Whether this is a private hosted zone"
  type        = bool
  default     = false
}

variable "vpc_ids" {
  description = "VPC IDs to associate with private hosted zone"
  type = list(object({
    vpc_id     = string
    vpc_region = string
  }))
  default = []
}

# Load balancer configuration for A/AAAA records
variable "load_balancer_dns_name" {
  description = "DNS name of the load balancer"
  type        = string
  default     = ""
}

variable "load_balancer_zone_id" {
  description = "Hosted zone ID of the load balancer"
  type        = string
  default     = ""
}

# CloudFront configuration for CDN subdomain
variable "cloudfront_dns_name" {
  description = "DNS name of the CloudFront distribution"
  type        = string
  default     = ""
}

variable "cloudfront_zone_id" {
  description = "Hosted zone ID of the CloudFront distribution"
  type        = string
  default     = ""
}

# Subdomain configuration
variable "subdomains" {
  description = "Map of subdomains to create"
  type = map(object({
    type    = string
    ttl     = number
    records = list(string)
    alias_to_alb = optional(bool, false)
    alias_to_cloudfront = optional(bool, false)
  }))
  default = {}
}

# WWW subdomain
variable "create_www_record" {
  description = "Whether to create www CNAME record"
  type        = bool
  default     = true
}

variable "cname_ttl" {
  description = "TTL for CNAME records"
  type        = number
  default     = 300
}

# IPv6 support
variable "enable_ipv6" {
  description = "Whether to create AAAA records for IPv6 support"
  type        = bool
  default     = true
}

# Email configuration
variable "mx_records" {
  description = "MX records for email"
  type        = list(string)
  default     = []
}

variable "mx_ttl" {
  description = "TTL for MX records"
  type        = number
  default     = 300
}

# TXT records (SPF, DKIM, DMARC, etc.)
variable "txt_records" {
  description = "TXT records to create"
  type        = map(list(string))
  default     = {}
}

variable "txt_ttl" {
  description = "TTL for TXT records"
  type        = number
  default     = 300
}

# CAA records for certificate authority authorization
variable "caa_records" {
  description = "CAA records for certificate authority authorization"
  type        = list(string)
  default     = []
}

variable "caa_ttl" {
  description = "TTL for CAA records"
  type        = number
  default     = 300
}

# Health checks
variable "enable_health_checks" {
  description = "Whether to enable Route53 health checks"
  type        = bool
  default     = false
}

variable "health_check_port" {
  description = "Port for health check"
  type        = number
  default     = 443
}

variable "health_check_type" {
  description = "Type of health check"
  type        = string
  default     = "HTTPS"
}

variable "health_check_path" {
  description = "Path for health check"
  type        = string
  default     = "/health"
}

variable "health_check_failure_threshold" {
  description = "Number of consecutive failures before marking unhealthy"
  type        = number
  default     = 3
}

variable "health_check_request_interval" {
  description = "Request interval for health check (30 or 10 seconds)"
  type        = number
  default     = 30
}

variable "health_check_measure_latency" {
  description = "Whether to measure latency"
  type        = bool
  default     = true
}

variable "health_check_invert" {
  description = "Whether to invert health check"
  type        = bool
  default     = false
}

variable "health_check_enable_sni" {
  description = "Whether to enable SNI for health check"
  type        = bool
  default     = true
}

variable "health_check_cloudwatch_alarm_region" {
  description = "CloudWatch alarm region for health check"
  type        = string
  default     = "us-east-1"
}

variable "health_check_insufficient_data_status" {
  description = "Status when insufficient data"
  type        = string
  default     = "Failure"
}

variable "health_check_alarm_actions" {
  description = "Actions to take when health check alarm triggers"
  type        = list(string)
  default     = []
}

# Certificate validation
variable "certificate_validation_options" {
  description = "Certificate validation options for ACM"
  type = map(object({
    resource_record_name  = string
    resource_record_type  = string
    resource_record_value = string
  }))
  default = {}
}

# Common tags
variable "common_tags" {
  description = "Common tags to apply to all resources"
  type        = map(string)
  default     = {}
}
