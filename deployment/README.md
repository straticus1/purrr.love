# 🚀 Purrr.love Deployment Guide

**Choose Your Adventure: AWS Containers or Rocky Linux!**

This deployment system gives you two powerful options to get Purrr.love running in production:

## 🎯 **Deployment Options**

### **Option 1: AWS Containerized Deployment** 🐳☁️
- **Terraform** for infrastructure as code
- **Docker containers** with ECS/Fargate
- **Auto-scaling** and load balancing
- **Managed databases** (RDS PostgreSQL)
- **CDN** and S3 for static assets
- **Full CI/CD pipeline** with GitHub Actions

### **Option 2: Rocky Linux Traditional Deployment** 🐧🖥️
- **Traditional VPS** or dedicated server
- **LAMP stack** (Linux, Apache, MySQL, PHP)
- **Manual configuration** with Ansible automation
- **Custom server setup** and management
- **Full control** over the environment

## 🚀 **Quick Start**

```bash
# Clone the repository
git clone https://github.com/yourusername/purrr-love.git
cd purrr-love

# Choose your deployment method
./deploy.sh --help

# Deploy to AWS
./deploy.sh --aws --environment production

# Deploy to Rocky Linux
./deploy.sh --rocky --server your-server.com

# Custom deployment
./deploy.sh --custom --config custom-deployment.yaml
```

## 🔧 **Prerequisites**

### **For AWS Deployment:**
- AWS CLI configured with appropriate permissions
- Terraform 1.0+
- Docker and Docker Compose
- GitHub repository with Actions enabled

### **For Rocky Linux Deployment:**
- Rocky Linux 8+ server
- SSH access with sudo privileges
- Ansible 2.9+
- Basic networking knowledge

## 🏗️ **Terraform Modules Architecture**

The AWS deployment uses a modular Terraform architecture for maintainable and reusable infrastructure components.

### **Module Structure:**

#### **VPC Module (`modules/vpc`)**
- **Purpose**: Complete network infrastructure setup
- **Features**:
  - Multi-AZ VPC with configurable CIDR blocks
  - Public, private, and database subnets
  - NAT Gateway and Internet Gateway configuration
  - VPC endpoints for AWS services
  - Flow logs for network monitoring

#### **Security Groups Module (`modules/security_groups`)**
- **Purpose**: Network security layer management
- **Features**:
  - Web tier security group for ALB
  - Application tier security group for ECS
  - Database tier security group for RDS
  - Admin access with IP whitelisting
  - Redis and EFS security groups

#### **Database Module (`modules/database`)**
- **Purpose**: Managed RDS PostgreSQL database
- **Features**:
  - Multi-AZ deployment for high availability
  - Automated backups with configurable retention
  - Performance insights and enhanced monitoring
  - Read replica support
  - Encryption at rest and in transit

#### **ECS Module (`modules/ecs`)**
- **Purpose**: Containerized application hosting
- **Features**:
  - Fargate cluster with auto-scaling
  - Service discovery and load balancer integration
  - Container insights monitoring
  - Spot instance support for cost optimization
  - Rolling deployments with health checks

#### **ALB Module (`modules/alb`)**
- **Purpose**: Application Load Balancer configuration
- **Features**:
  - SSL/TLS termination with ACM certificates
  - Health check configuration
  - Access logging to S3
  - Deletion protection for production
  - Target group management

## 📁 **Deployment Structure**

```
deployment/
├── aws/                    # AWS containerized deployment
│   ├── terraform/         # Infrastructure as code
│   │   ├── main.tf        # Main infrastructure configuration
│   │   ├── variables.tf   # Input variables
│   │   ├── outputs.tf     # Infrastructure outputs
│   │   └── modules/       # Modular infrastructure components
│   │       ├── vpc/       # VPC and networking module
│   │       ├── security_groups/ # Security groups module
│   │       ├── database/  # RDS database module
│   │       ├── ecs/       # ECS cluster and services
│   │       ├── alb/       # Application Load Balancer
│   │       └── route53/   # DNS and domain management
│   ├── docker/            # Container configurations
│   ├── ansible/           # Container orchestration
│   └── ci-cd/            # GitHub Actions workflows
├── rocky-linux/           # Rocky Linux deployment
│   ├── ansible/           # Server automation
│   ├── systemd/           # Service management
│   └── nginx/             # Web server configs
├── shared/                # Common deployment scripts
├── configs/               # Configuration templates
└── scripts/               # Deployment automation
```

## 🌟 **Features**

### **AWS Containerized Features:**
- ✅ **Modular Infrastructure** with reusable Terraform modules
- ✅ **Multi-Environment Support** (dev, staging, production)
- ✅ **Auto-scaling** based on demand with ECS Fargate
- ✅ **Load balancing** across multiple containers with ALB
- ✅ **Managed databases** with RDS PostgreSQL and automated backups
- ✅ **VPC Security** with isolated subnets and security groups
- ✅ **SSL/TLS** termination with ACM certificates
- ✅ **CDN** for global performance
- ✅ **Monitoring** and alerting with CloudWatch
- ✅ **Blue-green deployments** with zero downtime
- ✅ **Rollback capabilities** and disaster recovery

### **Rocky Linux Features:**
- ✅ **Full server control**
- ✅ **Custom optimization**
- ✅ **Traditional LAMP stack**
- ✅ **Manual scaling** control
- ✅ **Cost-effective** for smaller deployments
- ✅ **Custom security** policies

## 🎮 **Let's Deploy Everything!**

Ready to make Purrr.love the most amazing cat game ever? Let's build this deployment system and get it running! 🐱✨

---

**Next Steps:**
1. Choose your deployment preference
2. Configure your environment
3. Run the deployment
4. Enjoy your amazing cat game! 🎉
