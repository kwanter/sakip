# Tambah Indikator Kinerja Feature Documentation

**Feature**: Add Performance Indicator (Tambah Indikator Kinerja)
**Module**: SAKIP Performance Management
**Date**: December 2024
**Status**: ✅ Fully Functional

## Overview

The "Tambah Indikator Kinerja" (Add Performance Indicator) feature allows authorized users to create new performance indicators for their institution. This feature is a core component of the SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah) module.

## Access Control

### Required Permissions

To access this feature, users must have one of the following roles:
- **Superadmin** - Full access to all features
- **Executive** - Can create and manage indicators for their institution

### Policy Rules

The feature is controlled by `PerformanceIndicatorPolicy`:
- `create()` - Requires user to have 'superadmin' or 'executive' role
- Users can only create indicators for their assigned institution (`instansi_id`)

## How to Access

### Navigation

1. **From Main Menu**:
   - Navigate to Sidebar → "Indikator Kinerja"
   - Click the "Tambah Indikator" button (blue button with plus icon)

2. **From Dashboard**:
   - Executive Dashboard → Quick Actions → "Tambah Indikator"

3. **Direct URL**:
   - Route: `sakip.indicators.create`
   - URL: `/sakip/indicators/create`

### Button Location

The "Tambah Indikator" button is located at:
- **Page**: Indikator Kinerja Index (`/sakip/indicators`)
- **Position**: Top right corner, next to page title
- **Style**: Blue button with white text and plus icon
- **Visibility**: Only shown to users with create permission

## Form Structure

### Multi-Step Form

The form is organized into 4 progressive steps:

#### Step 1: Basic Information (Informasi Dasar)
- Indicator Code
- Indicator Name
- Category
- Description
- Measurement Unit

#### Step 2: Targets & Formula (Target & Formula)
- Calculation Formula
- Data Source
- Collection Method
- Frequency
- Weight

#### Step 3: Validation & Documents (Validasi & Dokumen)
- Supporting documents upload
- Evidence files
- Notes and comments

#### Step 4: Review & Submit
- Preview all entered data
- Final validation
- Submit button

## Form Fields

### Required Fields (*)

| Field | Type | Max Length | Validation Rules |
|-------|------|------------|------------------|
| `code` | String | 50 | Required, unique, alphanumeric |
| `name` | String | 255 | Required |
| `measurement_unit` | String | 100 | Required |
| `data_source` | String | 255 | Required |
| `collection_method` | Enum | - | Required, must be in: manual, automated, survey, interview, observation, document_review |
| `frequency` | Enum | - | Required, must be in: monthly, quarterly, semester, annual |
| `category` | Enum | - | Required, must be in: input, output, outcome, impact |
| `weight` | Numeric | - | Required, 0-100 |

### Optional Fields

| Field | Type | Description |
|-------|------|-------------|
| `description` | Text | Detailed description of the indicator |
| `calculation_formula` | Text | Mathematical or logical formula for calculation |
| `is_mandatory` | Boolean | Whether indicator is mandatory (default: false) |
| `metadata` | JSON | Additional custom fields |
| `notes` | Text | Additional notes |

## Field Options

### Category (Kategori)
- `input` - Input
- `output` - Output
- `outcome` - Outcome
- `impact` - Impact

### Frequency (Frekuensi)
- `monthly` - Bulanan (Monthly)
- `quarterly` - Triwulan (Quarterly)
- `semester` - Semester (Bi-annual)
- `annual` - Tahunan (Annual)

### Collection Method (Metode Pengumpulan)
- `manual` - Manual
- `automated` - Otomatis (Automated)
- `survey` - Survei (Survey)
- `interview` - Wawancara (Interview)
- `observation` - Observasi (Observation)
- `document_review` - Telaah Dokumen (Document Review)

## Auto-Generated Fields

The following fields are automatically set by the system:

