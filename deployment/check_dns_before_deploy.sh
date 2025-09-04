#!/bin/bash
# 🔍 Purrr.love DNS Preservation Check
# Validates current DNS setup before metaverse deployment

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "\n${BLUE}===================================================${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}===================================================${NC}\n"
}

# Check current DNS configuration
check_current_dns() {
    print_header "Current DNS Configuration Check"
    
    # Check if domain resolves
    if dig +short purrr.love A >/dev/null 2>&1; then
        CURRENT_IP=$(dig +short purrr.love A | head -1)
        print_status "Current purrr.love resolves to: $CURRENT_IP"
    else
        print_error "purrr.love does not currently resolve"
        return 1
    fi
    
    # Check current nameservers
    print_status "Current nameservers for purrr.love:"
    dig +short purrr.love NS | sed 's/^/  - /'
    
    # Check if Route53 is being used
    if dig +short purrr.love NS | grep -q "awsdns"; then
        print_status "✅ Already using AWS Route53 nameservers"
        USING_ROUTE53=true
    else
        print_warning "⚠️  Not currently using AWS Route53 nameservers"
        print_warning "   This means DNS is likely configured in GoDaddy"
        USING_ROUTE53=false
    fi
}

# Check AWS Route53 hosted zone
check_aws_route53() {
    print_header "AWS Route53 Configuration Check"
    
    # Check if hosted zone exists
    HOSTED_ZONE_ID=$(aws route53 list-hosted-zones --query "HostedZones[?Name=='purrr.love.'].Id" --output text 2>/dev/null | cut -d'/' -f3)
    
    if [ -n "$HOSTED_ZONE_ID" ] && [ "$HOSTED_ZONE_ID" != "None" ]; then
        print_status "✅ Route53 hosted zone exists: $HOSTED_ZONE_ID"
        
        # Get AWS nameservers
        print_status "AWS Route53 nameservers:"
        aws route53 get-hosted-zone --id "$HOSTED_ZONE_ID" --query 'DelegationSet.NameServers' --output text | tr '\t' '\n' | sed 's/^/  - /'
        
        # Check if current DNS records exist
        print_status "Current DNS records in Route53:"
        aws route53 list-resource-record-sets --hosted-zone-id "$HOSTED_ZONE_ID" --query 'ResourceRecordSets[?Type==`A` || Type==`CNAME`].[Name,Type,ResourceRecords[0].Value]' --output table
        
    else
        print_warning "⚠️  No Route53 hosted zone found for purrr.love"
        print_warning "   A hosted zone will be created during deployment"
    fi
}

# Check current website availability
check_website_availability() {
    print_header "Website Availability Check"
    
    # Test main domain
    if curl -Is "http://purrr.love" >/dev/null 2>&1; then
        print_status "✅ http://purrr.love is accessible"
    else
        print_warning "⚠️  http://purrr.love is not accessible"
    fi
    
    if curl -Is "https://purrr.love" >/dev/null 2>&1; then
        print_status "✅ https://purrr.love is accessible"
    else
        print_warning "⚠️  https://purrr.love is not accessible"
    fi
    
    # Check for specific endpoints
    endpoints=("/" "/health" "/api/health" "/web/index.php")
    for endpoint in "${endpoints[@]}"; do
        if curl -Is "http://purrr.love$endpoint" >/dev/null 2>&1; then
            print_status "✅ Endpoint $endpoint is accessible"
        else
            print_warning "⚠️  Endpoint $endpoint is not accessible"
        fi
    done
}

# Check existing AWS infrastructure
check_aws_infrastructure() {
    print_header "AWS Infrastructure Check"
    
    # Check ECS cluster
    CLUSTER_NAME="production-purrr-cluster"
    if aws ecs describe-clusters --clusters "$CLUSTER_NAME" --region us-east-1 | grep -q "ACTIVE" 2>/dev/null; then
        print_status "✅ ECS cluster '$CLUSTER_NAME' exists and is active"
    else
        print_warning "⚠️  ECS cluster '$CLUSTER_NAME' not found"
    fi
    
    # Check ALB
    ALB_NAME="production-purrr-alb"
    if aws elbv2 describe-load-balancers --names "$ALB_NAME" --region us-east-1 >/dev/null 2>&1; then
        print_status "✅ Application Load Balancer '$ALB_NAME' exists"
        ALB_DNS=$(aws elbv2 describe-load-balancers --names "$ALB_NAME" --query 'LoadBalancers[0].DNSName' --output text --region us-east-1)
        print_status "   ALB DNS: $ALB_DNS"
    else
        print_warning "⚠️  Application Load Balancer '$ALB_NAME' not found"
    fi
    
    # Check RDS
    if aws rds describe-db-instances --region us-east-1 | grep -q "production-purrr" 2>/dev/null; then
        print_status "✅ RDS database instance found"
    else
        print_warning "⚠️  RDS database instance not found"
    fi
}

