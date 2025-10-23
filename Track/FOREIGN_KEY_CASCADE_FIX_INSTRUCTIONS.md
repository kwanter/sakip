# Foreign Key Cascade Delete Fix Instructions

## ⚠️ CRITICAL: Data Safety Issue

**Problem**: Several foreign key constraints use `CASCADE` delete, which can cause catastrophic data loss if parent records are deleted.

**Risk Level**: HIGH  
**Impact**: Deleting a user or instansi could cascade delete all historical performance data

---

## Affected Tables & Foreign Keys

### 1. Performance Data Table
**Current Constraints** (DANGEROUS):
- `performance_data.submitted_by` → CASCADE
- `performance_data.instansi_id` → CASCADE

**Recommended Fix**: Change to `SET NULL` for user fields, `RESTRICT` for instansi

### 2. Assessments Table
**Current Constraints** (DANGEROUS):
- `assessments.assessed_by` → CASCADE

**Recommended Fix**: Change to `SET NULL`

### 3. Reports Table
**Current Constraints** (DANGEROUS):
- `reports.generated_by` → CASCADE
- `reports.instansi_id` → CASCADE

**Recommended Fix**: Change to `SET NULL` for user, `RESTRICT` for instansi

### 4. Performance Indicators Table
**Current Constraints** (DANGEROUS):
- `performance_indicators.instansi_id` → CASCADE

**Recommended Fix**: Change to `RESTRICT`

---

## Manual Fix Instructions

### Option 1: Using SQL Directly (Recommended)

```sql
-- First, check for orphaned records that would prevent constraint changes
SELECT COUNT(*) FROM performance_data pd
LEFT JOIN users u ON pd.submitted_by = u.id
WHERE pd.submitted_by IS NOT NULL AND u.id IS NULL;

-- If orphaned records exist, clean them up
UPDATE performance_data 
SET submitted_by = NULL 
WHERE submitted_by NOT IN (SELECT id FROM users);

-- Now fix the foreign keys
-- Performance Data - submitted_by
ALTER TABLE performance_data 
DROP FOREIGN KEY performance_data_submitted_by_foreign;

ALTER TABLE performance_data 
ADD CONSTRAINT performance_data_submitted_by_foreign 
FOREIGN KEY (submitted_by) REFERENCES users(id) 
ON DELETE SET NULL;

-- Performance Data - instansi_id
ALTER TABLE performance_data 
DROP FOREIGN KEY performance_data_instansi_id_foreign;

ALTER TABLE performance_data 
ADD CONSTRAINT performance_data_instansi_id_foreign 
FOREIGN KEY (instansi_id) REFERENCES instansis(id) 
ON DELETE RESTRICT;

-- Assessments - assessed_by
ALTER TABLE assessments 
DROP FOREIGN KEY assessments_assessed_by_foreign;

ALTER TABLE assessments 
ADD CONSTRAINT assessments_assessed_by_foreign 
FOREIGN KEY (assessed_by) REFERENCES users(id) 
ON DELETE SET NULL;

-- Reports - generated_by
ALTER TABLE reports 
DROP FOREIGN KEY reports_generated_by_foreign;

ALTER TABLE reports 
ADD CONSTRAINT reports_generated_by_foreign 
FOREIGN KEY (generated_by) REFERENCES users(id) 
ON DELETE SET NULL;

-- Reports - instansi_id
ALTER TABLE reports 
DROP FOREIGN KEY reports_instansi_id_foreign;

ALTER TABLE reports 
ADD CONSTRAINT reports_instansi_id_foreign 
FOREIGN KEY (instansi_id) REFERENCES instansis(id) 
ON DELETE RESTRICT;

-- Performance Indicators - instansi_id
ALTER TABLE performance_indicators 
DROP FOREIGN KEY performance_indicators_instansi_id_foreign;

ALTER TABLE performance_indicators 
ADD CONSTRAINT performance_indicators_instansi_id_foreign 
FOREIGN KEY (instansi_id) REFERENCES instansis(id) 
ON DELETE RESTRICT;

-- Programs - instansi_id
ALTER TABLE programs 
DROP FOREIGN KEY programs_instansi_id_foreign;

ALTER TABLE programs 
ADD CONSTRAINT programs_instansi_id_foreign 
FOREIGN KEY (instansi_id) REFERENCES instansis(id) 
ON DELETE RESTRICT;
```

