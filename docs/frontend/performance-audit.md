# Performance Audit Report - SITARA

## Bundle Size
- Total Bundle Size: ~600 KB (uncompressed)
- Largest Chunk: `DashboardView` (~215 KB)
- Key Libraries: `axios` (~42 KB), `vue` (embedded in index)

## Load Time (Estimated Development)
- First Contentful Paint: < 1s
- Largest Contentful Paint: < 1.5s
- Time to Interactive: < 2s

## Optimization Opportunities
1. [x] Implement code splitting for all routes. (Found `QueueView` issue)
2. [ ] Reduce bundle size by lazy loading `DashboardView`.
3. [ ] Compress `SITARA_RM_BG.png` (currently ~133 KB).
4. [ ] Configure `vite.config.js` for long-term caching.

## Recommendations
- Priority 1: Fix `QueueView` static import in `router/index.js` to enable lazy loading.
- Priority 2: Optimize large images to WebP format.
- Priority 3: Add service worker for PWA capabilities if offline access is required.
