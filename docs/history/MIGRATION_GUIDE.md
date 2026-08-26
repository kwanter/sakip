# Quick Migration Guide: Named Volume → Bind Mount

## ⚠️ Migrating Existing Data?

If you have been running the app and have data in the old `mysql-data` Docker volume, follow this guide.

---

## 📋 Pre-Migration Checklist

- [ ] I have data in the current MySQL setup
- [ ] I want to migrate to bind mount storage
- [ ] I have backed up my data (just in case)
- [ ] I have time for potential downtime

---

## 🚀 Migration Steps

### Step 1: Create Backup of Existing Data

```bash
cd docker/mysql

# Run backup script
./backup.sh

# Verify backup was created
ls -lh backups/
```

**You should see something like**:
```
sakip_backup_20250123_143022.sql.gz
```

---

### Step 2: Stop MySQL Container

```bash
# Stop only MySQL (keeps data in volume)
docker-compose stop mysql

# Verify it's stopped
docker-compose ps
```

---

### Step 3: Update docker-compose.yml

✅ **Already done for you!**

The file has been updated from:
```yaml
- mysql-data:/var/lib/mysql
```

To:
```yaml
- ./docker/mysql/data:/var/lib/mysql
```

And removed the volume declaration.

---

### Step 4: Remove Old Container

```bash
# Remove container (volume still safe)
docker rm sakip-mysql

# Verify
docker ps -a | grep mysql
```

---

### Step 5: Start MySQL with New Configuration

```bash
# Start MySQL (bind mount will be created)
docker-compose up -d mysql

# Wait for healthy status
docker-compose ps

# Watch startup logs
docker-compose logs -f mysql
```

**Expected**: Container should become "healthy" within 30 seconds.

---

### Step 6: Restore Your Data

```bash
cd docker/mysql

# Restore from backup (pick the file from Step 1)
./restore.sh sakip_backup_20250123_143022.sql.gz

# Confirm restore
docker exec -it sakip-mysql mysql -u root -proot -e "SHOW DATABASES;"
docker exec -it sakip-mysql mysql -u root -proot sakip -e "SHOW TABLES;"
```

**Expected**: You should see your database and tables listed.

---

### Step 7: Verify Application Works

```bash
# Start app container
docker-compose up -d app

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear

# Test database connection
docker-compose exec app php artisan migrate:status
```

**Expected**: Application connects to MySQL successfully.

---

### Step 8: Verify Data in New Location

```bash
# Check that data files exist in project directory
ls -la docker/mysql/data/sakip/

# You should see .ibd files like:
# users.ibd
# performance_data.ibd
# assessments.ibd
# etc.
```

---

### Step 9: Remove Old Named Volume (Optional but Recommended)

Once you've verified everything works for a few days:

```bash
# List volumes
docker volume ls | grep mysql

# Remove old volume
docker volume rm sakip_mysql-data

# Confirm it's gone
docker volume ls | grep mysql
```

---

## ✅ Migration Complete!

Your MySQL data is now stored in `docker/mysql/data/` instead of Docker's managed volume.

**What changed**:
- ✅ Data now in project directory: `sakip/docker/mysql/data/`
- ✅ Can view database files directly in Finder/Explorer
- ✅ Easier to backup with standard tools
- ✅ Backup/restore scripts work the same way

**What stayed the same**:
- ✅ Application connects to MySQL exactly the same way
- ✅ All commands work identically
- ✅ Backup/restore scripts unchanged
- ✅ No code changes needed

---

## 🔄 Rolling Back (If Needed)

If you need to revert to named volumes:

```bash
# 1. Stop containers
docker-compose down

# 2. Restore old docker-compose.yml
git checkout docker-compose.yml

# 3. Remove bind mount data
rm -rf docker/mysql/data

# 4. Start with named volume
docker-compose up -d mysql

# 5. Restore data from backup
cd docker/mysql
./restore.sh [your-backup-file]
```

---

## 🎯 After Migration: Next Steps

1. **Test thoroughly**: Use the application for a few days
2. **Monitor performance**: Check if MySQL seems slower (macOS users)
3. **Set up backups**: Use the backup script regularly
4. **Monitor disk space**: `du -sh docker/mysql/data/`
5. **Clean up old volume**: Once verified, remove `mysql-data` volume

---

## ⚠️ Troubleshooting

### Issue: "Permission denied" errors

**macOS Solution**:
```bash
# Fix permissions
sudo chown -R $USER:$USER docker/mysql/data

# Restart MySQL
docker-compose restart mysql
```

### Issue: MySQL won't start

**Check logs**:
```bash
docker-compose logs mysql
```

**Common causes**:
- Old data files in directory → Remove and start fresh
- Corruption during migration → Restore from backup

### Issue: Application can't connect

**Verify MySQL is running**:
```bash
docker-compose ps
```

**Test connection**:
```bash
docker exec -it sakip-mysql mysql -u sakip -psecret sakip
```

**Restart app**:
```bash
docker-compose restart app
```

---

## 📞 Need Help?

If you encounter issues during migration:

1. **Check logs**: `docker-compose logs mysql`
2. **Verify backup**: Make sure `.sql.gz` file exists
3. **Test MySQL CLI**: `docker exec -it sakip-mysql mysql -u root -proot`
4. **Roll back**: Follow the "Rolling Back" section above

---

**Migration typically takes 5-10 minutes** for most databases.

**Good luck! 🚀**
