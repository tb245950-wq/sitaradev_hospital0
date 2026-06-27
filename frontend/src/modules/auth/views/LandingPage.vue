<template>
  <div class="landing-page">
    <!-- Navigation -->
    <nav class="navbar">
      <div class="container nav-container">
        <div class="nav-brand">
          <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
          <div>
            <h1 class="brand-title">SITARA</h1>
            <p class="brand-subtitle">Sistem Informasi Terpadu Assessment dan Rekam Anak</p>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-bg-accent-1"></div>
      <div class="hero-bg-accent-2"></div>
      <div class="container hero-container">
        <div class="hero-content">
          <h2 class="hero-title">Selamat Datang di Klinik SITARA</h2>
          <p class="hero-subtitle">Pusat layanan terpadu untuk assessment dan tumbuh kembang anak dengan tim profesional berpengalaman</p>
          
          <!-- Portal Selection -->
          <div class="portal-selection">
            <div class="portal-card staff" @click="goToStaff">
              <div class="portal-icon-wrapper">
                <svg class="portal-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
              </div>
              <h3>Portal Staff</h3>
              <p>Masuk untuk Admin, Dokter, dan Terapis</p>
              <button class="btn btn-staff">Masuk Sekarang <span class="arrow-right">→</span></button>
            </div>

            <div class="portal-card patient" @click="goToPatient">
              <div class="portal-icon-wrapper">
                <svg class="portal-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
              </div>
              <h3>Portal Pasien</h3>
              <p>Login atau Daftar sebagai Pasien</p>
              <button class="btn btn-patient">Masuk Sekarang <span class="arrow-right">→</span></button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section class="services">
      <div class="container">
        <h2 class="section-title">Layanan Kami</h2>
        <div class="services-grid">
          <div class="service-card">
            <div class="service-icon-container speech">
              <svg class="service-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
            </div>
            <h3>Terapi Wicara</h3>
            <p>Terapi untuk mengatasi gangguan bicara dan bahasa pada anak</p>
          </div>
          <div class="service-card">
            <div class="service-icon-container occupational">
              <svg class="service-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
              </svg>
            </div>
            <h3>Terapi Okupasi</h3>
            <p>Meningkatkan kemandirian anak dalam aktivitas sehari-hari</p>
          </div>
          <div class="service-card">
            <div class="service-icon-container assessment">
              <svg class="service-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
              </svg>
            </div>
            <h3>Assessment Medis</h3>
            <p>Evaluasi menyeluruh perkembangan anak oleh dokter spesialis</p>
          </div>
          <div class="service-card">
            <div class="service-icon-container monitoring">
              <svg class="service-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
                <path d="M3 3v18h18"></path>
              </svg>
            </div>
            <h3>Monitoring Progress</h3>
            <p>Pemantauan berkala perkembangan anak selama terapi</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="contact">
      <div class="container">
        <h2 class="section-title">Kontak Kami</h2>
        <div class="contact-grid">
          <div class="contact-item">
            <div class="contact-icon-wrapper">
              <svg class="contact-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
            </div>
            <div>
              <h4>Alamat</h4>
              <p>Jl. Kesehatan No. 123<br/>Jakarta, Indonesia</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon-wrapper">
              <svg class="contact-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
            </div>
            <div>
              <h4>Telepon</h4>
              <p>(021) 1234-5678</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon-wrapper">
              <svg class="contact-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
            </div>
            <div>
              <h4>Email</h4>
              <p>Dev@sitara-klinik.com</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <p>&copy; 2026 SITARA - Klinik Tumbuh Kembang Anak. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const goToStaff = () => {
  router.push('/login')
}

const goToPatient = () => {
  router.push('/pasien/login')
}
</script>

<style scoped>
/* Styling System */
:root {
  --color-primary: #1e40af;
  --color-primary-light: #3b82f6;
  --color-secondary: #0f766e;
  --color-secondary-light: #14b8a6;
  --color-text-dark: #1e293b;
  --color-text-muted: #64748b;
  --color-bg-light: #f8fafc;
  --color-bg-white: #ffffff;
}

