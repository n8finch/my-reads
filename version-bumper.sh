#!/bin/bash

PLUGIN_FILE="my-reads.php"  # Change this. This is the main plugin file where the version is located.
POT_FILE="my-reads.pot"  # Change this

# Check if a version was passed as an argument
if [[ -n "$1" ]]; then
  VERSION="$1"
  echo "Using provided version: $VERSION"

  # Update version in style.css
  if [[ -f "$PLUGIN_FILE" ]]; then
    echo "Updating version in $PLUGIN_FILE..."
    sed -i.bak -E "s/(Version:[[:space:]]*)[0-9.]+/\1$VERSION/" "$PLUGIN_FILE"
    rm "$PLUGIN_FILE.bak"
  else
    echo "File $PLUGIN_FILE not found!"
    exit 1
  fi
else
  # No argument provided — get version from style.css
  VERSION=$(grep -i "Version:" "$PLUGIN_FILE" | head -n 1 | sed -E 's/.*Version:[[:space:]]*([0-9.]+).*/\1/')
  if [[ -z "$VERSION" ]]; then
    echo "Version not found in $PLUGIN_FILE"
    exit 1
  fi
  echo "Found theme version: $VERSION"
fi

# Define the array of files to update
FILES_TO_UPDATE=(
	"package.json"
)

# Get an array of JSON files in the `blocks/*` directory
FILES_TO_UPDATE+=($(find blocks -name "block.json"))

# Print the files to be updated
echo "Files to be updated:"
for FILE in "${FILES_TO_UPDATE[@]}"; do
	echo "- $FILE"
done

echo "----------------------"
echo "Updating version in files..."

# Go through each file and update the version
for FILE in "${FILES_TO_UPDATE[@]}"; do
  if [[ -f "$FILE" ]]; then
    echo "Updating $FILE..."
    sed -i.bak -E "s/(\"version\"[[:space:]]*:[[:space:]]*\")[^\"]+\"/\1$VERSION\"/" "$FILE"
    rm "$FILE.bak"
  else
    echo "File $FILE not found, skipping."
  fi
done

echo "All version files updated to version: $VERSION."
echo "----------------------"
echo "Updating pot file..."

# Update the pot file via WP-CLI
# if command -v wp &> /dev/null; then
# 	wp i18n make-pot . languages/$POT_FILE --exclude="build,vendor,node_modules"
# 	echo "Pot file updated."
# else
# 	echo "WP-CLI is not installed. Please install it to update the pot file."
# fi