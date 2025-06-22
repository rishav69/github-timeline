#!/bin/bash
# This script sets up a CRON job to run cron.php every 5 minutes

# Absolute path to your PHP binary
PHP_BIN=$(which php)

# Full path to cron.php
CRON_PATH="$(cd "$(dirname "$0")" && pwd)/cron.php"

# Add a cron job: every 5 minutes
( crontab -l 2>/dev/null; echo "*/5 * * * * $PHP_BIN $CRON_PATH >> /tmp/gh-timeline.log 2>&1" ) | crontab -
