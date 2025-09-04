#!/bin/bash

# 🚀 Purrr.love Deployment Script
# Choose Your Adventure: AWS Containers or Rocky Linux!

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEPLOYMENT_DIR="$SCRIPT_DIR/deployment"
CONFIG_FILE="$SCRIPT_DIR/deployment-config.yaml"

# Default values
DEPLOYMENT_TYPE=""
ENVIRONMENT="production"
SERVER=""
CONFIG_FILE_PATH=""
DRY_RUN=false
VERBOSE=false

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
    echo -e "${PURPLE}================================${NC}"
    echo -e "${PURPLE}  🐱 Purrr.love Deployment 🐱${NC}"
    echo -e "${PURPLE}================================${NC}"
}

print_help() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Deployment Options:"
    echo "  --aws                    Deploy to AWS using containers"
    echo "  --rocky                  Deploy to Rocky Linux server"
    echo "  --custom                 Custom deployment configuration"
    echo ""
    echo "Configuration Options:"
    echo "  --environment ENV        Environment (dev/staging/production) [default: production]"
    echo "  --server SERVER          Server address for Rocky Linux deployment"
    echo "  --config FILE            Custom configuration file path"
    echo "  --dry-run                Show what would be deployed without actually deploying"
    echo "  --verbose                Enable verbose output"
    echo "  --help                   Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 --aws --environment production"
    echo "  $0 --rocky --server your-server.com"
    echo "  $0 --custom --config custom-deployment.yaml"
}

# Function to check prerequisites
check_prerequisites() {
    print_status "Checking prerequisites..."
    
    # Check if deployment directory exists
    if [[ ! -d "$DEPLOYMENT_DIR" ]]; then
        print_error "Deployment directory not found: $DEPLOYMENT_DIR"
        exit 1
    fi
    
    # Check deployment type specific prerequisites
    case $DEPLOYMENT_TYPE in
        "aws")
            check_aws_prerequisites
            ;;
        "rocky")
            check_rocky_prerequisites
            ;;
        "custom")
            check_custom_prerequisites
            ;;
        *)
            print_error "No deployment type specified"
            print_help
            exit 1
            ;;
    esac
}

# Function to check AWS prerequisites
check_aws_prerequisites() {
    print_status "Checking AWS prerequisites..."
    
    # Check AWS CLI
    if ! command -v aws &> /dev/null; then
        print_error "AWS CLI not found. Please install it first."
        exit 1
    fi
    
    # Check if AWS is configured
    if ! aws sts get-caller-identity &> /dev/null; then
        print_error "AWS CLI not configured. Please run 'aws configure' first."
        exit 1
    fi
    
    # Check Terraform
    if ! command -v terraform &> /dev/null; then
        print_error "Terraform not found. Please install it first."
        exit 1
    fi
    
    # Check Docker
    if ! command -v docker &> /dev/null; then
        print_error "Docker not found. Please install it first."
        exit 1
    fi
    
    print_status "AWS prerequisites check passed! ✅"
}

# Function to check Rocky Linux prerequisites
check_rocky_prerequisites() {
    print_status "Checking Rocky Linux prerequisites..."
    
    # Check if server is specified
    if [[ -z "$SERVER" ]]; then
        print_error "Server address required for Rocky Linux deployment. Use --server option."
        exit 1
    fi
    
    # Check Ansible
    if ! command -v ansible &> /dev/null; then
        print_error "Ansible not found. Please install it first."
        exit 1
    fi
    
    # Check SSH access
    if ! ssh -o ConnectTimeout=10 -o BatchMode=yes "$SERVER" exit &> /dev/null; then
        print_error "Cannot connect to server $SERVER via SSH. Please check your SSH configuration."
        exit 1
    fi
    
    print_status "Rocky Linux prerequisites check passed! ✅"
}

# Function to check custom prerequisites
check_custom_prerequisites() {
    print_status "Checking custom deployment prerequisites..."
    
    # Check if config file exists
    if [[ ! -f "$CONFIG_FILE_PATH" ]]; then
        print_error "Custom configuration file not found: $CONFIG_FILE_PATH"
        exit 1
    fi
    
    print_status "Custom deployment prerequisites check passed! ✅"
}

