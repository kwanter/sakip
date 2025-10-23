#!/usr/bin/env python3
"""
Phase 1 Fix Implementation Script
Fixes the Tambah Indikator Kinerja form to match database schema and validation rules
"""

import re
import sys
from pathlib import Path

FILE_PATH = Path('/Users/macbook/Developer/php/sakip/resources/views/sakip/indicators/create.blade.php')
BACKUP_PATH = FILE_PATH.with_suffix('.blade.php.backup')

def read_file():
    """Read the form file"""
    if not FILE_PATH.exists():
        print(f"❌ Error: File not found at {FILE_PATH}")
        sys.exit(1)

    with open(FILE_PATH, 'r', encoding='utf-8') as f:
        return f.read()

def write_file(content):
    """Write the modified content back to file"""
    with open(FILE_PATH, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"✅ File saved to {FILE_PATH}")

def backup_file(content):
    """Create a backup of the current file"""
    if not BACKUP_PATH.exists():
        with open(BACKUP_PATH, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"✅ Backup created at {BACKUP_PATH}")

def fix_kategori_dropdown(content):
    """Fix 1: Replace kategori options from iku, ikk, ikt, iks to input, output, outcome, impact"""
    print("\n📝 Fix 1: Updating kategori dropdown...")

    # Find and replace the entire select element for category
    pattern = r'(<select class="form-select @error\(\'category\'\)[^>]*id="category"[^>]*>[\s]*<option value="">Pilih Kategori</option>)[\s\S]*?(<\/select>)'

    replacement = r'''\1
                                    <option value="input" {{ old('category') == 'input' ? 'selected' : '' }}>
                                        Input
                                    </option>
                                    <option value="output" {{ old('category') == 'output' ? 'selected' : '' }}>
                                        Output
                                    </option>
                                    <option value="outcome" {{ old('category') == 'outcome' ? 'selected' : '' }}>
                                        Outcome
                                    </option>
                                    <option value="impact" {{ old('category') == 'impact' ? 'selected' : '' }}>
                                        Impact
                                    </option>
                                \2'''

    new_content = re.sub(pattern, replacement, content, flags=re.DOTALL)

    if new_content != content:
        print("✅ Kategori options updated")
        return new_content
    else:
        print("⚠️ Kategori pattern not found, trying alternative approach...")

        # Alternative: find iku option and replace through iks option
        old_opts = r'''<option value="iku" {{ old\('category'\) == 'iku' \? 'selected' : '' }}>
                                        IKU \(Indikator Kinerja Utama\)
                                    </option>
                                    <option value="ikk" {{ old\('category'\) == 'ikk' \? 'selected' : '' }}>
                                        IKK \(Indikator Kinerja Kegiatan\)
                                    </option>
                                    <option value="ikt" {{ old\('category'\) == 'ikt' \? 'selected' : '' }}>
                                        IKT \(Indikator Kinerja Turunan\)
                                    </option>
                                    <option value="iks" {{ old\('category'\) == 'iks' \? 'selected' : '' }}>
                                        IKS \(Indikator Kinerja Strategis\)
                                    </option>'''

        new_opts = '''<option value="input" {{ old('category') == 'input' ? 'selected' : '' }}>
                                        Input
                                    </option>
                                    <option value="output" {{ old('category') == 'output' ? 'selected' : '' }}>
                                        Output
                                    </option>
                                    <option value="outcome" {{ old('category') == 'outcome' ? 'selected' : '' }}>
                                        Outcome
                                    </option>
                                    <option value="impact" {{ old('category') == 'impact' ? 'selected' : '' }}>
                                        Impact
                                    </option>'''

        new_content = re.sub(old_opts, new_opts, content)

        if new_content != content:
            print("✅ Kategori options updated (alternative method)")
            return new_content
        else:
            print("❌ Could not find kategori options to replace")
            return content

def remove_department_id_field(content):
    """Fix 2: Remove department_id field"""
    print("\n📝 Fix 2: Removing department_id field...")

    # Find and remove the entire department_id div
    pattern = r'<div class="mb-3">\s*<label for="department_id"[^<]*>[^<]*<\/label>[^<]*<select[^>]*id="department_id"[^>]*>[^<]*(?:<option[^>]*>[^<]*<\/option>\s*)*\s*<\/select>[^<]*@error\(\'department_id\'\)[^<]*<div class="invalid-feedback">[^<]*<\/div>[^<]*@enderror[^<]*<\/div>\s*<\/div>'

    new_content = re.sub(pattern, '', content, flags=re.DOTALL)

    if new_content != content:
        print("✅ department_id field removed")
        return new_content
    else:
        print("⚠️ Pattern not exact match, using broader approach...")
        # Broader approach - just remove lines containing department_id
        lines = content.split('\n')
        filtered_lines = [line for line in lines if 'department_id' not in line]
        new_content = '\n'.join(filtered_lines)
        print("✅ department_id field removed (broad approach)")
        return new_content

