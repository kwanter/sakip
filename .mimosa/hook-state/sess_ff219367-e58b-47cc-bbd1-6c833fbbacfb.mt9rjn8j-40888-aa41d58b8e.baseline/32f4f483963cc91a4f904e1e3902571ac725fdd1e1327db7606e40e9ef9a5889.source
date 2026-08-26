#!/bin/bash

# MySQL Restore Script for SAKIP Application
# Usage: ./restore.sh [backup_file.sql.gz]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Load .env file
if [ -f "${PROJECT_DIR}/.env" ]; then
    source ${PROJECT_DIR}/.env
else
    echo -e "${RED}Error: .env file not found at ${PROJECT_DIR}/.env${NC}"
    exit 1
fi

DB_NAME=${DB_DATABASE:-sakip}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD:-root}
CONTAINER_NAME=${MYSQL_CONTAINER_NAME:-sakip-mysql}
BACKUP_DIR="${SCRIPT_DIR}/backups"

# Check if backup file provided
if [ -z "$1" ]; then
    echo -e "${RED}Usage: $0 [backup_file.sql.gz]${NC}"
    echo ""
    echo "Available backups in ${BACKUP_DIR}:"
    ls -lh ${BACKUP_DIR}/*.sql.gz 2>/dev/null || echo "No backups found"
    exit 1
fi

BACKUP_FILE=$1

# If only filename provided, look in backup directory
if [[ ! "$BACKUP_FILE" =~ ^/ ]]; then
    BACKUP_FILE="${BACKUP_DIR}/${BACKUP_FILE}"
fi

# Check if backup exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}Error: Backup file not found: ${BACKUP_FILE}${NC}"
    exit 1
fi

echo -e "${YELLOW}=== SAKIP MySQL Restore ===${NC}"
echo -e "Backup: ${YELLOW}${BACKUP_FILE}${NC}"
echo -e "Database: ${YELLOW}${DB_NAME}${NC}"
echo -e "Container: ${YELLOW}${CONTAINER_NAME}${NC}"
echo ""
echo -e "${RED}WARNING: This will REPLACE all data in the database!${NC}"
read -p "Are you sure? (type 'yes' to continue): " confirmation

if [ "$confirmation" != "yes" ]; then
    echo -e "${YELLOW}Restore cancelled${NC}"
    exit 0
fi

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q ${CONTAINER_NAME}; then
    echo -e "${RED}Error: MySQL container is not running${NC}"
    echo "Start it with: cd ${PROJECT_DIR} && docker-compose up -d mysql"
    exit 1
fi

# Decompress and restore
echo -e "${GREEN}Restoring backup...${NC}"
if [[ "$BACKUP_FILE" == *.gz ]]; then
    gunzip -c ${BACKUP_FILE} | docker exec -i ${CONTAINER_NAME} mysql \
        -u root \
        -p${DB_ROOT_PASSWORD} \
        ${DB_NAME} 2>/dev/null
else
    cat ${BACKUP_FILE} | docker exec -i ${CONTAINER_NAME} mysql \
        -u root \
        -p${DB_ROOT_PASSWORD} \
        ${DB_NAME} 2>/dev/null
fi

echo -e "${GREEN}✓ Restore completed successfully!${NC}"
echo -e "Database: ${YELLOW}${DB_NAME}${NC} has been restored"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "1. Clear application cache: ${GREEN}php artisan cache:clear${NC}"
echo -e "2. Clear config cache: ${GREEN}php artisan config:clear${NC}"
echo -e "3. Run migrations: ${GREEN}php artisan migrate${NC}"
