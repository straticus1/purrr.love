# 🚀 Purrr.love Metaverse Deployment Guide

## Overview

This guide will help you deploy the enhanced Purrr.love metaverse system to AWS ECS while **preserving your existing DNS configuration** from GoDaddy. The deployment includes comprehensive automation that will keep your virtual worlds active 24/7.

## 🎯 What This Deployment Includes

### New Metaverse Features
- **AI-Driven Cat NPCs**: Autonomous cats that spawn in quiet worlds
- **Dynamic Weather Systems**: Real-time weather changes across all worlds
- **Gamification System**: 50+ achievements, daily quests, competitions
- **Real-Time Analytics**: Engagement monitoring with auto-boosting
- **Seasonal Content**: Automatic seasonal decorations and events
- **Limited-Time Areas**: Special legendary areas that appear randomly
- **24/7 Automation**: 8 cron jobs running continuously

### Infrastructure Updates
- **Enhanced Docker Image**: Includes cron and supervisor for automation
- **Updated ECS Configuration**: New environment variables and task definitions
- **Database Schema**: New tables for metaverse features
- **DNS Preservation**: Your existing GoDaddy DNS will be protected
- **Load Balancer**: Updated with new health checks

## 📋 Pre-Deployment Checklist

### 1. Prerequisites Check

Ensure you have these tools installed:

```bash
# Check AWS CLI
aws --version

# Check Docker
docker --version

# Check Terraform
terraform --version

# Check AWS credentials
aws sts get-caller-identity
```

### 2. Backup Current System

```bash
# Backup your current DNS configuration
./deployment/check_dns_before_deploy.sh

# This will create dns_backup/current_dns.txt with your current settings
```

### 3. Review Current Infrastructure

```bash
# Check current deployment status
./deployment/deploy_metaverse_update.sh check
```

## 🚀 Deployment Process

### Step 1: Pre-Deployment DNS Check

```bash
# Run this first to understand your current DNS setup
./deployment/check_dns_before_deploy.sh
```

This will:
- ✅ Check if you're already using Route53 (no DNS migration needed)
- ⚠️  Or identify if you need to migrate from GoDaddy nameservers
- 📁 Backup your current DNS configuration
- 🔍 Verify current website availability

### Step 2: Update Database Schema

First, apply the new database schema for metaverse features:

```bash
# Connect to your RDS instance and run:
psql -h your-rds-endpoint -U your-username -d your-database -f database/metaverse_update_schema.sql
```

This creates the new tables needed for:
- AI NPC management
- World events tracking
- User daily quests
- Weather systems
- Analytics and progress tracking

### Step 3: Deploy the Metaverse Update

```bash
# Run the main deployment
./deployment/deploy_metaverse_update.sh
```

This process will:

1. **Build Enhanced Docker Image** (5-10 minutes)
   - Includes new metaverse systems
   - Sets up cron jobs for automation
   - Adds supervisor for service management

2. **Update AWS Infrastructure** (10-15 minutes)
   - Updates ECS task definitions
   - Adds new environment variables
   - Preserves existing Route53 configuration

3. **Deploy to ECS** (5-10 minutes)
   - Rolling deployment with zero downtime
   - Health checks to ensure successful deployment
   - Auto-scaling configuration

4. **Verify Deployment** (2-3 minutes)
   - Tests health endpoints
   - Verifies metaverse automation
   - Confirms DNS configuration

### Step 4: Post-Deployment DNS Management

The deployment script will tell you if DNS migration is needed:

#### If Already Using Route53 ✅
- **No action required!**
- DNS configuration is automatically preserved
- All subdomains continue working immediately

#### If Using GoDaddy DNS ⚠️
You'll need to update your nameservers in GoDaddy:

1. **Get AWS Nameservers** (provided by deployment script):
   ```
   ns-xxx.awsdns-xx.com
   ns-xxx.awsdns-xx.co.uk
   ns-xxx.awsdns-xx.net
   ns-xxx.awsdns-xx.org
   ```

2. **Update in GoDaddy**:
   - Log into GoDaddy account
   - Go to DNS Management for purrr.love
   - Update nameservers to the AWS ones
   - Save changes

3. **DNS Propagation**: Wait 1-48 hours for full propagation

## 🔧 Automation Features

Once deployed, these systems run automatically:

### Cron Job Schedule
```bash
*/5 * * * *   - Engagement monitoring and boosting
*/10 * * * *  - AI NPC spawning in quiet worlds  
*/15 * * * *  - Population balancing across worlds
*/30 * * * *  - Weather system updates
0 */1 * * *   - Special area management
*/20 * * * *  - Analytics processing
0 6 * * *     - Seasonal content updates
0 0 * * *     - Daily quest generation
```

### What Happens Automatically
- **Low Engagement Detection**: When activity drops, AI cats spawn automatically
- **Dynamic Weather**: Weather changes every 30 minutes based on world type
- **Daily Quests**: New personalized quests generated daily for each user
- **Seasonal Updates**: Decorations and themes update automatically
- **Special Areas**: Legendary areas appear randomly (15% chance per hour)
- **Population Balancing**: Users guided to underutilized worlds

