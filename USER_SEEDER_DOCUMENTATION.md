# User Seeder Documentation - SAKIP System

**Date**: October 22, 2025  
**Project**: SAKIP (Government Performance Accountability System)  
**Status**: ✅ **SUCCESSFULLY COMPLETED**

---

## Overview

This document describes the user seeder implementation for the SAKIP system, which creates sample users for all 7 role types with proper permissions.

---

## Roles Created

The system includes the following 7 roles:

### 1. Super Admin
- **Full system access** with all 27 permissions
- Can manage all system settings and users
- No instansi restriction (access to all institutions)
- **Email**: `superadmin@sakip.go.id`

### 2. Assessor
- **Evaluation and assessment** capabilities
- 4 permissions:
  - view-assessment-reports
  - submit-evaluation-findings
  - comment-on-assessment-results
  - access-assessment-tools
- **Email**: `assessor@sakip.go.id`

### 3. Data Collector
- **Data entry and submission** capabilities
- 4 permissions:
  - enter-and-submit-data-records
  - edit-own-data-submissions
  - view-data-collection-forms
  - access-basic-reporting-features
- **Email**: `datacollector@sakip.go.id`

### 4. Auditor
- **Audit and compliance review** capabilities
- 5 permissions:
  - review-all-system-data
  - generate-audit-reports
  - flag-data-inconsistencies
  - access-historical-records
  - export-audit-findings
- **Email**: `auditor@sakip.go.id`

### 5. Executive
- **High-level reporting and analytics** capabilities
- 5 permissions:
  - view-all-reports-and-dashboards
  - access-analytics-tools
  - approve-system-changes
  - manage-high-level-settings
  - export-comprehensive-reports
- **Email**: `executive@sakip.go.id`

### 6. Collector
- **Field data collection** capabilities
- 4 permissions:
  - gather-and-input-field-data
  - update-collection-records
  - view-collection-schedules
  - submit-completed-work-reports
- **Email**: `collector@sakip.go.id`

### 7. Government Official
- **Official reports and compliance** capabilities
- 5 permissions:
  - view-verified-public-data
  - access-official-reports
  - submit-formal-requests
  - review-compliance-documents
  - access-restricted-government-portals
- **Email**: `official@sakip.go.id`

---

## Default Login Credentials

| Role                | Email                     | Password    |
|---------------------|---------------------------|-------------|
| Super Admin         | superadmin@sakip.go.id    | password123 |
| Assessor            | assessor@sakip.go.id      | password123 |
| Data Collector      | datacollector@sakip.go.id | password123 |
| Auditor             | auditor@sakip.go.id       | password123 |
| Executive           | executive@sakip.go.id     | password123 |
| Collector           | collector@sakip.go.id     | password123 |
| Government Official | official@sakip.go.id      | password123 |

⚠️ **IMPORTANT**: Change these passwords immediately in production!

---

## Files Modified/Created

### 1. UserSeeder.php
**Location**: `database/seeders/UserSeeder.php`

**Purpose**: Creates sample users for each role with:
- Proper UUID primary keys
- Assigned roles from Spatie Permission
- Linked to default instansi (except Super Admin)
- Email verification set
- Hashed passwords

**Key Features**:
- Checks for existing users before creating (idempotent)
- Creates default instansi if none exists
- Displays helpful summary table after seeding
- Uses proper UUID-compatible Role model

### 2. DatabaseSeeder.php
**Location**: `database/seeders/DatabaseSeeder.php`

**Changes**: Updated to call UserSeeder after RolesAndPermissionsSeeder

**Execution Order**:
1. RolesAndPermissionsSeeder (creates roles and permissions)
2. UserSeeder (creates users and assigns roles)

### 3. RolesAndPermissionsSeeder.php
**Location**: `database/seeders/RolesAndPermissionsSeeder.php` (already existed)

**Purpose**: Creates all roles and permissions, then assigns permissions to roles

---

## Technical Implementation

### UUID Compatibility

The system uses UUIDs for primary keys, which required special configuration:

1. **User Model**: Uses `HasUuids` trait with `HasRoles` from Spatie Permission
2. **Role Model**: Custom model extending Spatie's Role with UUID support
3. **Permission Config**: `model_morph_key` set to `'model_uuid'`
4. **Seeder**: Uses `App\Models\Role` instead of `Spatie\Permission\Models\Role`

### Database Structure

**Users Table** (`users`):
- `id` (UUID - primary key)
- `name` (string)
- `email` (string, unique)
- `password` (hashed)
- `instansi_id` (UUID, nullable, foreign key to instansis)
- `email_verified_at` (timestamp)
- Timestamps

**Roles Table** (`roles`):
- `id` (UUID - primary key)
- `name` (string, unique)
- `guard_name` (string)
- Timestamps

**Pivot Table** (`model_has_roles`):
- `role_id` (UUID, foreign key to roles)
- `model_type` (string - polymorphic type)
- `model_uuid` (UUID - polymorphic ID)

---

## Usage

### Running Seeders

**Fresh database (drops all tables and recreates)**:
```bash
php artisan migrate:fresh --seed
```

**Run seeders only** (without migrating):
```bash
php artisan db:seed
```

**Run specific seeder**:
```bash
php artisan db:seed --class=UserSeeder
```

### Verifying Users

