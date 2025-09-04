# 🧪 Testing Documentation - Purrr.love Production

## 📋 Overview

This document provides comprehensive testing procedures for the Purrr.love application deployed on AWS ECS with multi-domain support and SSL security.

## 🌐 Live Environment Testing

### ✅ **Production URLs - All LIVE**

| Service | URL | Expected Response | Status |
|---------|-----|-------------------|--------|
| **Primary Site** | https://purrr.love/ | 200 OK + Application UI | ✅ LIVE |
| **Alternate Site** | https://purrr.me/ | 200 OK + Application UI | ✅ LIVE |
| **WWW Subdomain** | https://www.purrr.me/ | 200 OK + Application UI | ✅ LIVE |
| **API Endpoint** | https://api.purrr.love/ | API Documentation/Response | ✅ LIVE |
| **Web App** | https://app.purrr.me/ | Application Interface | ✅ LIVE |
| **Admin Panel** | https://admin.purrr.love/ | Admin Interface | ✅ LIVE |
| **Health Check** | https://purrr.love/health.php | `{"status":"healthy","timestamp":"..."}` | ✅ OPERATIONAL |

### 🔒 SSL/TLS Security Testing

#### **SSL Certificate Validation**
```bash
# Test SSL certificate validity
openssl s_client -connect purrr.love:443 -servername purrr.love < /dev/null 2>/dev/null | openssl x509 -noout -dates

# Expected output:
# notBefore=...
# notAfter=...
```

#### **SSL Security Rating**
```bash
# Check SSL Labs rating (should be A+)
curl -s "https://api.ssllabs.com/api/v3/analyze?host=purrr.love" | jq '.endpoints[0].grade'

# Expected: "A+"
```

#### **Security Headers Testing**
```bash
# Test security headers
curl -I https://purrr.love/

# Expected headers:
# Strict-Transport-Security: max-age=31536000; includeSubDomains
# Content-Security-Policy: ...
# X-Frame-Options: DENY
# X-Content-Type-Options: nosniff
```

## 🏥 Health Check Endpoints

### **Application Health**
```bash
# Primary health endpoint
curl -s https://purrr.love/health.php | jq '.'

# Expected response:
{
  "status": "healthy",
  "timestamp": "2025-01-03T20:00:00Z",
  "version": "2.1.2",
  "database": "connected",
  "services": {
    "web": "operational",
    "api": "operational"
  }
}
```

### **Load Balancer Health**
```bash
# Test ALB health check path
curl -s -o /dev/null -w "%{http_code}" https://purrr.love/health.php

# Expected: 200
```

## 🔄 Performance Testing

### **Response Time Testing**
```bash
# Test response times for all domains
for domain in purrr.love purrr.me www.purrr.me api.purrr.love app.purrr.me admin.purrr.love; do
  echo "Testing $domain:"
  curl -s -o /dev/null -w "  Response time: %{time_total}s\n  HTTP code: %{http_code}\n" https://$domain/
done

# Expected: All < 200ms response time, HTTP 200
```

### **Load Testing (Optional)**
```bash
# Simple load test with ab (Apache Bench)
ab -n 100 -c 10 https://purrr.love/

# Expected: No failures, reasonable response times
```

## 🧪 Functional Testing

### **API Endpoint Testing**
```bash
# Test API authentication endpoint
curl -X POST https://api.purrr.love/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test"}'

# Expected: JSON response with authentication details or proper error
```

### **Database Connectivity**
```bash
# Test database-dependent endpoint
curl -s https://purrr.love/api/cats/list

# Expected: JSON response with cat data or proper authentication error
```

### **File Upload Testing**
```bash
# Test file upload capability (requires authentication)
curl -X POST https://purrr.love/api/cats/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@test-cat.jpg"

# Expected: JSON response confirming upload or proper error
```

## 🚨 Error Handling Testing

### **404 Error Testing**
```bash
# Test non-existent page
curl -s -o /dev/null -w "%{http_code}" https://purrr.love/non-existent-page

# Expected: 404
```

### **API Error Testing**
```bash
# Test API with invalid data
curl -X POST https://api.purrr.love/invalid-endpoint \
  -H "Content-Type: application/json" \
  -d '{}'

# Expected: Proper JSON error response, not HTML error page
```

## 🔐 Security Testing

### **Authentication Testing**
```bash
# Test protected endpoint without authentication
curl -s https://api.purrr.love/admin/users

# Expected: 401 Unauthorized with JSON error
```

### **CORS Testing**
```bash
# Test CORS headers
curl -H "Origin: https://example.com" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: X-Requested-With" \
  -X OPTIONS https://api.purrr.love/

# Expected: Proper CORS headers or rejection
```

### **Rate Limiting Testing**
```bash
# Test rate limiting (may require multiple rapid requests)
for i in {1..20}; do
  curl -s -o /dev/null -w "%{http_code} " https://api.purrr.love/
done
echo

# Expected: Some 429 (Too Many Requests) responses after threshold
```

## 🔄 Infrastructure Testing

### **Auto-Scaling Testing**
```bash
# Monitor ECS service during load
aws ecs describe-services --cluster purrr-love-cluster --services purrr-love-service

# Expected: Service should scale up under load
```

### **Container Health Testing**
```bash
# Check ECS task health
aws ecs list-tasks --cluster purrr-love-cluster --service-name purrr-love-service

# Expected: All tasks in RUNNING state
```

## 📊 Monitoring & Logging

