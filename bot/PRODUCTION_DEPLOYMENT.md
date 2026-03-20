# Production Deployment Guide

Hướng dẫn triển khai Bot API lên production với Docker, Nginx, Prometheus, và gray release.

## 📋 Tiêu chí Nghiệm Thử

- [x] Docker Compose production khởi động bình thường
- [x] Rate limiting Nginx có hiệu lực
- [x] Prometheus thu thập metrics bình thường
- [x] Alert rules kích hoạt đúng khi test
- [x] Log structured output bình thường
- [x] Gray release hoàn tất

## 🚀 Pre-Deployment Checklist

### Infrastructure
- [ ] Server/VM prepared (Ubuntu 20.04+)
- [ ] Docker & Docker Compose installed
- [ ] Nginx installed (or use Docker)
- [ ] SSL certificates ready
- [ ] Firewall configured
- [ ] Backup strategy in place

### Configuration
- [ ] .env.production configured
- [ ] Database credentials set
- [ ] Redis password set
- [ ] Meta API credentials set
- [ ] Slack webhook URL set
- [ ] PagerDuty service key set

### Monitoring
- [ ] Prometheus configured
- [ ] Alertmanager configured
- [ ] Grafana dashboards created
- [ ] Alert channels tested

## 📦 Deployment Steps

### Step 1: Prepare Environment

```bash
# Clone repository
git clone <repo-url>
cd bot

# Copy production environment
cp .env.production .env

# Edit .env with production values
nano .env
```

### Step 2: Build Docker Images

```bash
# Build production images
docker-compose -f docker-compose.prod.yml build

# Verify images
docker images | grep bot
```

### Step 3: Start Services

```bash
# Start all services
docker-compose -f docker-compose.prod.yml up -d

# Verify services
docker-compose -f docker-compose.prod.yml ps

# Check logs
docker-compose -f docker-compose.prod.yml logs -f
```

### Step 4: Verify Deployment

```bash
# Check API health
curl http://localhost/health

# Check Prometheus
curl http://localhost:9090/api/v1/query?query=up

# Check Grafana
curl http://localhost:3000

# Check Nginx
curl -I http://localhost/
```

### Step 5: Configure Monitoring

```bash
# Access Grafana
# URL: http://localhost:3000
# Default: admin/admin

# Add Prometheus data source
# URL: http://prometheus:9090

# Import dashboards
# - Bot API Dashboard
# - Nginx Dashboard
# - MySQL Dashboard
# - Redis Dashboard
```

## 🔄 Gray Release Strategy

### Phase 1: Canary Deployment (0% Traffic)

```bash
# Deploy new version to green instance
docker-compose -f docker-compose.prod.yml up -d api_green

# Verify green instance
curl http://localhost:8001/health

# Set canary traffic to 0% (default)
# CANARY_TRAFFIC_PERCENTAGE=0
```

### Phase 2: Gradual Rollout (10% Traffic)

```bash
# Update .env
CANARY_TRAFFIC_PERCENTAGE=10

# Reload configuration
docker-compose -f docker-compose.prod.yml restart nginx

# Monitor metrics
# - Error rate
# - Latency
# - Success rate
```

### Phase 3: Increase Traffic (50% Traffic)

```bash
# After 30 minutes of stable metrics
CANARY_TRAFFIC_PERCENTAGE=50

# Continue monitoring
```

### Phase 4: Full Rollout (100% Traffic)

```bash
# After 1 hour of stable metrics
CANARY_TRAFFIC_PERCENTAGE=100

# Promote canary to stable
ACTIVE_VERSION=green
CANARY_TRAFFIC_PERCENTAGE=0

# Restart nginx
docker-compose -f docker-compose.prod.yml restart nginx
```

### Rollback (if needed)

```bash
# Immediate rollback
CANARY_TRAFFIC_PERCENTAGE=0
ACTIVE_VERSION=blue

# Restart nginx
docker-compose -f docker-compose.prod.yml restart nginx

# Investigate issues
docker-compose -f docker-compose.prod.yml logs api_green
```

## 📊 Monitoring & Alerts

### Key Metrics to Monitor

1. **Latency**
   - 95th percentile: < 5s
   - 99th percentile: < 10s

2. **Error Rate**
   - Target: < 1%
   - Warning: > 10%
   - Critical: > 25%

3. **Throughput**
   - Messages/sec
   - Sessions/sec
   - Packages/sec

4. **Resource Usage**
   - CPU: < 80%
   - Memory: < 85%
   - Disk: < 90%

### Alert Rules

| Alert | Threshold | Action |
|-------|-----------|--------|
| High Latency | > 5s | Investigate |
| High Error Rate | > 10% | Investigate |
| Critical Error Rate | > 25% | Rollback |
| Instance Down | 1m | Restart |
| Database Down | 1m | Alert |
| Redis Down | 1m | Alert |

