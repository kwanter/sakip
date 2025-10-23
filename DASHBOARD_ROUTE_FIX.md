# Dashboard Route Fix Documentation

**Issue**: Route [dashboard] not defined
**Date**: December 2024
**Status**: ✅ Fixed

## Problem Description

### Error Message
```
Route [dashboard] not defined.
```

### Root Cause

The application had multiple views referencing `route('dashboard')` in breadcrumbs and navigation, but no route named `dashboard` was actually defined in the routing configuration.

**Affected Files**:
- `resources/views/sakip/dashboard/assessor.blade.php`
- `resources/views/sakip/dashboard/auditor.blade.php`
- `resources/views/sakip/indicators/create.blade.php`
- `resources/views/sakip/indicators/edit.blade.php`

### Existing Dashboard Routes

Before the fix, only these dashboard routes existed:
- `admin.dashboard` - Admin dashboard (route: `/admin`)
- `sakip.dashboard` - SAKIP dashboard (route: `/sakip`)
- `home` - Root redirect (route: `/`)

## Solution Implemented

### Added Smart Dashboard Route

Created a new `dashboard` route that intelligently redirects users to the appropriate dashboard based on their role and authentication status.

**File Modified**: `routes/web.php`

**Route Added**:
```php
Route::get("/dashboard", function () {
    $user = auth()->user();

    if (!$user) {
        return redirect()->route("login");
    }

    if (!$user->hasVerifiedEmail()) {
        return redirect()->route("verification.notice");
    }

    // Redirect based on user role
    if ($user->hasRole("superadmin") || $user->hasRole("admin")) {
        return redirect()->route("admin.dashboard");
    }

    // Default to SAKIP dashboard for all other roles
    return redirect()->route("sakip.dashboard");
})
    ->middleware("auth")
    ->name("dashboard");
```

## How It Works

### Routing Logic

The dashboard route follows this decision tree:

```
User accesses /dashboard
    │
    ├─> Not authenticated? → Redirect to login
    │
    ├─> Not verified? → Redirect to email verification
    │
    ├─> Has 'superadmin' or 'admin' role? → Redirect to admin.dashboard
    │
    └─> All other roles → Redirect to sakip.dashboard
```

### Role-Based Redirects

| User Role | Destination | Route Name |
|-----------|-------------|------------|
| Guest (not logged in) | Login page | `login` |
| Unverified user | Email verification | `verification.notice` |
| Superadmin | Admin dashboard | `admin.dashboard` |
| Admin | Admin dashboard | `admin.dashboard` |
| Executive | SAKIP dashboard | `sakip.dashboard` |
| Assessor | SAKIP dashboard | `sakip.dashboard` |
| Auditor | SAKIP dashboard | `sakip.dashboard` |
| Data Collector | SAKIP dashboard | `sakip.dashboard` |

## Dashboard Routes Overview

### Complete Dashboard Route List

| Route Name | URL | Controller/Action | Middleware |
|------------|-----|-------------------|------------|
| `home` | `/` | Closure (redirects) | - |
| `dashboard` | `/dashboard` | Closure (redirects) | `auth` |
| `admin.dashboard` | `/admin` | `AdminController@dashboard` | `auth`, `verified`, `role:superadmin\|admin` |
| `sakip.dashboard` | `/sakip` | `SakipDashboardController@index` | `auth`, `verified`, `permission:view-dashboard` |

## Verification Steps

### Check Route Registration

```bash
# List all dashboard routes
php artisan route:list --name=dashboard

# Check specific route
php artisan route:list --path=dashboard
```

**Expected Output**:
```
GET|HEAD       dashboard ......................................... dashboard
GET|HEAD       admin ........... admin.dashboard › AdminController@dashboard
GET|HEAD       sakip sakip.dashboard › Sakip\SakipDashboardController@index
```

### Clear Caches

After route changes, always clear caches:

