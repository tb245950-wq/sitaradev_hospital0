#!/bin/bash
# =============================================================
# git-push.sh — Commit per file dengan deskripsi ke GitHub
# Jalankan: bash git-push.sh
# =============================================================

set -e  # Berhenti jika ada error

echo "🚀 Mulai commit per file..."
echo ""

# --- Hapus file sampah ---
git rm --cached database/sitaradev_hospital.sql 2>/dev/null || true
git rm --force assessment_test.json assessment_test2.json therapy_test.json monitoring_test.json sitaradev_backup.sql 2>/dev/null || true
git rm --force database/migrations/2026_06_17_000003_restructure_database_schema.php.skip 2>/dev/null || true
git rm --force frontend/public/cleanup-storage.html frontend/public/debug.html frontend/public/test-super-admin.html 2>/dev/null || true

git add -u assessment_test.json assessment_test2.json therapy_test.json monitoring_test.json sitaradev_hospital.sql sitaradev_backup.sql 2>/dev/null || true
git commit -m "chore: hapus file test JSON dan backup SQL yang tidak diperlukan" 2>/dev/null || true

git add -u database/migrations/2026_06_17_000003_restructure_database_schema.php.skip 2>/dev/null || true
git commit -m "chore: hapus migration .skip yang sudah tidak digunakan" 2>/dev/null || true

git add -u frontend/public/cleanup-storage.html frontend/public/debug.html frontend/public/test-super-admin.html 2>/dev/null || true
git commit -m "chore: hapus file HTML debug dari frontend/public" 2>/dev/null || true

# --- Security: untrack .env.docker ---
git add .env.docker
git commit -m "chore: untrack .env.docker dari git (dipindah ke .gitignore)"

# --- .gitignore ---
git add .gitignore
git commit -m "chore: update .gitignore — tambah pola .env.*, docker override, SSL keys, storage"

# --- frontend/.gitignore ---
git add frontend/.gitignore
git commit -m "chore: update frontend/.gitignore — tambah .env.*, .wrangler/"

# --- .env.example ---
git add .env.example
git commit -m "chore: update .env.example — ganti default DB dari sqlite ke postgresql"

# --- config/database.php ---
git add config/database.php
git commit -m "refactor: bersihkan config/database.php — hapus koneksi mysql/mariadb/sqlsrv, gunakan pgsql"

# --- Dockerfile (Render.com) ---
git add Dockerfile
git commit -m "fix: Dockerfile Render — ganti php:8.3-cli ke apache, fix config:cache di CMD bukan build"

# --- Dockerfile.backend (VPS) ---
git add Dockerfile.backend
git commit -m "fix: Dockerfile.backend — hapus Node.js redundan, murni php-fpm untuk VPS"

# --- frontend/Dockerfile ---
git add frontend/Dockerfile
git commit -m "fix: frontend/Dockerfile — ganti dev server ke multi-stage build dengan Nginx production"

# --- docker-compose.yml (baru) ---
git add docker-compose.yml
git commit -m "feat: tambah docker-compose.yml — 4 service: nginx, backend, frontend, postgresql"

# --- nginx.conf (baru) ---
git add nginx.conf
git commit -m "feat: tambah nginx.conf — gateway /api ke backend, / ke frontend SPA"

# --- railway.json ---
git add railway.json
git commit -m "fix: railway.json — ganti builder Nixpacks ke DOCKERFILE, fix startCommand"

# --- .gitkeep storage folders ---
git add storage/app/public/.gitkeep storage/app/exports/.gitkeep storage/app/backups/.gitkeep storage/app/private/.gitkeep 2>/dev/null || true
git commit -m "chore: tambah .gitkeep di folder storage agar struktur folder terjaga di git" 2>/dev/null || true

echo ""
echo "✅ Semua commit selesai!"
echo ""

# --- Push ke GitHub ---
echo "📤 Push ke GitHub..."
git push origin main

echo ""
echo "🎉 Done! Semua perubahan sudah di GitHub."
