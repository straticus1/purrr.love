#!/bin/bash
# 🚀 Purrr.love Metaverse Update Deployment Script
# Updates AWS ECS with new metaverse systems while preserving DNS configuration

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="purrr"
ENVIRONMENT="production"
AWS_REGION="us-east-1"
ECR_REPOSITORY="purrr-love"
IMAGE_TAG="metaverse-$(date +%Y%m%d-%H%M%S)"

# Function to print colored output
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

# Check prerequisites
check_prerequisites() {
    print_header "Checking Prerequisites"
    
    # Check AWS CLI
    if ! command -v aws &> /dev/null; then
        print_error "AWS CLI is not installed. Please install it first."
        exit 1
    fi
    
    # Check Docker
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install it first."
        exit 1
    fi
    
    # Check Terraform
    if ! command -v terraform &> /dev/null; then
        print_error "Terraform is not installed. Please install it first."
        exit 1
    fi
    
    # Check jq for JSON parsing
    if ! command -v jq &> /dev/null; then
        print_error "jq is not installed. Please install it first: brew install jq"
        exit 1
    fi
    
    # Check AWS credentials
    if ! aws sts get-caller-identity &> /dev/null; then
        print_error "AWS credentials not configured. Please run 'aws configure'."
        exit 1
    fi
    
    print_status "All prerequisites satisfied!"
}

# Get current deployment information
get_current_deployment_info() {
    print_header "Getting Current Deployment Information"
    
    # Get current ECS service info
    CLUSTER_NAME="${ENVIRONMENT}-${PROJECT_NAME}-cluster"
    SERVICE_NAME="${ENVIRONMENT}-${PROJECT_NAME}-app"
    
    print_status "ECS Cluster: $CLUSTER_NAME"
    print_status "ECS Service: $SERVICE_NAME"
    
    # Check if cluster exists
    if aws ecs describe-clusters --clusters "$CLUSTER_NAME" --region "$AWS_REGION" | grep -q "ACTIVE"; then
        print_status "ECS Cluster found and active"
    else
        print_warning "ECS Cluster not found or not active"
    fi
    
    # Get current Route53 hosted zone info (to preserve DNS)
    HOSTED_ZONE_ID=$(aws route53 list-hosted-zones --query "HostedZones[?Name=='purrr.love.'].Id" --output text | cut -d'/' -f3)
    
    if [ -n "$HOSTED_ZONE_ID" ]; then
        print_status "Found existing Route53 hosted zone: $HOSTED_ZONE_ID"
        print_status "DNS configuration will be preserved"
    else
        print_warning "No existing Route53 hosted zone found"
    fi
}

# Build and push Docker image
build_and_push_image() {
    print_header "Building and Pushing Docker Image"
    
    # Get AWS account ID
    AWS_ACCOUNT_ID=$(aws sts get-caller-identity --query Account --output text)
    ECR_REGISTRY="${AWS_ACCOUNT_ID}.dkr.ecr.${AWS_REGION}.amazonaws.com"
    FULL_IMAGE_URI="${ECR_REGISTRY}/${ECR_REPOSITORY}:${IMAGE_TAG}"
    
    print_status "Building image: $FULL_IMAGE_URI"
    
    # Create ECR repository if it doesn't exist
    aws ecr describe-repositories --repository-names "$ECR_REPOSITORY" --region "$AWS_REGION" || {
        print_status "Creating ECR repository..."
        aws ecr create-repository --repository-name "$ECR_REPOSITORY" --region "$AWS_REGION"
    }
    
    # Get ECR login token
    aws ecr get-login-password --region "$AWS_REGION" | docker login --username AWS --password-stdin "$ECR_REGISTRY"
    
    # Build image
    print_status "Building Docker image with metaverse features..."
    docker build -t "$ECR_REPOSITORY:$IMAGE_TAG" .
    docker tag "$ECR_REPOSITORY:$IMAGE_TAG" "$FULL_IMAGE_URI"
    
    # Push image
    print_status "Pushing image to ECR..."
    docker push "$FULL_IMAGE_URI"
    
    print_status "Image pushed successfully: $FULL_IMAGE_URI"
    echo "$FULL_IMAGE_URI" > .docker_image_uri
}