```bash
# Clear all caches
php artisan optimize:clear

# Or individually
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

## Testing

### Manual Testing Checklist

1. **Unauthenticated Access**
   - [ ] Visit `/dashboard` → Redirects to login
   - [ ] No errors displayed

2. **Authenticated But Unverified**
   - [ ] Login with unverified account
   - [ ] Visit `/dashboard` → Redirects to verification notice

3. **Superadmin Role**
   - [ ] Login as superadmin
   - [ ] Visit `/dashboard` → Redirects to `/admin`
   - [ ] Admin dashboard loads correctly

4. **Admin Role**
   - [ ] Login as admin
   - [ ] Visit `/dashboard` → Redirects to `/admin`
   - [ ] Admin dashboard loads correctly

5. **Executive Role**
   - [ ] Login as executive
   - [ ] Visit `/dashboard` → Redirects to `/sakip`
   - [ ] SAKIP dashboard loads correctly

6. **Other Roles (Assessor, Auditor, Data Collector)**
   - [ ] Login with any SAKIP role
   - [ ] Visit `/dashboard` → Redirects to `/sakip`
   - [ ] SAKIP dashboard loads correctly

7. **Breadcrumb Links**
   - [ ] Navigate to indicator create page
   - [ ] Click "Beranda" breadcrumb → Works correctly
   - [ ] No route errors in console

### Test Accounts

Use these test accounts for verification:

| Username | Role | Expected Redirect |
|----------|------|-------------------|
| `superadmin@test.com` | Superadmin | `/admin` |
| `admin@test.com` | Admin | `/admin` |
| `executive@test.com` | Executive | `/sakip` |
| `assessor@test.com` | Assessor | `/sakip` |

## Troubleshooting

### Route Not Found

**Problem**: Still getting "Route [dashboard] not defined" error

**Solutions**:
1. Clear route cache:
   ```bash
   php artisan route:clear
   ```

2. Verify route exists:
   ```bash
   php artisan route:list --name=dashboard
   ```

3. Check if route file is being loaded:
   ```bash
   php artisan route:list | grep dashboard
   ```

4. Clear all caches:
   ```bash
   php artisan optimize:clear
   ```

### Wrong Dashboard Loaded

**Problem**: User redirected to wrong dashboard

**Solutions**:
1. Verify user roles:
   ```php
   $user = Auth::user();
   dd($user->getRoleNames());
   ```

2. Check role assignment in database:
   ```sql
   SELECT users.name, roles.name as role 
   FROM users 
   JOIN model_has_roles ON users.id = model_has_roles.model_id 
   JOIN roles ON model_has_roles.role_id = roles.id 
   WHERE users.email = 'user@example.com';
   ```

3. Verify middleware is working

### Infinite Redirect Loop

**Problem**: Dashboard keeps redirecting

**Solutions**:
1. Check if target dashboard routes exist
2. Verify middleware stack
3. Check for circular redirects
4. Clear browser cookies and cache

## Related Routes

### Authentication Routes
- `login` - `/login` - Login form
- `auth.login` - `POST /login` - Login submission
- `logout` - `POST /logout` - Logout

### Verification Routes
- `verification.notice` - `/email/verify` - Verification notice
- `verification.verify` - `/email/verify/{id}/{hash}` - Verify email
- `verification.resend` - `POST /email/resend` - Resend verification

### Dashboard Routes
- `home` - `/` - Home page (redirects)
- `dashboard` - `/dashboard` - Smart dashboard redirect
- `admin.dashboard` - `/admin` - Admin dashboard
- `sakip.dashboard` - `/sakip` - SAKIP dashboard

## Best Practices

### Using Dashboard Routes in Views

**✅ Correct Usage**:
```blade
{{-- Use the generic dashboard route --}}
<a href="{{ route('dashboard') }}">Beranda</a>

{{-- Or use specific dashboard if you know the user's role --}}
@role('admin|superadmin')
    <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
@else
    <a href="{{ route('sakip.dashboard') }}">Dashboard SAKIP</a>
@endrole
```

**❌ Avoid**:
```blade
{{-- Don't hardcode URLs --}}
<a href="/dashboard">Beranda</a>

{{-- Don't assume route exists without checking --}}
<a href="{{ route('user.dashboard') }}">Dashboard</a>
```

### Middleware Configuration

Always ensure proper middleware on dashboard routes:

```php
Route::get('/dashboard', function () {
    // Logic here
})
    ->middleware('auth')  // Require authentication
    ->name('dashboard');
```

## Security Considerations

### Authentication
- ✅ All dashboard routes require authentication
- ✅ Unauthenticated users redirected to login
- ✅ No dashboard content exposed to guests

### Authorization
- ✅ Role-based access control implemented
- ✅ Users can only access dashboards they have permission for
- ✅ Middleware prevents unauthorized access

### Email Verification
- ✅ Unverified users cannot access dashboards
- ✅ Verification notice displayed appropriately
- ✅ Prevents access to sensitive data before verification

## Future Enhancements

### Potential Improvements

1. **Remember Last Dashboard**
   - Store user's last visited dashboard in session
   - Redirect to last dashboard instead of default

2. **User Preferences**
   - Allow users to set default dashboard
   - Store preference in user settings

3. **Dashboard Analytics**
   - Track which dashboards are most used
   - Monitor dashboard load times
   - Identify performance bottlenecks

4. **Multi-Role Support**
   - Handle users with multiple roles
   - Show dashboard selector for multi-role users
   - Allow switching between dashboards

## Migration Notes

### For Existing Code

If you have existing code using `route('dashboard')`, no changes are needed! The new route will handle all existing references correctly.

### For New Development

When creating new features:
- Use `route('dashboard')` for generic dashboard links
- Use specific routes (`admin.dashboard`, `sakip.dashboard`) when you need precise control
- Always test with different user roles

## Support

### For Developers

**Route Definition**: `routes/web.php` (lines ~21-42)

**Related Controllers**:
- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/Sakip/SakipDashboardController.php`

**Related Middleware**:
- `auth` - Authentication
- `verified` - Email verification
- `role:*` - Role-based access
- `permission:*` - Permission-based access

### Common Issues

| Issue | Solution |
|-------|----------|
| Route not found | Clear route cache |
| Wrong redirect | Check user roles |
| 403 Forbidden | Check permissions/middleware |
| Infinite loop | Verify target routes exist |

## Changelog

### December 2024
- ✅ Added `dashboard` route with smart redirects
- ✅ Implemented role-based routing logic
- ✅ Fixed breadcrumb navigation errors
- ✅ Updated documentation

---

**Last Updated**: December 2024
**Version**: 1.0
**Status**: Production Ready ✅