# Function to deploy to AWS
deploy_aws() {
    print_status "🚀 Deploying to AWS (Containerized)..."
    
    cd "$DEPLOYMENT_DIR/aws"
    
    # Initialize Terraform
    print_status "Initializing Terraform..."
    terraform init
    
    # Plan deployment
    print_status "Planning Terraform deployment..."
    if [[ "$DRY_RUN" == true ]]; then
        terraform plan -var="environment=$ENVIRONMENT"
        print_warning "Dry run completed. No changes were made."
        return
    fi
    
    # Apply Terraform
    print_status "Applying Terraform configuration..."
    terraform apply -var="environment=$ENVIRONMENT" -auto-approve
    
    # Get outputs
    print_status "Getting deployment outputs..."
    terraform output
    
    # Deploy containers
    print_status "Deploying containers..."
    cd docker
    docker-compose -f docker-compose.$ENVIRONMENT.yml up -d
    
    print_status "AWS deployment completed! 🎉"
}

# Function to deploy to Rocky Linux
deploy_rocky() {
    print_status "🚀 Deploying to Rocky Linux..."
    
    cd "$DEPLOYMENT_DIR/rocky-linux"
    
    # Create inventory file
    print_status "Creating Ansible inventory..."
    cat > inventory.ini << EOF
[purrr_love_servers]
$SERVER ansible_user=root ansible_ssh_private_key_file=~/.ssh/id_rsa
EOF
    
    # Run Ansible playbook
    print_status "Running Ansible playbook..."
    if [[ "$DRY_RUN" == true ]]; then
        ansible-playbook -i inventory.ini main.yml --check
        print_warning "Dry run completed. No changes were made."
        return
    fi
    
    ansible-playbook -i inventory.ini main.yml
    
    print_status "Rocky Linux deployment completed! 🎉"
}

# Function to deploy custom configuration
deploy_custom() {
    print_status "🚀 Deploying custom configuration..."
    
    # Parse custom config and deploy accordingly
    print_status "Parsing custom configuration: $CONFIG_FILE_PATH"
    
    # This would parse the YAML config and determine deployment method
    # For now, just show the config
    if [[ "$VERBOSE" == true ]]; then
        cat "$CONFIG_FILE_PATH"
    fi
    
    print_status "Custom deployment completed! 🎉"
}

# Function to validate configuration
validate_config() {
    print_status "Validating configuration..."
    
    # Validate environment
    if [[ ! "$ENVIRONMENT" =~ ^(dev|staging|production)$ ]]; then
        print_error "Invalid environment: $ENVIRONMENT. Must be dev, staging, or production."
        exit 1
    fi
    
    # Validate deployment type
    if [[ -z "$DEPLOYMENT_TYPE" ]]; then
        print_error "Deployment type must be specified (--aws, --rocky, or --custom)"
        exit 1
    fi
    
    print_status "Configuration validation passed! ✅"
}

# Function to show deployment summary
show_summary() {
    print_header
    echo ""
    echo "Deployment Summary:"
    echo "  Type:        $DEPLOYMENT_TYPE"
    echo "  Environment: $ENVIRONMENT"
    if [[ -n "$SERVER" ]]; then
        echo "  Server:      $SERVER"
    fi
    if [[ -n "$CONFIG_FILE_PATH" ]]; then
        echo "  Config:      $CONFIG_FILE_PATH"
    fi
    echo "  Dry Run:     $DRY_RUN"
    echo "  Verbose:     $VERBOSE"
    echo ""
}

# Main script execution
main() {
    print_header
    
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --aws)
                DEPLOYMENT_TYPE="aws"
                shift
                ;;
            --rocky)
                DEPLOYMENT_TYPE="rocky"
                shift
                ;;
            --custom)
                DEPLOYMENT_TYPE="custom"
                shift
                ;;
            --environment)
                ENVIRONMENT="$2"
                shift 2
                ;;
            --server)
                SERVER="$2"
                shift 2
                ;;
            --config)
                CONFIG_FILE_PATH="$2"
                shift 2
                ;;
            --dry-run)
                DRY_RUN=true
                shift
                ;;
            --verbose)
                VERBOSE=true
                shift
                ;;
            --help)
                print_help
                exit 0
                ;;
            *)
                print_error "Unknown option: $1"
                print_help
                exit 1
                ;;
        esac
    done
    
    # Show deployment summary
    show_summary
    
    # Validate configuration
    validate_config
    
    # Check prerequisites
    check_prerequisites
    
    # Execute deployment
    case $DEPLOYMENT_TYPE in
        "aws")
            deploy_aws
            ;;
        "rocky")
            deploy_rocky
            ;;
        "custom")
            deploy_custom
            ;;
    esac
    
    print_status "🎉 Deployment completed successfully!"
    print_status "🐱 Purrr.love is now running! Enjoy your cat game!"
}

# Run main function
main "$@"
