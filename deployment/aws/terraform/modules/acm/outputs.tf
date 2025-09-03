# ACM Certificate Module Outputs

output "certificate_arn" {
  description = "ARN of the validated certificate"
  value       = aws_acm_certificate_validation.purrr_love.certificate_arn
}

output "certificate_domain_name" {
  description = "Domain name of the certificate"
  value       = aws_acm_certificate.purrr_love.domain_name
}

output "certificate_subject_alternative_names" {
  description = "Subject alternative names of the certificate"
  value       = aws_acm_certificate.purrr_love.subject_alternative_names
}

output "certificate_status" {
  description = "Status of the certificate"
  value       = aws_acm_certificate.purrr_love.status
}