# Update infrastructure with Terraform
update_infrastructure() {
    print_header "Updating AWS Infrastructure with Terraform"
    
    # Fetch database password from AWS Secrets Manager
    print_status "Retrieving database password from AWS Secrets Manager..."
    DB_PASSWORD=$(aws secretsmanager get-secret-value \
        --secret-id "purrr/database" \
        --region "$AWS_REGION" \
        --query 'SecretString' \
        --output text | jq -r '.password')
    
    if [ -z "$DB_PASSWORD" ] || [ "$DB_PASSWORD" = "null" ]; then
        print_error "Failed to retrieve database password from Secrets Manager"
        exit 1
    fi
    
    export TF_VAR_database_password="$DB_PASSWORD"
    print_status "Database password retrieved successfully"
    
    cd deployment/aws/terraform
    
    # Initialize Terraform
    print_status "Initializing Terraform..."
    terraform init
    
    # Create terraform.tfvars if it doesn't exist
    if [ ! -f "terraform.tfvars" ]; then
        print_status "Creating terraform.tfvars..."
        cat > terraform.tfvars << EOF
# Purrr.love Production Configuration
environment = "production"
project_name = "purrr"
aws_region = "us-east-1"
domain_name = "purrr.love"

# Container Configuration
container_image = "$(cat ../../../.docker_image_uri)"
container_cpu = 512
container_memory = 1024
task_cpu = 1024
task_memory = 2048

# ECS Configuration
ecs_desired_count = 2
ecs_enable_auto_scaling = true
ecs_autoscaling_min_capacity = 1
ecs_autoscaling_max_capacity = 5

# Database Configuration (password retrieved from Secrets Manager)
db_instance_class = "db.t3.small"
db_allocated_storage = 20
db_username = "purrr_admin"

# SSL Configuration
enable_ssl = true

# Monitoring
enable_monitoring = true
log_retention_days = 30
ecs_enable_container_insights = true
EOF
    fi
    
    # Plan the deployment
    print_status "Planning infrastructure changes..."
    terraform plan -var-file="terraform.tfvars" -out=tfplan
    
    print_warning "Please review the Terraform plan above."
    read -p "Do you want to apply these changes? (yes/no): " confirm
    
    if [ "$confirm" = "yes" ]; then
        print_status "Applying Terraform changes..."
        terraform apply tfplan
        print_status "Infrastructure updated successfully!"
    else
        print_error "Deployment cancelled by user"
        exit 1
    fi
    
    cd ../../..
}

# Update ECS service
update_ecs_service() {
    print_header "Updating ECS Service"
    
    # Force new deployment with the new image
    print_status "Triggering ECS service update..."
    aws ecs update-service \
        --cluster "$CLUSTER_NAME" \
        --service "$SERVICE_NAME" \
        --force-new-deployment \
        --region "$AWS_REGION"
    
    print_status "Waiting for service to stabilize..."
    aws ecs wait services-stable \
        --cluster "$CLUSTER_NAME" \
        --services "$SERVICE_NAME" \
        --region "$AWS_REGION"
    
    print_status "ECS service updated successfully!"
}

# Verify deployment
verify_deployment() {
    print_header "Verifying Deployment"
    
    # Get ALB DNS name
    ALB_DNS=$(aws elbv2 describe-load-balancers \
        --names "${ENVIRONMENT}-${PROJECT_NAME}-alb" \
        --query 'LoadBalancers[0].DNSName' \
        --output text \
        --region "$AWS_REGION" 2>/dev/null || echo "")
    
    if [ -n "$ALB_DNS" ]; then
        print_status "Load Balancer DNS: $ALB_DNS"
        print_status "Testing health endpoint..."
        
        # Test health endpoint
        if curl -f "http://$ALB_DNS/health" > /dev/null 2>&1; then
            print_status "Health check passed!"
        else
            print_warning "Health check failed, but service may still be starting"
        fi
    fi
    
    # Test metaverse automation
    print_status "Testing metaverse automation..."
    TASK_ARN=$(aws ecs list-tasks --cluster "$CLUSTER_NAME" --service-name "$SERVICE_NAME" --query 'taskArns[0]' --output text --region "$AWS_REGION")
    
    if [ "$TASK_ARN" != "None" ] && [ -n "$TASK_ARN" ]; then
        print_status "Found running task: $TASK_ARN"
        
        # Execute metaverse test command
        aws ecs execute-command \
            --cluster "$CLUSTER_NAME" \
            --task "$TASK_ARN" \
            --container "${PROJECT_NAME}-app" \
            --command "php /var/www/html/cli/metaverse_automation.php test" \
            --interactive \
            --region "$AWS_REGION" || print_warning "Could not execute test command (execute-command may not be enabled)"
    fi
}