.landing-page {
  min-height: 100vh;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #1e293b;
  background-color: #f8fafc;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Navbar */
.navbar {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid #e2e8f0;
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.nav-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.logo {
  width: 52px;
  height: 52px;
  object-fit: contain;
}

.brand-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e40af;
  margin: 0;
  letter-spacing: -0.025em;
  line-height: 1.1;
}

.brand-subtitle {
  font-size: 0.825rem;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}

/* Hero Section */
.hero {
  position: relative;
  background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
  padding: 6rem 0;
  overflow: hidden;
}

.hero-bg-accent-1 {
  position: absolute;
  top: -10%;
  right: -5%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
  z-index: 0;
  pointer-events: none;
}

.hero-bg-accent-2 {
  position: absolute;
  bottom: -10%;
  left: -5%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(20, 184, 166, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
  z-index: 0;
  pointer-events: none;
}

.hero-container {
  position: relative;
  z-index: 1;
}

.hero-content {
  text-align: center;
  max-width: 900px;
  margin: 0 auto;
}

.hero-title {
  font-size: 3.5rem;
  color: #1e3a8a;
  margin-bottom: 1.25rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.15;
}

.hero-subtitle {
  font-size: 1.25rem;
  color: #475569;
  margin-bottom: 4rem;
  max-width: 720px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

/* Portal Selection */
.portal-selection {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 2.5rem;
  max-width: 800px;
  margin: 0 auto;
}

.portal-card {
  background: white;
  border-radius: 1.25rem;
  padding: 3rem 2.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  border: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
}

.portal-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.portal-card.staff:hover {
  border-color: #3b82f6;
}

.portal-card.patient:hover {
  border-color: #14b8a6;
}

.portal-icon-wrapper {
  width: 84px;
  height: 84px;
  margin-bottom: 2rem;
  border-radius: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.3s;
}

.portal-card:hover .portal-icon-wrapper {
  transform: scale(1.1);
}

.portal-icon {
  width: 40px;
  height: 40px;
}

.staff .portal-icon-wrapper {
  background: #eff6ff;
  color: #1d4ed8;
}

.patient .portal-icon-wrapper {
  background: #f0fdfa;
  color: #0f766e;
}

.portal-card h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.75rem;
}

.portal-card p {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0 0 2rem;
  line-height: 1.5;
}

.btn {
  padding: 0.875rem 2rem;
  border: none;
  border-radius: 0.75rem;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-staff {
  background: #1e40af;
  color: white;
  box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
}

.btn-staff:hover {
  background: #1d4ed8;
  transform: scale(1.02);
}

.btn-patient {
  background: #0f766e;
  color: white;
  box-shadow: 0 4px 12px rgba(15, 118, 110, 0.2);
}

.btn-patient:hover {
  background: #14b8a6;
  transform: scale(1.02);
}

.arrow-right {
  transition: transform 0.2s;
}

.btn:hover .arrow-right {
  transform: translateX(4px);
}

/* Services Section */
.services {
  padding: 6rem 0;
  background: #ffffff;
}

.section-title {
  text-align: center;
  font-size: 2.25rem;
  color: #0f172a;
  font-weight: 800;
  margin-bottom: 4rem;
  letter-spacing: -0.025em;
  position: relative;
}

.section-title::after {
  content: '';
  position: absolute;
  bottom: -1rem;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 4px;
  background: #1e40af;
  border-radius: 2px;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 2rem;
}

.service-card {
  background: #f8fafc;
  padding: 2.5rem 2rem;
  border-radius: 1.25rem;
  text-align: center;
  border: 1px solid #f1f5f9;
  transition: all 0.3s;
}

.service-card:hover {
  transform: translateY(-6px);
  background: white;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
  border-color: #e2e8f0;
}

.service-icon-container {
  width: 64px;
  height: 64px;
  margin: 0 auto 1.5rem;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.service-icon {
  width: 28px;
  height: 28px;
}

.service-icon-container.speech {
  background: #eef2ff;
  color: #4f46e5;
}

.service-icon-container.occupational {
  background: #fdf2f8;
  color: #db2777;
}

.service-icon-container.assessment {
  background: #f0fdf4;
  color: #16a34a;
}

.service-icon-container.monitoring {
  background: #fffbeb;
  color: #d97706;
}

.service-card h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.75rem;
}

.service-card p {
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.6;
  margin: 0;
}

/* Contact Section */
.contact {
  padding: 6rem 0;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 3rem;
}

.contact-item {
  display: flex;
  gap: 1.25rem;
  align-items: flex-start;
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
  transition: all 0.3s;
}

.contact-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.04);
}

.contact-icon-wrapper {
  width: 48px;
  height: 48px;
  background: #eff6ff;
  color: #2563eb;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.contact-icon {
  width: 24px;
  height: 24px;
}

.contact-item h4 {
  color: #0f172a;
  font-weight: 700;
  font-size: 1.1rem;
  margin: 0 0 0.5rem;
}

.contact-item p {
  color: #64748b;
  font-size: 0.95rem;
  line-height: 1.5;
  margin: 0;
}

/* Footer */
.footer {
  background: #0f172a;
  color: #94a3b8;
  text-align: center;
  padding: 2.5rem 0;
  border-top: 1px solid #1e293b;
}

.footer p {
  margin: 0;
  font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .hero-title {
    font-size: 3rem;
  }
}

@media (max-width: 768px) {
  .hero {
    padding: 4rem 0;
  }
  
  .hero-title {
    font-size: 2.25rem;
  }
  
  .hero-subtitle {
    font-size: 1.1rem;
    margin-bottom: 3rem;
  }
  
  .portal-selection {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .portal-card {
    padding: 2.5rem 1.5rem;
  }
  
  .services-grid,
  .contact-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
}
</style>
