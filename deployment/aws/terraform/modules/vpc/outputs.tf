# VPC Outputs
output "vpc_id" {
  description = "ID of the VPC"
  value       = aws_vpc.main.id
}

output "vpc_cidr_block" {
  description = "CIDR block of the VPC"
  value       = aws_vpc.main.cidr_block
}

output "vpc_arn" {
  description = "ARN of the VPC"
  value       = aws_vpc.main.arn
}

# Internet Gateway
output "internet_gateway_id" {
  description = "ID of the Internet Gateway"
  value       = aws_internet_gateway.main.id
}

# Public Subnet Outputs
output "public_subnet_ids" {
  description = "List of IDs of the public subnets"
  value       = aws_subnet.public[*].id
}

output "public_subnet_cidrs" {
  description = "List of CIDR blocks of the public subnets"
  value       = aws_subnet.public[*].cidr_block
}

output "public_subnet_arns" {
  description = "List of ARNs of the public subnets"
  value       = aws_subnet.public[*].arn
}

# Private Subnet Outputs
output "private_subnet_ids" {
  description = "List of IDs of the private subnets"
  value       = aws_subnet.private[*].id
}

output "private_subnet_cidrs" {
  description = "List of CIDR blocks of the private subnets"
  value       = aws_subnet.private[*].cidr_block
}

output "private_subnet_arns" {
  description = "List of ARNs of the private subnets"
  value       = aws_subnet.private[*].arn
}

# Database Subnet Outputs
output "database_subnet_ids" {
  description = "List of IDs of the database subnets"
  value       = aws_subnet.database[*].id
}

output "database_subnet_cidrs" {
  description = "List of CIDR blocks of the database subnets"
  value       = aws_subnet.database[*].cidr_block
}

output "database_subnet_group_id" {
  description = "ID of the database subnet group"
  value       = aws_db_subnet_group.main.id
}

output "database_subnet_group_arn" {
  description = "ARN of the database subnet group"
  value       = aws_db_subnet_group.main.arn
}

# NAT Gateway Outputs
output "nat_gateway_ids" {
  description = "List of IDs of the NAT Gateways"
  value       = var.enable_nat_gateway ? aws_nat_gateway.main[*].id : []
}

output "nat_public_ips" {
  description = "List of public Elastic IPs of NAT Gateways"
  value       = var.enable_nat_gateway ? aws_eip.nat[*].public_ip : []
}

# Route Table Outputs
output "public_route_table_ids" {
  description = "List of IDs of the public route tables"
  value       = [aws_route_table.public.id]
}

output "private_route_table_ids" {
  description = "List of IDs of the private route tables"
  value       = var.enable_nat_gateway ? aws_route_table.private[*].id : []
}

output "database_route_table_ids" {
  description = "List of IDs of the database route tables"
  value       = [aws_route_table.database.id]
}

# VPC Endpoints Outputs
output "s3_vpc_endpoint_id" {
  description = "ID of the S3 VPC endpoint"
  value       = var.enable_vpc_endpoints ? aws_vpc_endpoint.s3[0].id : null
}

output "dynamodb_vpc_endpoint_id" {
  description = "ID of the DynamoDB VPC endpoint"
  value       = var.enable_vpc_endpoints ? aws_vpc_endpoint.dynamodb[0].id : null
}

# Availability Zones
output "availability_zones" {
  description = "List of availability zones used"
  value       = var.availability_zones
}

# Flow Logs
output "vpc_flow_log_id" {
  description = "ID of the VPC Flow Log"
  value       = var.enable_flow_logs ? aws_flow_log.vpc[0].id : null
}

output "vpc_flow_log_destination_arn" {
  description = "ARN of the destination for VPC Flow Logs"
  value       = var.enable_flow_logs ? aws_cloudwatch_log_group.vpc_flow_log[0].arn : null
}

# Network ACLs
output "public_network_acl_id" {
  description = "ID of the public network ACL"
  value       = aws_network_acl.public.id
}

output "private_network_acl_id" {
  description = "ID of the private network ACL"
  value       = aws_network_acl.private.id
}

output "database_network_acl_id" {
  description = "ID of the database network ACL"
  value       = aws_network_acl.database.id
}