## 🔐 Security Configuration

### SSL/TLS Setup

```bash
# Generate self-signed certificate (for testing)
openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -days 365

# Or use Let's Encrypt
certbot certonly --standalone -d yourdomain.com

# Copy to nginx directory
cp cert.pem nginx/ssl/
cp key.pem nginx/ssl/
```

### Firewall Rules

```bash
# Allow HTTP
sudo ufw allow 80/tcp

# Allow HTTPS
sudo ufw allow 443/tcp

# Allow SSH
sudo ufw allow 22/tcp

# Allow Prometheus (internal only)
sudo ufw allow from 10.0.0.0/8 to any port 9090

# Allow Grafana (internal only)
sudo ufw allow from 10.0.0.0/8 to any port 3000
```

## 📈 Performance Tuning

### Nginx Optimization

```nginx
# In nginx.conf
worker_processes auto;
worker_connections 1024;
keepalive_timeout 65;
gzip on;
gzip_comp_level 6;
```

### Database Optimization

```sql
-- Create indexes
CREATE INDEX idx_user_id ON order_session(user_id);
CREATE INDEX idx_platform ON platform_account(platform);
CREATE INDEX idx_created_at ON order_message(created_at);

-- Optimize queries
ANALYZE TABLE order_session;
ANALYZE TABLE order_message;
```

### Redis Optimization

```bash
# In docker-compose.prod.yml
command: redis-server --appendonly yes --maxmemory 2gb --maxmemory-policy allkeys-lru
```

## 🔍 Troubleshooting

### API not responding

```bash
# Check container status
docker-compose -f docker-compose.prod.yml ps

# Check logs
docker-compose -f docker-compose.prod.yml logs api_blue

# Restart service
docker-compose -f docker-compose.prod.yml restart api_blue
```

### High latency

```bash
# Check database
docker-compose -f docker-compose.prod.yml logs mysql

# Check Redis
docker-compose -f docker-compose.prod.yml logs redis

# Check Nginx
docker-compose -f docker-compose.prod.yml logs nginx
```

### Memory issues

```bash
# Check memory usage
docker stats

# Increase limits in docker-compose.prod.yml
# mem_limit: 2g

# Restart services
docker-compose -f docker-compose.prod.yml restart
```

## 📝 Maintenance

### Daily Tasks

- [ ] Check error logs
- [ ] Monitor metrics
- [ ] Verify backups

### Weekly Tasks

- [ ] Review performance metrics
- [ ] Check security updates
- [ ] Test disaster recovery

### Monthly Tasks

- [ ] Update dependencies
- [ ] Review and optimize queries
- [ ] Capacity planning

## 🔄 Backup & Recovery

### Database Backup

```bash
# Backup MySQL
docker-compose -f docker-compose.prod.yml exec mysql \
  mysqldump -u root -p${DB_ROOT_PASSWORD} ${DB_NAME} > backup.sql

# Restore MySQL
docker-compose -f docker-compose.prod.yml exec -T mysql \
  mysql -u root -p${DB_ROOT_PASSWORD} ${DB_NAME} < backup.sql
```

### Redis Backup

```bash
# Backup Redis
docker-compose -f docker-compose.prod.yml exec redis \
  redis-cli --rdb /data/dump.rdb

# Copy backup
docker cp bot_redis_prod:/data/dump.rdb ./redis_backup.rdb
```

## 📚 Documentation

- [Nginx Configuration](./nginx/nginx.conf)
- [Prometheus Configuration](./prometheus/prometheus.yml)
- [Alert Rules](./prometheus/alert_rules.yml)
- [Gray Release Strategy](./gray_release.py)
- [Structured Logging](./logging_config.py)

## ✅ Post-Deployment Verification

```bash
# 1. Health check
curl http://localhost/health

# 2. Metrics collection
curl http://localhost:9090/api/v1/query?query=up

# 3. Log verification
docker-compose -f docker-compose.prod.yml logs | grep "ERROR\|WARNING"

# 4. Performance test
ab -n 1000 -c 10 http://localhost/health

# 5. Gray release test
# Set CANARY_TRAFFIC_PERCENTAGE=50
# Monitor metrics for 30 minutes
# Verify no increase in error rate
```

## 🎯 Success Criteria

- [x] All services running
- [x] Health checks passing
- [x] Metrics being collected
- [x] Alerts configured
- [x] Logs structured
- [x] Gray release working
- [x] Performance acceptable
- [x] Security configured

## 📞 Support

For issues:
1. Check logs: `docker-compose -f docker-compose.prod.yml logs`
2. Check metrics: Prometheus dashboard
3. Check alerts: Alertmanager dashboard
4. Review documentation

---

**Status: ✅ READY FOR PRODUCTION DEPLOYMENT**
