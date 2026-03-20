#!/bin/bash

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Meta API Integration - Test Runner${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if API is running
echo -e "${YELLOW}Checking if API is running...${NC}"
if curl -s http://localhost:8000/health > /dev/null; then
    echo -e "${GREEN}✓ API is running${NC}"
else
    echo -e "${RED}✗ API is not running${NC}"
    echo -e "${YELLOW}Please start the API first:${NC}"
    echo -e "  uvicorn main:app --reload"
    exit 1
fi

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Running Comprehensive Test Suite${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Run comprehensive tests
python run_all_tests.py

TEST_RESULT=$?

echo ""
echo -e "${BLUE}========================================${NC}"

if [ $TEST_RESULT -eq 0 ]; then
    echo -e "${GREEN}✅ All tests passed!${NC}"
    echo -e "${BLUE}========================================${NC}"
    exit 0
else
    echo -e "${RED}❌ Some tests failed${NC}"
    echo -e "${BLUE}========================================${NC}"
    exit 1
fi