### Option 2: Create a Laravel Migration

Create a new migration file:

```bash
php artisan make:migration fix_cascade_delete_constraints
```

Then use the SQL above in a raw query:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clean up orphaned records first
        DB::statement("UPDATE performance_data SET submitted_by = NULL WHERE submitted_by NOT IN (SELECT id FROM users)");
        DB::statement("UPDATE performance_data SET validated_by = NULL WHERE validated_by IS NOT NULL AND validated_by NOT IN (SELECT id FROM users)");
        DB::statement("UPDATE assessments SET assessed_by = NULL WHERE assessed_by NOT IN (SELECT id FROM users)");
        DB::statement("UPDATE reports SET generated_by = NULL WHERE generated_by NOT IN (SELECT id FROM users)");
        
        // Fix performance_data constraints
        DB::statement("ALTER TABLE performance_data DROP FOREIGN KEY performance_data_submitted_by_foreign");
        DB::statement("ALTER TABLE performance_data ADD CONSTRAINT performance_data_submitted_by_foreign FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL");
        
        DB::statement("ALTER TABLE performance_data DROP FOREIGN KEY performance_data_instansi_id_foreign");
        DB::statement("ALTER TABLE performance_data ADD CONSTRAINT performance_data_instansi_id_foreign FOREIGN KEY (instansi_id) REFERENCES instansis(id) ON DELETE RESTRICT");
        
        // Fix other constraints similarly...
    }

    public function down(): void
    {
        // Reverting is risky - document only
    }
};
```

---

## Testing the Fix

After applying the changes, test with:

```sql
-- Test 1: Try to delete a user (should succeed, setting FKs to NULL)
-- Create test user
INSERT INTO users (id, name, email, password) VALUES ('test-uuid', 'Test User', 'test@test.com', 'hash');

-- Create test data
INSERT INTO performance_data (performance_indicator_id, instansi_id, submitted_by, period, actual_value, status) 
VALUES (1, 'instansi-uuid', 'test-uuid', '2025-01', 100, 'draft');

-- Delete user (should succeed and set submitted_by to NULL)
DELETE FROM users WHERE id = 'test-uuid';

-- Verify data preserved
SELECT submitted_by FROM performance_data WHERE period = '2025-01'; -- Should be NULL

-- Test 2: Try to delete instansi with data (should FAIL with RESTRICT)
DELETE FROM instansis WHERE id = 'instansi-uuid'; -- Should error
```

---

## Verification Checklist

After applying fixes:

- [ ] Check all foreign key constraints: `SHOW CREATE TABLE performance_data;`
- [ ] Verify user deletion doesn't cascade: Test deleting a user
- [ ] Verify instansi deletion is blocked: Try deleting an instansi with data
- [ ] Check audit logs still work
- [ ] Test application functionality

---

## Why These Changes?

### SET NULL for User References
**Reason**: Users may leave the organization, but their historical contributions (submitted data, assessments) should be preserved for audit trails.

**Effect**: When a user is deleted, their UUID in `submitted_by`, `assessed_by`, etc. becomes NULL, but the data remains.

### RESTRICT for Instansi References
**Reason**: Government institutions rarely close. If they do, all their data should be explicitly archived or transferred, not automatically deleted.

**Effect**: Cannot delete an `instansi` if it has related performance data. Must clean up data first.

---

## Rollback Plan

If issues arise, the original CASCADE constraints can be restored:

```sql
ALTER TABLE performance_data DROP FOREIGN KEY performance_data_submitted_by_foreign;
ALTER TABLE performance_data ADD CONSTRAINT performance_data_submitted_by_foreign 
FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE;
```

⚠️ **WARNING**: Only rollback if absolutely necessary. CASCADE deletes are dangerous!

---

## Priority

**When to Apply**: Within 1 week (before production deployment)  
**Risk if Not Applied**: HIGH - Accidental data loss  
**Effort Required**: 15-30 minutes  
**Downtime Required**: ~5 minutes (during low-traffic period)

---

## Support

If you encounter errors during the fix:
1. Check for orphaned foreign key references
2. Verify all parent tables exist
3. Ensure no duplicate constraint names
4. Review MySQL error logs

---

*Last Updated: October 22, 2025*