| Field | Source | Description |
|-------|--------|-------------|
| `instansi_id` | Auth User | Taken from logged-in user's institution |
| `created_by` | Auth User | User ID who created the indicator |
| `updated_by` | Auth User | User ID for last update |
| `created_at` | System | Timestamp of creation |
| `updated_at` | System | Timestamp of last update |

## Code Generation

### Auto-generate Code Feature

Users can click the magic wand button (🪄) next to the code field to auto-generate a unique indicator code.

**Format**: `IK-{PREFIX}-{NUMBER}`
- `IK` - Indikator Kinerja prefix
- `PREFIX` - Based on category or name
- `NUMBER` - Sequential number

**Example**: `IK-U-001`, `IK-OUT-042`

## Workflow

### Step-by-Step Process

1. **Navigate** to Indikator Kinerja page
2. **Click** "Tambah Indikator" button (requires permission)
3. **Fill** Step 1: Basic Information
   - Enter indicator code (or auto-generate)
   - Enter indicator name
   - Select category
   - Add description
4. **Proceed** to Step 2: Targets & Formula
   - Enter calculation formula
   - Specify data source
   - Select collection method
   - Choose frequency
   - Set weight (0-100)
5. **Upload** Step 3: Documents (optional)
   - Upload supporting documents
   - Add evidence files
   - Include notes
6. **Review** Step 4: Final Check
   - Verify all information
   - Make corrections if needed
7. **Submit** the form
8. **Redirect** to indicator detail page on success

## Validation

### Client-Side Validation

- Real-time validation as user types
- Character count for text fields
- Field format validation
- Required field indicators (*)

### Server-Side Validation

Handled by `StorePerformanceIndicatorRequest`:
- Data type validation
- Length constraints
- Enum value validation
- Database uniqueness checks
- Authorization checks

### Error Handling

- Validation errors displayed below respective fields
- Form submission prevented if validation fails
- User-friendly error messages in Indonesian
- Scrolls to first error on submit

## Database Transaction

The creation process uses database transactions:

```php
DB::beginTransaction();
try {
    // Create indicator
    // Log audit trail
    DB::commit();
    return redirect with success message
} catch (Exception $e) {
    DB::rollBack();
    return back with error message
}
```

## Audit Logging

Every indicator creation is logged in `audit_logs` table:

| Field | Value |
|-------|-------|
| `action` | CREATE |
| `module` | SAKIP |
| `description` | "Membuat indikator kinerja: {name}" |
| `old_values` | null |
| `new_values` | Full indicator data |

## Success Response

After successful creation:
- **Redirect**: To indicator detail page (`sakip.indicators.show`)
- **Message**: "Indikator kinerja berhasil dibuat."
- **Status Code**: 302 (Redirect)

## Error Handling

### Common Errors

1. **Permission Denied**
   - User doesn't have create permission
   - Returns 403 Forbidden

2. **Validation Error**
   - Invalid field data
   - Returns to form with error messages

3. **Duplicate Code**
   - Indicator code already exists
   - Message: "The indicator code has already been taken."

4. **Database Error**
   - Connection issues
   - Returns generic error message

## API Endpoints

### Related Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/sakip/indicators` | List all indicators |
| GET | `/sakip/indicators/create` | Show create form |
| POST | `/sakip/indicators` | Store new indicator |
| GET | `/sakip/indicators/{id}` | Show indicator detail |
| GET | `/sakip/api/datatables/indicator` | DataTable data |

## Technical Stack

### Frontend
- **Blade Templates**: Server-side rendering
- **Bootstrap 5**: UI framework
- **Tailwind CSS**: Utility classes
- **JavaScript**: Form interactivity
- **DataTables**: List management

### Backend
- **Laravel 11**: PHP framework
- **Eloquent ORM**: Database interaction
- **Form Requests**: Validation
- **Policies**: Authorization
- **DB Transactions**: Data integrity

### Database
- **Table**: `performance_indicators`
- **Related**: `instansis`, `audit_logs`, `users`

## File Structure