## 🧪 Testing the Deployment

### 1. Basic Health Check
```bash
curl -f https://purrr.love/health
# Should return 200 OK
```

### 2. Test Metaverse Automation
```bash
# SSH into ECS container and test
php /var/www/html/cli/metaverse_automation.php test
```

### 3. Check Automation Logs
```bash
# View automation logs
tail -f logs/metaverse_automation.log
```

### 4. Verify New Features
Visit these URLs to test new functionality:
- `https://purrr.love/web/metaverse-vr.php` - VR interface
- `https://purrr.love/web/admin.php` - Admin panel (if admin)
- `https://api.purrr.love/v2/advanced_features.php` - API endpoints

## 📊 Monitoring & Analytics

### CloudWatch Metrics
The deployment automatically sets up monitoring for:
- ECS service health and performance  
- Application Load Balancer metrics
- Database performance
- Custom metaverse engagement metrics

### Application Logs
- **ECS Logs**: `/aws/ecs/production-purrr`
- **Automation Logs**: `logs/metaverse_automation.log` 
- **Apache Logs**: Standard web server logs
- **PHP Error Logs**: Application error tracking

### Engagement Dashboard
Access real-time metrics:
```bash
# Check current engagement status
php cli/metaverse_automation.php status
```

## 🔄 Rollback Process

If something goes wrong, you can rollback:

```bash
# Emergency rollback
./deployment/deploy_metaverse_update.sh rollback
```

This will:
- Revert to the previous ECS task definition
- Restore previous service configuration
- Maintain DNS configuration (no changes to DNS during rollback)

## 🔧 Troubleshooting

### Common Issues

#### 1. Deployment Fails During Build
```bash
# Check Docker daemon is running
docker ps

# Ensure AWS credentials are valid
aws sts get-caller-identity

# Check ECR permissions
aws ecr describe-repositories
```

#### 2. ECS Service Won't Start
```bash
# Check ECS task logs
aws ecs describe-tasks --cluster production-purrr-cluster --tasks task-arn

# Verify environment variables
aws ecs describe-task-definition --task-definition production-purrr-app
```

#### 3. DNS Not Working After Migration
```bash
# Check DNS propagation
dig purrr.love NS
dig purrr.love A

# Verify Route53 records
aws route53 list-resource-record-sets --hosted-zone-id YOUR_ZONE_ID
```

#### 4. Metaverse Automation Not Working
```bash
# Check cron is running
ps aux | grep cron

# Test automation manually
php /var/www/html/cli/metaverse_automation.php test

# Check database connectivity
php /var/www/html/cli/metaverse_automation.php status
```

## 📱 Post-Deployment Tasks

### 1. Update Monitoring
- Set up CloudWatch alarms for new metrics
- Configure SNS notifications for deployment events
- Add custom dashboards for metaverse analytics

### 2. Test All Features
- Create test cats and verify VR interactions
- Test daily quest generation
- Verify weather system updates
- Check achievement unlocks

### 3. Performance Optimization
- Monitor ECS resource utilization
- Adjust auto-scaling parameters if needed
- Optimize database queries based on new load patterns

### 4. User Communication
- Announce new metaverse features to users
- Update documentation and help guides
- Monitor user feedback and engagement metrics

## 🎉 Success Indicators

You'll know the deployment was successful when:

✅ **Website Accessible**: `https://purrr.love` loads normally  
✅ **DNS Working**: All subdomains resolve correctly  
✅ **Health Checks Pass**: `/health` endpoint returns 200  
✅ **Automation Active**: Cron jobs running and logging  
✅ **Database Updated**: New tables exist and are populated  
✅ **ECS Healthy**: Service shows as "STABLE" in AWS console  
✅ **Logs Clean**: No critical errors in CloudWatch  
✅ **Metrics Flowing**: Engagement analytics being recorded  

## 🔮 What's Next

After successful deployment, your metaverse will automatically:

- **Maintain 24/7 Activity**: AI NPCs ensure no dead periods
- **Adapt to Usage Patterns**: Learn peak times and optimize accordingly  
- **Generate Personalized Content**: Daily quests tailored to each user
- **Create Seasonal Experiences**: Automatic updates for holidays/seasons
- **Monitor Engagement**: Real-time analytics with automatic interventions
- **Scale Resources**: Auto-scaling based on demand

## 📞 Support

If you encounter issues during deployment:

1. **Check the logs** first - most issues are logged with clear error messages
2. **Review the troubleshooting section** - covers common deployment problems
3. **Use the rollback option** if needed - preserves DNS and reverts safely
4. **Run the check script** to validate your current state

The deployment is designed to be **safe and reversible** - your DNS configuration and existing data are protected throughout the process.

---

**🌟 Ready to deploy your enhanced metaverse?**

```bash
# Start with the DNS check
./deployment/check_dns_before_deploy.sh

# Then run the full deployment
./deployment/deploy_metaverse_update.sh
```

Your virtual cat worlds will never be quiet again! 🐱✨
