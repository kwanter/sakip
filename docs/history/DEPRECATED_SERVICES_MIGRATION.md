# Migration Guide: Deprecated Services to New Validators

This guide helps you migrate from deprecated validation services to the new specialized validators.

## Overview

The following services are deprecated and should be replaced:

| Deprecated Service | Replacement |
|-------------------|-------------|
| `SakipValidationService` | Individual validators in `App\Services\Validation\*` |
| `DataValidationService` | `PerformanceDataValidator`, `DataIntegrityChecker` |
| `ValidationOrchestrator` | Use individual validators directly |

## Migration Steps

### 1. Replace SakipValidationService

**Before:**
```php
use App\Services\SakipValidationService;

$validationService = new SakipValidationService();
$result = $validationService->validatePerformanceData($data);
```

**After:**
```php
use App\Services\Validation\PerformanceDataValidator;

$validator = new PerformanceDataValidator();
$result = $validator->validate($data);
```

### 2. Replace DataValidationService Methods

**Before:**
```php
use App\Services\DataValidationService;

$service = new DataValidationService();
$duplicates = $service->findDuplicates($instansiId);
$orphaned = $service->findOrphaned($instansiId);
```

**After:**
```php
use App\Services\Validation\DataIntegrityChecker;

$checker = new DataIntegrityChecker();
$duplicates = $checker->findDuplicateRecords($instansiId);
$orphaned = $checker->findOrphanedRecords($instansiId);
```

### 3. Target Validation

**Before:**
```php
$validationService = new SakipValidationService();
$result = $validationService->validateTarget($targetData);
```

**After:**
```php
use App\Services\Validation\TargetValidator;

$validator = new TargetValidator();
$result = $validator->validate($targetData);
```

### 4. Assessment Validation

**Before:**
```php
$validationService = new SakipValidationService();
$result = $validationService->validateAssessment($assessmentData);
```

**After:**
```php
use App\Services\Validation\AssessmentValidator;

$validator = new AssessmentValidator();
$result = $validator->validate($assessmentData);
```

## Benefits of New Validators

1. **Single Responsibility**: Each validator focuses on one domain
2. **Better Testing**: Easier to write unit tests for specific validators
3. **Performance**: Optimized queries for specific validation tasks
4. **Maintainability**: Clearer code organization

## Timeline

- **Now**: Both old and new services work
- **Next Release**: Deprecation warnings will be added
- **Future Release**: Deprecated services will be removed

## Need Help?

If you encounter issues during migration, check:
1. The new validator's PHPDoc for method signatures
2. Existing tests in `tests/Feature/` for usage examples
3. Contact the development team for assistance
