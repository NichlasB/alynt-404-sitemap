#!/bin/bash
# Deploy script for alynt-404-sitemap
set -e

# Use your SSH config alias - do NOT hardcode username here
# Configure in ~/.ssh/config: Host, HostName, User, IdentityFile
REMOTE_HOST="your-ssh-alias"
REMOTE_PATH="/var/www/your-site/htdocs/wp-content/plugins/alynt-404-sitemap"

echo "Deploying alynt-404-sitemap to staging..."
rsync -avz --delete \
  --exclude='.git' \
  --exclude='.github' \
  --exclude='docs' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='tests' \
  --exclude='coverage' \
  --exclude='assets/src' \
  --exclude='.DS_Store' \
  --exclude='.editorconfig' \
  --exclude='.gitignore' \
  --exclude='.phpcs.xml' \
  --exclude='.phpunit.result.cache' \
  --exclude='.env' \
  --exclude='composer.phar' \
  --exclude='composer.json' \
  --exclude='composer.lock' \
  --exclude='package.json' \
  --exclude='package-lock.json' \
  --exclude='phpunit.xml' \
  --exclude='scripts/' \
  --exclude='deploy.sh' \
  --exclude='pre-release-model-recommendations.tmp.txt' \
  --exclude='*.map' \
  ./ "${REMOTE_HOST}:${REMOTE_PATH}/"
echo "Deployment complete!"
echo "Remote path: ${REMOTE_PATH}"
