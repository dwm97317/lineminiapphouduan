#!/bin/bash

echo "🔍 Verifying Bot API Setup..."
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check Docker
echo -n "Checking Docker... "
if command -v docker &> /dev/null; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC} Docker not installed"
fi

# Check Docker Compose
echo -n "Checking Docker Compose... "
if command -v docker-compose &> /dev/null; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC} Docker Compose not installed"
fi

# Check containers
echo ""
echo "Checking containers..."
echo -n "  MySQL... "
if docker-compose ps mysql | grep -q "Up"; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC} MySQL not running"
fi

echo -n "  Redis... "
if docker-compose ps redis | grep -q "Up"; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC} Redis not running"
fi

echo -n "  API... "
if docker-compose ps api | grep -q "Up"; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC} API not running"
fi

# Check API health
echo ""
echo -n "Checking API health endpoint... "
HEALTH=$(curl -s http://localhost:8000/health)
if echo "$HEALTH" | grep -q "ok"; then
    echo -e "${GREEN}✓${NC}"
    echo "  Response: $HEALTH"
else
    echo -e "${RED}✗${NC} API health check failed"
fi

# Check database tables
echo ""
echo "Checking database tables..."
TABLES=$(docker-compose exec -T mysql mysql -u bot_user -pbot_password bot_db -e "SHOW TABLES;" 2>/dev/null | tail -n +2)
TABLE_COUNT=$(echo "$TABLES" | wc -l)

if [ "$TABLE_COUNT" -ge 4 ]; then
    echo -e "${GREEN}✓${NC} Found $TABLE_COUNT tables"
    echo "$TABLES" | sed 's/^/  /'
else
    echo -e "${RED}✗${NC} Expected 4 tables, found $TABLE_COUNT"
fi

# Check Redis connection
echo ""
echo -n "Checking Redis connection... "
REDIS_PING=$(docker-compose exec -T redis redis-cli ping 2>/dev/null)
if [ "$REDIS_PING" = "PONG" ]; then
    echo -e "${GREEN}✓${NC}"
else
    echo -e "${RED}✗${NC} Redis connection failed"
fi

echo ""
echo -e "${GREEN}✅ Setup verification complete!${NC}"