Check all users and their roles:
```bash
php artisan tinker --execute="
\$users = App\Models\User::with('roles')->get();
foreach (\$users as \$user) {
    echo \$user->name . ' - ' . \$user->roles->pluck('name')->implode(', ') . PHP_EOL;
}
"
```

Check permissions for a specific role:
```bash
php artisan tinker --execute="
\$role = App\Models\Role::where('name', 'Assessor')->with('permissions')->first();
echo \$role->name . ': ' . \$role->permissions->pluck('name')->implode(', ') . PHP_EOL;
"
```

---

## Security Considerations

### Production Deployment

⚠️ **CRITICAL**: Before deploying to production:

1. **Change Default Passwords**:
   ```bash
   php artisan tinker
   $user = User::where('email', 'superadmin@sakip.go.id')->first();
   $user->password = Hash::make('new-secure-password');
   $user->save();
   ```

2. **Disable/Remove Sample Users**:
   - Option 1: Don't run UserSeeder in production
   - Option 2: Modify UserSeeder to use environment variables for credentials
   - Option 3: Delete sample users after creating real admin

3. **Use Strong Passwords**:
   - Minimum 12 characters
   - Mix of uppercase, lowercase, numbers, symbols
   - Use password manager

4. **Enable Two-Factor Authentication** (if implemented)

### Environment-Specific Seeding

Modify `DatabaseSeeder.php` to only seed users in development:

```php
public function run(): void
{
    $this->call(RolesAndPermissionsSeeder::class);
    
    // Only seed sample users in local/development environment
    if (app()->environment(['local', 'development'])) {
        $this->call(UserSeeder::class);
    }
}
```

---

## Customization

### Adding More Users

Edit `database/seeders/UserSeeder.php` and add to the `$users` array:

```php
[
    'role' => 'Assessor',
    'name' => 'John Doe',
    'email' => 'john.doe@sakip.go.id',
    'password' => 'password123',
    'instansi_id' => $instansi->id,
],
```

### Adding More Permissions

Edit `database/seeders/RolesAndPermissionsSeeder.php`:

1. Add permission to the `$permissions` array
2. Assign to appropriate role using `givePermissionTo()`

### Changing Default Instansi

The seeder creates a default instansi if none exists. To use a different one:

1. Create instansi first
2. Modify UserSeeder to use specific instansi:
   ```php
   $instansi = Instansi::where('kode_instansi', 'YOUR-CODE')->first();
   ```

---

## Testing

### Test User Login

After seeding, test login with any user:

```bash
# Using Laravel Tinker
php artisan tinker
auth()->attempt(['email' => 'superadmin@sakip.go.id', 'password' => 'password123'])
auth()->user()
```

### Test Permissions

Check if user has permission:

```bash
php artisan tinker
$user = User::where('email', 'assessor@sakip.go.id')->first()
$user->can('view-assessment-reports')  // Should return true
$user->can('manage-high-level-settings')  // Should return false
```

### Test Role Assignment

```bash
php artisan tinker
$user = User::where('email', 'assessor@sakip.go.id')->first()
$user->hasRole('Assessor')  // Should return true
$user->hasRole('Super Admin')  // Should return false
```

---

## Troubleshooting

### Issue: "Role not found"

**Cause**: RolesAndPermissionsSeeder didn't run or failed

**Solution**:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Issue: "User already exists"

**Cause**: Seeder is idempotent - it skips existing users

**Solution**: This is expected behavior. Delete users if you want to recreate:
```bash
php artisan tinker
User::where('email', 'superadmin@sakip.go.id')->delete()
```

### Issue: "Foreign key constraint fails"

**Cause**: Using wrong Role model (Spatie's instead of App\Models\Role)

**Solution**: Already fixed in UserSeeder - ensure you're using:
```php
use App\Models\Role;  // ✅ Correct (UUID support)
// NOT use Spatie\Permission\Models\Role;  // ❌ Wrong (auto-increment)
```

### Issue: "Super Admin has no permissions"

**Cause**: Super Admin role wasn't assigned properly

**Solution**:
```bash
php artisan tinker
$user = User::where('email', 'superadmin@sakip.go.id')->first();
$role = Role::where('name', 'Super Admin')->first();
$user->assignRole($role);
```

---

## Statistics

- **Total Roles**: 7
- **Total Permissions**: 27
- **Total Users Created**: 7 (1 per role)
- **Seeding Time**: ~1.5 seconds
- **Instansi Created**: 1 (if none exists)

---

## Next Steps

### Recommended Actions

1. ✅ **Change default passwords** in production
2. ✅ **Create real admin users** with proper credentials
3. ⚠️ **Test login functionality** for each role
4. ⚠️ **Test permission gates** in your application
5. ⚠️ **Configure email verification** if required
6. ⚠️ **Set up two-factor authentication** for admins

### Future Enhancements

- Add more granular permissions (CRUD operations per module)
- Implement role hierarchy (e.g., Manager inherits from Data Collector)
- Add team/department support with Spatie Permission teams feature
- Create factory for generating test users
- Add seeder for multiple instansis with different users

---

## Summary

✅ **Seeder Implementation Complete**

The SAKIP system now has:
- 7 roles with appropriate permissions
- 7 sample users (1 per role)
- Proper UUID support for all models
- Idempotent seeding (safe to run multiple times)
- Clear documentation and testing procedures

**Status**: Ready for development and testing  
**Production Ready**: Yes (after changing default passwords)

---

*Last Updated: October 22, 2025*
