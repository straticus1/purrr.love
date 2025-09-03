# Route53 DNS Module Outputs

# Hosted Zone Information
output "hosted_zone_id" {
  description = "The hosted zone ID"
  value       = local.hosted_zone_id
}

output "hosted_zone_name" {
  description = "The hosted zone name"
  value       = var.domain_name
}

output "name_servers" {
  description = "The authoritative name servers for the hosted zone"
  value = var.domain_name != "" && var.create_hosted_zone ? (
    length(aws_route53_zone.main) > 0 ? aws_route53_zone.main[0].name_servers : []
  ) : (
    length(data.aws_route53_zone.main) > 0 ? data.aws_route53_zone.main[0].name_servers : []
  )
}

# Domain records
output "main_domain_fqdn" {
  description = "The FQDN of the main domain A record"
  value       = var.domain_name != "" && length(aws_route53_record.main) > 0 ? aws_route53_record.main[0].fqdn : ""
}

output "www_domain_fqdn" {
  description = "The FQDN of the www subdomain CNAME record"
  value       = var.domain_name != "" && length(aws_route53_record.www) > 0 ? aws_route53_record.www[0].fqdn : ""
}

# Subdomain records
output "subdomain_fqdns" {
  description = "Map of subdomain names to their FQDNs"
  value = {
    for k, v in aws_route53_record.subdomains : k => v.fqdn
  }
}

# Health check information
output "health_check_id" {
  description = "The health check ID"
  value       = length(aws_route53_health_check.main) > 0 ? aws_route53_health_check.main[0].id : ""
}

output "health_check_alarm_id" {
  description = "The CloudWatch alarm ID for health check"
  value       = length(aws_cloudwatch_metric_alarm.health_check_alarm) > 0 ? aws_cloudwatch_metric_alarm.health_check_alarm[0].id : ""
}

# Zone delegation information for registrar configuration
output "delegation_set" {
  description = "Information needed to configure domain registrar"
  value = var.domain_name != "" ? {
    domain_name = var.domain_name
    name_servers = var.create_hosted_zone ? (
      length(aws_route53_zone.main) > 0 ? aws_route53_zone.main[0].name_servers : []
    ) : (
      length(data.aws_route53_zone.main) > 0 ? data.aws_route53_zone.main[0].name_servers : []
    )
    hosted_zone_id = local.hosted_zone_id
  } : null
}