# Generate DNS migration plan
generate_dns_migration_plan() {
    print_header "DNS Migration Strategy"
    
    if [ "$USING_ROUTE53" = true ]; then
        print_status "✅ No DNS migration needed - already using Route53"
        print_status "   Deployment will preserve existing DNS configuration"
    else
        print_warning "📋 DNS Migration Required:"
        echo "   1. Deployment will create Route53 hosted zone"
        echo "   2. You'll need to update nameservers in GoDaddy after deployment"
        echo "   3. Current DNS will continue working during migration"
        echo "   4. Migration should be seamless with no downtime"
        
        print_warning "⚠️  Important: Save current GoDaddy DNS settings as backup"
        print_warning "   In case rollback is needed"
    fi
}

# Check prerequisites
check_prerequisites() {
    print_header "Prerequisites Check"
    
    # Check AWS CLI
    if command -v aws >/dev/null 2>&1; then
        print_status "✅ AWS CLI is installed"
        if aws sts get-caller-identity >/dev/null 2>&1; then
            print_status "✅ AWS credentials are configured"
        else
            print_error "❌ AWS credentials not configured"
            exit 1
        fi
    else
        print_error "❌ AWS CLI not installed"
        exit 1
    fi
    
    # Check dig command
    if command -v dig >/dev/null 2>&1; then
        print_status "✅ dig command available"
    else
        print_warning "⚠️  dig command not available, using nslookup fallback"
    fi
    
    # Check curl
    if command -v curl >/dev/null 2>&1; then
        print_status "✅ curl is available"
    else
        print_error "❌ curl not available"
        exit 1
    fi
}

# Backup current DNS configuration
backup_dns_config() {
    print_header "DNS Configuration Backup"
    
    # Create backup directory
    mkdir -p dns_backup
    
    # Backup current DNS records
    echo "# DNS Backup - $(date)" > dns_backup/current_dns.txt
    echo "# Domain: purrr.love" >> dns_backup/current_dns.txt
    echo "" >> dns_backup/current_dns.txt
    
    echo "Current IP Address:" >> dns_backup/current_dns.txt
    dig +short purrr.love A >> dns_backup/current_dns.txt 2>/dev/null || echo "Could not resolve" >> dns_backup/current_dns.txt
    
    echo "" >> dns_backup/current_dns.txt
    echo "Current Nameservers:" >> dns_backup/current_dns.txt
    dig +short purrr.love NS >> dns_backup/current_dns.txt 2>/dev/null || echo "Could not get nameservers" >> dns_backup/current_dns.txt
    
    print_status "✅ DNS configuration backed up to dns_backup/current_dns.txt"
}

# Main check function
main() {
    print_header "🔍 Purrr.love Pre-Deployment DNS Check"
    print_status "Checking current configuration before metaverse deployment"
    
    check_prerequisites
    backup_dns_config
    check_current_dns
    check_aws_route53
    check_website_availability
    check_aws_infrastructure
    generate_dns_migration_plan
    
    print_header "Summary & Recommendations"
    
    if [ "$USING_ROUTE53" = true ]; then
        print_status "🎉 READY FOR DEPLOYMENT"
        print_status "   - DNS is already using Route53"
        print_status "   - No DNS changes required"
        print_status "   - Deployment will preserve current setup"
    else
        print_warning "⚠️  DNS MIGRATION REQUIRED"
        print_warning "   - Currently using non-AWS nameservers"
        print_warning "   - Deployment will work but require nameserver update"
        print_warning "   - Have GoDaddy credentials ready for nameserver update"
    fi
    
    echo -e "\n${GREEN}🚀 Ready to deploy? Run:${NC}"
    echo "   ./deployment/deploy_metaverse_update.sh"
    
    echo -e "\n${YELLOW}📋 Post-deployment checklist:${NC}"
    echo "   1. Update GoDaddy nameservers if needed"
    echo "   2. Wait for DNS propagation (up to 48 hours)"
    echo "   3. Test all subdomains"
    echo "   4. Monitor CloudWatch logs"
    echo "   5. Test metaverse automation features"
}

# Run the check
main
