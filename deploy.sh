#!/bin/bash

# ============================================
# PetsList Live Deployment Script
# Usage: ./deploy.sh
# ============================================

FTP_HOST="computerkingdom.co"
FTP_USER="compftp@computerkingdom.co"
FTP_PASS="nYrA9r]v&0x=*BAU"
REMOTE_THEME_DIR="/wp-content/themes"

LOCAL_THEME_DIR="$(pwd)/wp-content/themes"

echo "🚀 Starting deployment to $FTP_HOST..."
echo ""

# Check if lftp is installed
if ! command -v lftp &> /dev/null; then
    echo "❌ lftp not installed. Installing via Homebrew..."
    brew install lftp
fi

echo "📦 Syncing theme: petslist..."
lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<EOF
set ssl:verify-certificate no
set ftp:passive-mode yes
mirror --reverse --delete --verbose \
  "$LOCAL_THEME_DIR/petslist" \
  "$REMOTE_THEME_DIR/petslist"
bye
EOF

echo ""
echo "📦 Syncing theme: petslist-raushan..."
lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<EOF
set ssl:verify-certificate no
set ftp:passive-mode yes
mirror --reverse --delete --verbose \
  "$LOCAL_THEME_DIR/petslist-raushan" \
  "$REMOTE_THEME_DIR/petslist-raushan"
bye
EOF

echo ""
echo "✅ Deployment complete! Visit https://computerkingdom.co/dog/ to verify."