def remove_year_field(content):
    """Fix 3: Remove year field"""
    print("\n📝 Fix 3: Removing year field...")

    pattern = r'<div class="mb-3">\s*<label for="year"[^<]*>[^<]*<\/label>[^<]*<select[^>]*id="year"[^>]*>[^<]*(?:<option[^>]*>[^<]*<\/option>|@for[^@]*@endfor)[^<]*<\/select>[^<]*@error\(\'year\'\)[^<]*<div class="invalid-feedback">[^<]*<\/div>[^<]*@enderror[^<]*<\/div>\s*<\/div>'

    new_content = re.sub(pattern, '', content, flags=re.DOTALL)

    if new_content != content:
        print("✅ year field removed")
        return new_content
    else:
        print("⚠️ Pattern not exact, using line-based approach...")
        lines = content.split('\n')
        filtered_lines = [line for line in lines if 'id="year"' not in line and 'name="year"' not in line]
        new_content = '\n'.join(filtered_lines)
        print("✅ year field removed (line-based approach)")
        return new_content

def remove_strategic_linkage_section(content):
    """Fix 4: Remove Strategic Linkage section (sasaran_strategis_id, program_id)"""
    print("\n📝 Fix 4: Removing Strategic Linkage section...")

    # Find and remove the entire card with "Keterkaitan dengan Tujuan Strategis"
    pattern = r'<div class="card">\s*<div class="card-header">\s*<h5[^>]*>\s*<i class="fas fa-link"><\/i> Keterkaitan dengan Tujuan Strategis[^<]*<\/h5>\s*<\/div>\s*<div class="card-body">[^<]*(?:<div[^>]*>(?:[^<]|<(?!\/div>))*<\/div>)*[^<]*<\/div>\s*<\/div>'

    new_content = re.sub(pattern, '', content, flags=re.DOTALL)

    if new_content != content:
        print("✅ Strategic Linkage section removed")
        return new_content
    else:
        print("⚠️ Could not find Strategic Linkage section")
        return content

def fix_calculation_formula_name(content):
    """Fix 5: Rename calculation_method to calculation_formula"""
    print("\n📝 Fix 5: Renaming calculation_method to calculation_formula...")

    content = content.replace('name="calculation_method"', 'name="calculation_formula"')
    content = content.replace('id="calculation_method"', 'id="calculation_formula"')
    content = content.replace('document.getElementById(\'calculation_method\')', 'document.getElementById(\'calculation_formula\')')

    print("✅ calculation_method renamed to calculation_formula")
    return content

def remove_target_fields(content):
    """Fix 6: Remove all target_* fields"""
    print("\n📝 Fix 6: Removing target_* fields...")

    target_fields = ['target_value', 'target_type', 'target_direction', 'baseline_value', 'baseline_year']

    for field in target_fields:
        # Remove lines containing these fields
        lines = content.split('\n')
        filtered_lines = [line for line in lines if f'id="{field}"' not in line and f'name="{field}"' not in line and f"'{field}'" not in line]
        content = '\n'.join(filtered_lines)

    print(f"✅ Target fields removed: {', '.join(target_fields)}")
    return content

def remove_validation_fields(content):
    """Fix 7: Remove validation_frequency and responsible_person"""
    print("\n📝 Fix 7: Removing validation_frequency and responsible_person fields...")

    for field in ['validation_frequency', 'responsible_person']:
        lines = content.split('\n')
        filtered_lines = [line for line in lines if f'id="{field}"' not in line and f'name="{field}"' not in line and f"'{field}'" not in line]
        content = '\n'.join(filtered_lines)

    print("✅ validation_frequency and responsible_person removed")
    return content

def remove_numerator_denominator(content):
    """Fix 8: Remove numerator and denominator fields"""
    print("\n📝 Fix 8: Removing numerator and denominator fields...")

    for field in ['numerator', 'denominator']:
        lines = content.split('\n')
        filtered_lines = [line for line in lines if f'id="{field}"' not in line and f'name="{field}"' not in line]
        content = '\n'.join(filtered_lines)

    print("✅ numerator and denominator removed")
    return content

