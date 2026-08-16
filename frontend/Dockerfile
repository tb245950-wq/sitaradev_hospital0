# =============================================================
# frontend/Dockerfile — Frontend Vue.js (Production)
# Multi-stage: build dengan Node, serve dengan Nginx
# =============================================================

# --- Stage 1: Build ---
FROM node:20-alpine AS builder

WORKDIR /app

# Copy package files dulu untuk cache layer
COPY package*.json ./
RUN npm ci

# Copy semua source dan build
COPY . .
RUN npm run build

# --- Stage 2: Serve ---
FROM nginx:alpine

# Copy hasil build ke Nginx
COPY --from=builder /app/dist /usr/share/nginx/html

# Config Nginx untuk SPA (semua route fallback ke index.html)
RUN echo 'server { \
    listen 80; \
    root /usr/share/nginx/html; \
    index index.html; \
    \
    # Gzip compression \
    gzip on; \
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript; \
    \
    # Cache static assets \
    location /assets/ { \
        expires 1y; \
        add_header Cache-Control "public, immutable"; \
    } \
    \
    # SPA fallback \
    location / { \
        try_files $uri $uri/ /index.html; \
    } \
}' > /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
