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

echo "Starting required Docker containers..."
docker start user_code_node 2>/dev/null || docker run -d --name user_code_node node:20 sleep infinity
docker start user_code_ts 2>/dev/null || docker run -d --name user_code_ts node:20 sleep infinity
docker start user_code_python 2>/dev/null || docker run -d --name user_code_python python sleep infinity
docker start user_code_php 2>/dev/null || docker run -d --name user_code_php php:8.1-cli sleep infinity
docker start user_code_gcc 2>/dev/null || docker run -d --name user_code_gcc gcc:latest sleep infinity
docker start user_code_dotnet 2>/dev/null || docker run -d --name user_code_dotnet mcr.microsoft.com/dotnet/sdk:8.0 sleep infinity
docker start user_code_java 2>/dev/null || docker run -d --name user_code_java openjdk:latest sleep infinity
docker start user_code_go 2>/dev/null || docker run -d --name user_code_go golang:latest sleep infinity

echo "PHP version:"
php -v

echo "Starting local PHP server..."
php -S localhost:8000 -t public