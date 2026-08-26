#!/bin/bash

# MySQL Backup Script for SAKIP Application
# Usage: ./backup.sh [database_name]

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

# Defaults
DB_NAME=${1:-${DB_DATABASE:-sakip}}
DB_USER=${DB_USERNAME:-sakip}
DB_PASSWORD=${DB_PASSWORD:-secret}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD:-root}
CONTAINER_NAME=${MYSQL_CONTAINER_NAME:-sakip-mysql}
BACKUP_DIR="${SCRIPT_DIR}/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_backup_${TIMESTAMP}.sql"

echo -e "${GREEN}=== SAKIP MySQL Backup ===${NC}"
echo -e "Database: ${YELLOW}${DB_NAME}${NC}"
echo -e "Container: ${YELLOW}${CONTAINER_NAME}${NC}"
echo -e "Data stored in: ${YELLOW}docker/mysql/data/${NC}"
echo -e "Output: ${YELLOW}${BACKUP_FILE}${NC}"
echo ""

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q ${CONTAINER_NAME}; then
    echo -e "${RED}Error: MySQL container is not running${NC}"
    echo "Start it with: cd ${PROJECT_DIR} && docker-compose up -d mysql"
    exit 1
fi

# Create backup directory if it doesn't exist
mkdir -p ${BACKUP_DIR}

# Perform backup
echo -e "${GREEN}Creating backup...${NC}"
docker exec ${CONTAINER_NAME} mysqldump \
    -u root \
    -p${DB_ROOT_PASSWORD} \
    --single-transaction \
    --quick \
    --lock-tables=false \
    ${DB_NAME} > ${BACKUP_FILE} 2>/dev/null

# Compress backup
echo -e "${GREEN}Compressing backup...${NC}"
gzip ${BACKUP_FILE}
BACKUP_FILE="${BACKUP_FILE}.gz"

# Get file size
BACKUP_SIZE=$(du -h ${BACKUP_FILE} | cut -f1)

echo -e "${GREEN}✓ Backup completed successfully!${NC}"
echo -e "File: ${YELLOW}${BACKUP_FILE}${NC}"
echo -e "Size: ${YELLOW}${BACKUP_SIZE}${NC}"
echo ""
echo -e "To restore this backup, use:"
echo -e "${YELLOW}cd docker/mysql && ./restore.sh $(basename ${BACKUP_FILE})${NC}"

# Keep only last 7 days of backups (optional)
echo -e "\n${GREEN}Cleaning old backups (keeping last 7 days)...${NC}"
find ${BACKUP_DIR} -name "${DB_NAME}_backup_*.sql.gz" -mtime +7 -delete 2>/dev/null || true
echo -e "${GREEN}✓ Cleanup complete${NC}"

# List all backups
echo -e "\n${YELLOW}Current backups:${NC}"
ls -lh ${BACKUP_DIR}/*.sql.gz 2>/dev/null | awk '{print $9, "("$5")"}' | xargs -n 1 basename || echo "No backups yet"