```
app/
├── Http/
│   ├── Controllers/Sakip/
│   │   └── PerformanceIndicatorController.php
│   └── Requests/Sakip/
│       └── StorePerformanceIndicatorRequest.php
├── Models/
│   └── PerformanceIndicator.php
└── Policies/Sakip/
    └── PerformanceIndicatorPolicy.php

resources/
└── views/sakip/indicators/
    ├── index.blade.php
    ├── create.blade.php
    ├── show.blade.php
    └── edit.blade.php

routes/
└── web_sakip.php
```

## Testing

### Manual Testing Checklist

- [ ] Button visible for authorized users
- [ ] Button hidden for unauthorized users
- [ ] Form loads without errors
- [ ] All dropdown options populated
- [ ] Code auto-generation works
- [ ] Form validation triggers correctly
- [ ] Successful submission creates indicator
- [ ] Audit log entry created
- [ ] Redirects to detail page
- [ ] Success message displayed
- [ ] Error handling works properly

### Test User Roles

1. **Superadmin**: Full access ✅
2. **Executive**: Can create ✅
3. **Assessor**: No access ❌
4. **Data Collector**: No access ❌
5. **Auditor**: No access ❌

## Troubleshooting

### Button Not Visible

**Problem**: "Tambah Indikator" button doesn't appear

**Solutions**:
1. Check user role (must be superadmin or executive)
2. Verify user is authenticated
3. Clear browser cache
4. Check if `@can` directive is properly used

### Form Not Loading

**Problem**: Create form returns 403 or 500 error

**Solutions**:
1. Check policy authorization
2. Verify user has `instansi_id` set
3. Check database connection
4. Review Laravel logs: `storage/logs/laravel.log`

### Validation Errors

**Problem**: Form won't submit due to validation errors

**Solutions**:
1. Check all required fields are filled
2. Verify dropdown selections match allowed values
3. Check code uniqueness
4. Review field lengths
5. Inspect browser console for JS errors

### Database Errors

**Problem**: Error during save operation

**Solutions**:
1. Check database connection
2. Verify table exists and is migrated
3. Check foreign key constraints
4. Review transaction logs
5. Clear cache: `php artisan cache:clear`

## Recent Changes

### Fixed Issues (December 2024)

1. ✅ Added link to "Tambah Indikator" button
2. ✅ Updated validation rules to match controller
3. ✅ Fixed category enum values (input, output, outcome, impact)
4. ✅ Added collection method validation
5. ✅ Made instansi_id optional in validation
6. ✅ Added permission check with @can directive
7. ✅ Added icon to button for better UX

## Future Enhancements

### Planned Features

- [ ] Bulk import indicators from Excel/CSV
- [ ] Indicator templates for quick creation
- [ ] Duplicate indicator feature
- [ ] Advanced formula builder
- [ ] Real-time preview of indicator
- [ ] Indicator categorization by unit/department
- [ ] Indicator versioning and history
- [ ] Collaborative editing

## Support & Maintenance

### For Developers

- Controller: `app/Http/Controllers/Sakip/PerformanceIndicatorController.php`
- Form Request: `app/Http/Requests/Sakip/StorePerformanceIndicatorRequest.php`
- Policy: `app/Policies/Sakip/PerformanceIndicatorPolicy.php`
- View: `resources/views/sakip/indicators/create.blade.php`

### Common Maintenance Tasks

1. **Update Validation Rules**: Edit `StorePerformanceIndicatorRequest.php`
2. **Modify Form Fields**: Edit `create.blade.php`
3. **Change Permissions**: Update `PerformanceIndicatorPolicy.php`
4. **Add New Categories**: Update `getCategories()` method in controller

## References

- [SAKIP Guidelines](https://www.menpan.go.id)
- [Laravel Documentation](https://laravel.com/docs)
- [Bootstrap Documentation](https://getbootstrap.com/docs/5.3/)

---

**Last Updated**: December 2024
**Version**: 1.0
**Status**: Production Ready ✅