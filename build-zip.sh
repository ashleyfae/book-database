#!/bin/bash

set -e

PLUGIN_DIR="book-database"
ZIP_NAME="book-database.zip"
BUILD_DIR="build"

mkdir -p "$BUILD_DIR/$PLUGIN_DIR"

git archive --prefix "$PLUGIN_DIR/" -o "$BUILD_DIR/$ZIP_NAME" HEAD

cp -r vendor/ "$BUILD_DIR/$PLUGIN_DIR/vendor"
cd "$BUILD_DIR" && zip -ur "$ZIP_NAME" "$PLUGIN_DIR/" > /dev/null

echo "Built: $BUILD_DIR/$ZIP_NAME"
