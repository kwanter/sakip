#!/bin/bash

# Script to extract inline scripts from blade files and create external JS files

echo "Extracting inline scripts from blade files..."

# Array of files to process
files=(
    "resources/views/sakip/targets/index.blade.php"
    "resources/views/sakip/indicators/show.blade.php"
    "resources/views/sakip/assessments/index.blade.php"
    "resources/views/sakip/data-collection/index.blade.php"
    "resources/views/sakip/data-collection/edit.blade.php"
    "resources/views/sakip/data-collection/create.blade.php"
    "resources/views/sakip/audit/index.blade.php"
)

for file in "${files[@]}"; do
    if [[ ! -f "$file" ]]; then
        echo "Skipping $file (not found)"
        continue
    fi

    # Generate JS filename based on blade file path
    # e.g., resources/views/sakip/targets/index.blade.php -> sakip-targets-index.js
    jsname=$(echo "$file" | sed 's|resources/views/||' | sed 's|.blade.php||' | sed 's|/|-|g')
    jsfile="public/js/${jsname}.js"

    echo "Processing $file -> $jsfile"

    # Extract script content between @push('scripts') and @endpush
    # Use awk to handle multi-line extraction
    awk '/@push\('\''scripts'\''\)/,/@endpush/ {
        if (/\<script\>/) { in_script=1; next }
        if (/\<\/script\>/) { in_script=0; next }
        if (in_script) print
    }' "$file" > "$jsfile"

    # Check if file has content
    if [[ -s "$jsfile" ]]; then
        echo "  ✓ Extracted $(wc -l < "$jsfile") lines"
    else
        echo "  ✗ No content extracted, removing file"
        rm "$jsfile"
    fi
done

echo ""
echo "✅ Extraction complete!"
echo "Created JS files can be found in public/js/"
