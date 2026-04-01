#!/bin/bash
# WhatsApp Lead Capture System - Verification Script
# This script helps verify that all components are working correctly

echo "=========================================="
echo "WhatsApp Lead Capture System Verification"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counter for checks
PASSED=0
FAILED=0

# ==========================================
# Helper Functions
# ==========================================

check_port() {
    local port=$1
    local service=$2
    
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1 ; then
        echo -e "${GREEN}✓${NC} $service is running on port $port"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} $service is NOT running on port $port"
        ((FAILED++))
        return 1
    fi
}

check_endpoint() {
    local method=$1
    local url=$2
    local expected_status=$3
    local service=$4
    
    response=$(curl -s -w "\n%{http_code}" -X $method "$url" 2>/dev/null)
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | head -n-1)
    
    if [ "$http_code" -eq "$expected_status" ] || [ "$http_code" -gt 0 ]; then
        echo -e "${GREEN}✓${NC} $service API responded with status $http_code"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} $service API failed (no response)"
        ((FAILED++))
        return 1
    fi
}

check_directory() {
    local dir=$1
    local name=$2
    
    if [ -d "$dir" ]; then
        echo -e "${GREEN}✓${NC} $name directory exists"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} $name directory NOT found"
        ((FAILED++))
        return 1
    fi
}

check_file() {
    local file=$1
    local name=$2
    
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $name file exists"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} $name file NOT found"
        ((FAILED++))
        return 1
    fi
}

# ==========================================
# SECTION 1: Directory Structure
# ==========================================
echo -e "${BLUE}[1/5] Checking Directory Structure${NC}"
echo "--------------------------------------"

check_directory "app/Http/Controllers/Api" "Laravel API Controllers"
check_directory "app/Models" "Laravel Models"
check_directory "app/Services" "Laravel Services"
check_directory "whatsapp-bot" "WhatsApp Bot"
check_directory "database/migrations" "Database Migrations"

echo ""

# ==========================================
# SECTION 2: File Existence
# ==========================================
echo -e "${BLUE}[2/5] Checking Critical Files${NC}"
echo "--------------------------------------"

check_file "app/Http/Controllers/Api/WhatsAppLeadController.php" "WhatsAppLeadController"
check_file "app/Models/ScamLead.php" "ScamLead Model"
check_file "app/Services/ScamLeadService.php" "ScamLeadService"
check_file "routes/api.php" "API Routes"
check_file "whatsapp-bot/index.js" "WhatsApp Bot Entry Point"
check_file "whatsapp-bot/package.json" "Bot Package Config"

echo ""

# ==========================================
# SECTION 3: NPM Dependencies
# ==========================================
echo -e "${BLUE}[3/5] Checking NPM Dependencies${NC}"
echo "--------------------------------------"

if [ -d "whatsapp-bot/node_modules" ]; then
    echo -e "${GREEN}✓${NC} Node modules installed"
    ((PASSED++))
    
    # Check for specific packages
    if [ -f "whatsapp-bot/node_modules/whatsapp-web.js/package.json" ]; then
        echo -e "${GREEN}✓${NC} whatsapp-web.js is installed"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} whatsapp-web.js is NOT installed"
        ((FAILED++))
    fi
    
    if [ -f "whatsapp-bot/node_modules/axios/package.json" ]; then
        echo -e "${GREEN}✓${NC} axios is installed"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} axios is NOT installed"
        ((FAILED++))
    fi
    
    if [ -f "whatsapp-bot/node_modules/qrcode-terminal/package.json" ]; then
        echo -e "${GREEN}✓${NC} qrcode-terminal is installed"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} qrcode-terminal is NOT installed"
        ((FAILED++))
    fi
else
    echo -e "${RED}✗${NC} Node modules NOT installed in whatsapp-bot"
    echo "   Run: cd whatsapp-bot && npm install"
    ((FAILED++))
fi

echo ""

# ==========================================
# SECTION 4: Services Running
# ==========================================
echo -e "${BLUE}[4/5] Checking Running Services${NC}"
echo "--------------------------------------"

check_port 8000 "Laravel Dev Server"
check_port 3000 "Node.js Bot (if running)"

echo ""

# ==========================================
# SECTION 5: API Connectivity
# ==========================================
echo -e "${BLUE}[5/5] Checking API Connectivity${NC}"
echo "--------------------------------------"

# Test Laravel API endpoint
echo -n "Testing Laravel API endpoint... "
response=$(curl -s -X POST http://127.0.0.1:8000/api/whatsapp/lead \
    -H "Content-Type: application/json" \
    -d '{"phone":"919876543210@c.us","message":"test"}' 2>/dev/null)

if echo "$response" | grep -q "success\|error"; then
    echo -e "${GREEN}✓${NC} API is responding"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} API did not respond properly"
    ((FAILED++))
fi

echo ""

# ==========================================
# SUMMARY
# ==========================================
echo "=========================================="
echo "Verification Summary"
echo "=========================================="
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed! System is ready.${NC}"
    exit 0
else
    echo -e "${YELLOW}⚠ Some checks failed. Please review the output above.${NC}"
    exit 1
fi