### **CloudWatch Metrics**
```bash
# Check ALB metrics
aws cloudwatch get-metric-statistics \
  --namespace AWS/ApplicationELB \
  --metric-name TargetResponseTime \
  --dimensions Name=LoadBalancer,Value=app/purrr-love-alb/... \
  --start-time $(date -u -d '1 hour ago' +%Y-%m-%dT%H:%M:%S) \
  --end-time $(date -u +%Y-%m-%dT%H:%M:%S) \
  --period 300 \
  --statistics Average

# Expected: Response times < 200ms
```

### **Log Validation**
```bash
# Check ECS logs
aws logs describe-log-groups --log-group-name-prefix /ecs/purrr-love

# Expected: Log groups exist and contain recent entries
```

## 🧪 Automated Testing Scripts

### **Health Check Script**
```bash
#!/bin/bash
# health-check.sh - Automated health verification

DOMAINS=("purrr.love" "purrr.me" "www.purrr.me" "api.purrr.love" "app.purrr.me" "admin.purrr.love")

echo "🏥 Purrr.love Health Check Report"
echo "================================"

for domain in "${DOMAINS[@]}"; do
  echo "Testing $domain..."
  
  # Test HTTP response
  HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://$domain/)
  RESPONSE_TIME=$(curl -s -o /dev/null -w "%{time_total}" https://$domain/)
  
  if [ "$HTTP_CODE" = "200" ]; then
    echo "  ✅ HTTP: $HTTP_CODE (${RESPONSE_TIME}s)"
  else
    echo "  ❌ HTTP: $HTTP_CODE (${RESPONSE_TIME}s)"
  fi
done

# Test health endpoint
echo "Testing health endpoint..."
HEALTH_RESPONSE=$(curl -s https://purrr.love/health.php)
if echo "$HEALTH_RESPONSE" | jq -e '.status == "healthy"' > /dev/null 2>&1; then
  echo "  ✅ Health: HEALTHY"
else
  echo "  ❌ Health: UNHEALTHY"
fi

echo "================================"
echo "Health check complete!"
```

### **Performance Test Script**
```bash
#!/bin/bash
# performance-test.sh - Basic performance validation

echo "🚀 Purrr.love Performance Test"
echo "=============================="

# Test response times for key endpoints
ENDPOINTS=(
  "/"
  "/health.php"
  "/api/status"
  "/login"
)

for endpoint in "${ENDPOINTS[@]}"; do
  echo "Testing $endpoint..."
  
  RESPONSE_TIME=$(curl -s -o /dev/null -w "%{time_total}" https://purrr.love$endpoint)
  
  if (( $(echo "$RESPONSE_TIME < 0.5" | bc -l) )); then
    echo "  ✅ Response time: ${RESPONSE_TIME}s (GOOD)"
  elif (( $(echo "$RESPONSE_TIME < 1.0" | bc -l) )); then
    echo "  ⚠️  Response time: ${RESPONSE_TIME}s (ACCEPTABLE)"
  else
    echo "  ❌ Response time: ${RESPONSE_TIME}s (SLOW)"
  fi
done

echo "=============================="
echo "Performance test complete!"
```

## 🎯 Testing Checklist

### **Pre-Deployment Testing**
- [ ] All URLs respond with 200 OK
- [ ] SSL certificates are valid and properly configured
- [ ] Health check endpoint returns healthy status
- [ ] Database connections are working
- [ ] File uploads and downloads work correctly
- [ ] Authentication and authorization work properly

### **Post-Deployment Validation**
- [ ] All domains resolve correctly
- [ ] SSL/TLS is enforced with A+ rating
- [ ] Health checks pass consistently
- [ ] Performance meets requirements (<200ms response)
- [ ] Auto-scaling triggers under load
- [ ] Monitoring and logging are operational

### **Security Validation**
- [ ] Security headers are present and correct
- [ ] Authentication is required for protected endpoints
- [ ] Rate limiting is working
- [ ] CORS policies are properly configured
- [ ] No sensitive information exposed in errors

### **Ongoing Monitoring**
- [ ] Set up automated health checks
- [ ] Monitor CloudWatch metrics
- [ ] Review application logs regularly
- [ ] Test disaster recovery procedures
- [ ] Validate backup and restore processes

## 🚨 Troubleshooting

### **Common Issues**

#### **503 Service Unavailable**
```bash
# Check ECS service status
aws ecs describe-services --cluster purrr-love-cluster --services purrr-love-service

# Check target group health
aws elbv2 describe-target-health --target-group-arn arn:aws:elasticloadbalancing:...
```

#### **SSL Certificate Issues**
```bash
# Check certificate status
aws acm list-certificates --certificate-statuses ISSUED

# Verify DNS validation
nslookup purrr.love
```

#### **Health Check Failures**
```bash
# Test health endpoint directly
curl -v https://purrr.love/health.php

# Check application logs
aws logs tail /ecs/purrr-love --follow
```

## 📈 Success Criteria

### **Performance Benchmarks**
- **Response Time**: < 200ms average
- **SSL Handshake**: < 100ms
- **Health Check**: < 50ms
- **Uptime**: 99.9% availability

### **Security Requirements**
- **SSL Labs Grade**: A+
- **Security Headers**: All required headers present
- **Certificate Validity**: Valid and auto-renewing
- **HSTS**: Properly configured

### **Functional Requirements**
- **All URLs**: Return 200 OK with expected content
- **API Endpoints**: Return proper JSON responses
- **Authentication**: Working correctly
- **Database**: All connections stable

---

**🐱 Happy Testing! Remember: A well-tested cat is a happy cat!** 🧪✅