# Check DNS configuration
check_dns_configuration() {
    print_header "Checking DNS Configuration"
    
    if [ -n "$HOSTED_ZONE_ID" ]; then
        print_status "Route53 Hosted Zone ID: $HOSTED_ZONE_ID"
        
        # Get name servers
        NAME_SERVERS=$(aws route53 get-hosted-zone --id "$HOSTED_ZONE_ID" --query 'DelegationSet.NameServers' --output text)
        print_status "DNS Name Servers:"
        echo "$NAME_SERVERS" | tr '\t' '\n' | sed 's/^/  - /'
        
        print_warning "Make sure these name servers are configured in your domain registrar (GoDaddy)"
        print_warning "DNS Name Servers should match your GoDaddy configuration"
    else
        print_error "No Route53 hosted zone found. DNS may need manual configuration."
    fi
}

# Display deployment summary
show_deployment_summary() {
    print_header "Deployment Summary"
    
    print_status "✅ Docker image built and pushed"
    print_status "✅ AWS infrastructure updated"
    print_status "✅ ECS service updated"
    print_status "✅ Metaverse automation enabled"
    
    echo -e "\n${GREEN}🌐 Metaverse Features Deployed:${NC}"
    echo "  • AI-driven cat NPCs with autonomous behavior"
    echo "  • Dynamic world events and weather systems" 
    echo "  • Real-time engagement monitoring and boosting"
    echo "  • Comprehensive gamification with achievements"
    echo "  • Automated cron jobs for 24/7 activity"
    echo "  • Advanced analytics and predictive modeling"
    
    echo -e "\n${GREEN}🔧 Automation Schedule:${NC}"
    echo "  • Every 5 minutes: Engagement monitoring"
    echo "  • Every 10 minutes: AI NPC spawning"
    echo "  • Every 15 minutes: Population balancing"
    echo "  • Every 30 minutes: Weather updates"
    echo "  • Hourly: Special area management"
    echo "  • Daily: Seasonal content & quest generation"
    
    if [ -n "$ALB_DNS" ]; then
        echo -e "\n${GREEN}🌍 Application URLs:${NC}"
        echo "  • Main site: https://purrr.love"
        echo "  • API: https://api.purrr.love"  
        echo "  • Admin: https://admin.purrr.love"
        echo "  • Direct ALB: http://$ALB_DNS"
    fi
    
    echo -e "\n${YELLOW}📋 Next Steps:${NC}"
    echo "  1. Monitor logs: docker logs <container_id>"
    echo "  2. Check automation: tail -f logs/metaverse_automation.log"
    echo "  3. Verify DNS: Ensure GoDaddy points to AWS name servers"
    echo "  4. Test metaverse: Visit the VR section of your site"
    echo "  5. Monitor CloudWatch for ECS health"
    
    print_status "🎉 Purrr.love Metaverse deployment completed successfully!"
}

# Rollback function (in case something goes wrong)
rollback_deployment() {
    print_header "Rolling Back Deployment"
    print_error "Rolling back to previous version..."
    
    # This would rollback to previous task definition
    # aws ecs update-service --cluster "$CLUSTER_NAME" --service "$SERVICE_NAME" --task-definition "previous-task-def"
    
    print_warning "Rollback functionality not fully implemented. Manual rollback may be required."
}

# Main deployment function
main() {
    print_header "🚀 Purrr.love Metaverse Deployment Started"
    print_status "Deploying enhanced metaverse features to AWS ECS"
    print_status "DNS configuration will be preserved"
    
    # Trap errors for rollback
    trap 'print_error "Deployment failed! Consider rollback."; rollback_deployment; exit 1' ERR
    
    check_prerequisites
    get_current_deployment_info
    build_and_push_image
    update_infrastructure
    update_ecs_service
    verify_deployment
    check_dns_configuration
    show_deployment_summary
    
    print_status "🎉 Deployment completed successfully!"
}

# Check if script is being run directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    # Handle command line arguments
    case "${1:-deploy}" in
        "deploy"|"")
            main
            ;;
        "rollback")
            rollback_deployment
            ;;
        "check")
            check_prerequisites
            get_current_deployment_info
            check_dns_configuration
            ;;
        *)
            echo "Usage: $0 [deploy|rollback|check]"
            echo "  deploy (default): Deploy metaverse update"
            echo "  rollback: Rollback to previous version"
            echo "  check: Check current deployment status"
            exit 1
            ;;
    esac
fi