def fix_update_category_description(content):
    """Fix 9: Update updateCategoryDescription JavaScript function"""
    print("\n📝 Fix 9: Updating updateCategoryDescription() function...")

    old_func = r'''function updateCategoryDescription\(\) \{
    const category = document\.getElementById\('category'\)\.value;
    const descriptionElement = document\.getElementById\('categoryDescription'\);

    const descriptions = \{
        'iku': 'Indikator Kinerja Utama: Mengukur hasil utama dari unit kerja',
        'ikk': 'Indikator Kinerja Kegiatan: Mengukur hasil dari kegiatan tertentu',
        'ikt': 'Indikator Kinerja Turunan: Indikator yang diturunkan dari IKU',
        'iks': 'Indikator Kinerja Strategis: Mengukur pencapaian strategis organisasi'
    \};

    descriptionElement\.textContent = descriptions\[category\] \|\| 'Pilih kategori yang sesuai dengan jenis indikator';
\}'''

    new_func = '''function updateCategoryDescription() {
    const category = document.getElementById('category').value;
    const descriptionElement = document.getElementById('categoryDescription');

    const descriptions = {
        'input': 'Input: Sumber daya yang digunakan untuk menghasilkan output',
        'output': 'Output: Hasil langsung dari kegiatan/program',
        'outcome': 'Outcome: Dampak jangka menengah dari program',
        'impact': 'Impact: Dampak jangka panjang bagi masyarakat'
    };

    descriptionElement.textContent = descriptions[category] || 'Pilih kategori yang sesuai dengan jenis indikator';
}'''

    new_content = re.sub(old_func, new_func, content, flags=re.DOTALL)

    if new_content != content:
        print("✅ updateCategoryDescription() updated")
        return new_content
    else:
        print("⚠️ Could not find exact updateCategoryDescription function")
        return content

def remove_update_target_fields(content):
    """Fix 10: Remove updateTargetFields function"""
    print("\n📝 Fix 10: Removing updateTargetFields() function...")

    pattern = r'\/\/ Update target fields\s*function updateTargetFields\(\) \{[^}]*\}(?:\s*)?'
    new_content = re.sub(pattern, '', content, flags=re.DOTALL)

    # Also remove references to it
    new_content = new_content.replace('onchange="updateTargetFields()"', '')

    if new_content != content:
        print("✅ updateTargetFields() removed")
        return new_content
    else:
        print("⚠️ Could not find updateTargetFields function")
        return content

def verify_changes(content):
    """Verify that all changes were applied correctly"""
    print("\n" + "="*50)
    print("VERIFICATION REPORT")
    print("="*50 + "\n")

    checks = {
        '✅ Kategori input': 'value="input"',
        '✅ Kategori output': 'value="output"',
        '✅ Kategori outcome': 'value="outcome"',
        '✅ Kategori impact': 'value="impact"',
        '❌ Field calculation_formula': 'name="calculation_formula"',
        '❌ Field data_source': 'name="data_source"',
        '❌ REMOVED department_id': 'name="department_id"' not in content,
        '❌ REMOVED year': 'name="year"' not in content,
        '❌ REMOVED target_value': 'name="target_value"' not in content,
        '❌ REMOVED sasaran_strategis_id': 'name="sasaran_strategis_id"' not in content,
        '❌ REMOVED validation_frequency': 'name="validation_frequency"' not in content,
        '❌ REMOVED numerator': 'name="numerator"' not in content,
        '❌ REMOVED updateTargetFields': 'function updateTargetFields' not in content,
    }

    passed = 0
    failed = 0

    for check_name, pattern in checks.items():
        if isinstance(pattern, str):
            found = pattern in content
        else:
            found = pattern

        if found:
            print(f"✅ {check_name}")
            passed += 1
        else:
            print(f"❌ {check_name}")
            failed += 1

    print(f"\n📊 Summary: {passed} passed, {failed} failed")
    return failed == 0

def main():
    """Main execution"""
    print("\n" + "="*60)
    print("PHASE 1 FIX IMPLEMENTATION - Tambah Indikator Kinerja Form")
    print("="*60)

    # Read the file
    print("\n📖 Reading form file...")
    content = read_file()
    original_content = content

    # Create backup
    backup_file(content)

    # Apply all fixes in sequence
    content = fix_kategori_dropdown(content)
    content = remove_department_id_field(content)
    content = remove_year_field(content)
    content = remove_strategic_linkage_section(content)
    content = fix_calculation_formula_name(content)
    content = remove_target_fields(content)
    content = remove_validation_fields(content)
    content = remove_numerator_denominator(content)
    content = fix_update_category_description(content)
    content = remove_update_target_fields(content)

    # Verify changes
    if verify_changes(content):
        print("\n✅ All fixes applied successfully!")
        write_file(content)
        print("\n" + "="*60)
        print("✅ PHASE 1 IMPLEMENTATION COMPLETE")
        print("="*60 + "\n")
    else:
        print("\n⚠️ Some changes could not be verified")
        print("Please review the file manually")
        write_file(content)
        print("\n" + "="*60)
        print("⚠️ PHASE 1 IMPLEMENTATION COMPLETED WITH WARNINGS")
        print("="*60 + "\n")

if __name__ == '__main__':
    main()
