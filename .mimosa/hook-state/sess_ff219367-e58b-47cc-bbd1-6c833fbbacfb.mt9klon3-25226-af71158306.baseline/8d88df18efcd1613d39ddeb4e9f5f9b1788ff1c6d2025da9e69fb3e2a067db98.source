#!/bin/bash

# Script to convert onclick attributes to data-onclick for CSP compliance
# This processes all blade files in the resources/views directory

echo "Converting onclick to data-onclick in blade files..."

# Find all .blade.php files and replace onclick with data-onclick
find resources/views -name "*.blade.php" -type f -exec sed -i '' 's/onclick=/data-onclick=/g' {} \;

echo "✅ Conversion complete!"
echo ""
echo "Modified files:"
find resources/views -name "*.blade.php" -type f -exec grep -l "data-onclick" {} \;

echo ""
echo "Next steps:"
echo "1. Test your application to ensure all onclick functionality still works"
echo "2. If everything works, commit the changes"
echo "3. The event delegation handler in custom-scripts.js will execute these handlers"
