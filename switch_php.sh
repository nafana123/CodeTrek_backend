#!/bin/bash

echo "Unlinking PHP versions..."
brew unlink php@7.4
brew unlink php@8.1

echo "Linking PHP 8.1..."
brew link --force php@8.1

echo "Configuring PATH..."
echo 'export PATH="/opt/homebrew/opt/php@8.1/bin:$PATH"' >> ~/.zshrc
echo 'export PATH="/opt/homebrew/opt/php@8.1/sbin:$PATH"' >> ~/.zshrc

source ~/.zshrc

echo "PHP version:"
php -v

echo "Starting local server..."
php -S localhost:8000 -t public