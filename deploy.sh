#!/bin/bash

# ============================================
# PetsList Live Deployment Script
# Usage: ./deploy.sh
# ============================================

FTP_HOST="computerkingdom.co"
FTP_USER="compftp@computerkingdom.co"
FTP_PASS="nYrA9r]v&0x=*BAU"

LOCAL_THEME_DIR="$(pwd)/wp-content/themes"

echo "🚀 Starting deployment to $FTP_HOST..."
echo ""

# Check if lftp is installed
if ! command -v lftp &> /dev/null; then
    echo "❌ lftp not installed. Installing via Homebrew..."
    brew install lftp
fi

echo "📦 Syncing theme: petslist-raushan..."
lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<EOF
set ssl:verify-certificate no
set ftp:passive-mode yes
mirror --reverse --delete --verbose \
  "$LOCAL_THEME_DIR/petslist-raushan" \
  "wp-content/themes/petslist-raushan"
bye
EOF

echo ""
echo "📦 Uploading push_to_live.sql and import-db.php..."
lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<EOF
set ssl:verify-certificate no
set ftp:passive-mode yes
put -O . push_to_live.sql
put -O . import-db.php
bye
EOF

echo ""
echo "✅ Files transferred successfully!"
echo "👉 Visit this link in your browser to import the database live:"
echo "👉 https://computerkingdom.co/dog/import-db.php?key=raushan2024"
echo ""
