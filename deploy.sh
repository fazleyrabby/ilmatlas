#!/bin/bash
set -e

echo "=== Committing and pushing local changes ==="
git add .
git commit -m "Deploy automatic update" || true
git push origin main || true

echo "=== Deploying to signalstack VPS ==="
ssh signalstack << 'EOF'
  set -e
  mkdir -p ~/apps
  
  if [ ! -d "/home/fazley/apps/edubase" ]; then
    echo "Cloning repository on VPS..."
    git clone https://github.com/fazleyrabby/edubase.git /home/fazley/apps/edubase
  fi

  cd /home/fazley/apps/edubase
  echo "Pulling latest changes from main branch..."
  git pull origin main

  # Set up .env if it doesn't exist
  if [ ! -f ".env" ]; then
    echo "Setting up production .env from template..."
    cp .env.prod .env
  fi

  echo "Starting docker services..."
  docker compose -f docker-compose.prod.yml down || true
  docker compose -f docker-compose.prod.yml up -d --build

  echo "Waiting 10 seconds for database to start up and become healthy..."
  sleep 10

  echo "Running post-deploy tasks inside container..."
  docker exec edubase_app php artisan key:generate --force || true
  docker exec edubase_app php artisan migrate:fresh --seed --force

  echo "Restarting cloudflared tunnel to apply hostname mapping..."
  sudo systemctl restart cloudflared || true

  echo "=== VPS Deployment Completed Successfully! ==="
EOF
