<template>
  <div class="landing-container">
    <!-- Header dengan Logo -->
    <header class="header">
      <div class="logo-small">
        <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" />
        <span>SITARA</span>
      </div>
    </header>

    <!-- Hero Section dengan 2 Tombol Akses -->
    <section class="hero">
      <div class="hero-content">
        <img src="@/assets/SITARA_RM_BG.png" alt="SITARA Logo" class="hero-logo" />
        <h1 class="hero-title">SITARA</h1>
        <p class="hero-subtitle">Sistem Informasi Terpadu</p>
        <p class="hero-subtitle-small">Assessment dan Rekam Anak</p>
        
        <div class="hero-description">
          <h2>Selamat Datang di Klinik SITARA</h2>
          <p>Pusat layanan terpadu untuk assessment dan tumbuh kembang anak dengan tim profesional berpengalaman.</p>
        </div>

        <!-- 2 TOMBOL UTAMA -->
        <div class="access-buttons">
          <router-link to="/login" class="btn-access btn-staff">
            <span class="btn-icon">👨‍⚕️</span>
            <div class="btn-content">
              <strong>Portal Staff</strong>
              <small>Masuk untuk Admin, Dokter, Terapis</small>
            </div>
          </router-link>
          
          <router-link to="/pasien/login" class="btn-access btn-patient">
            <span class="btn-icon">👶</span>
            <div class="btn-content">
              <strong>Portal Pasien</strong>
              <small>Login atau Daftar sebagai Pasien</small>
            </div>
          </router-link>
        </div>
      </div>

      <!-- 2 Tombol Portal -->
      <div class="portal-selection">
        <div class="portal-card staff-portal" @click="goToStaffLogin">
          <div class="portal-icon">👨‍⚕️</div>
          <h3>Portal Staff</h3>
          <p>Masuk untuk Admin, Dokter, dan Terapis</p>
          <button class="btn-portal btn-staff">Masuk sebagai Staff →</button>
        </div>

        <div class="portal-divider">
          <span>atau</span>
        </div>

        <div class="portal-card patient-portal" @click="goToPatientLogin">
          <div class="portal-icon">👶</div>
          <h3>Portal Pasien</h3>
          <p>Login atau Daftar sebagai Pasien</p>
          <button class="btn-portal btn-patient">Masuk sebagai Pasien →</button>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer-content">
        <div class="footer-section">
          <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="footer-logo" />
          <p>Sistem Informasi Terpadu Assessment dan Rekam Anak</p>
        </div>
        <div class="footer-section">
          <h4>Quick Links</h4>
          <ul>
            <li><router-link to="/login">Portal Staff</router-link></li>
            <li><router-link to="/pasien/login">Portal Pasien</router-link></li>
            <li><a href="#layanan">Layanan</a></li>
            <li><a href="#kontak">Kontak</a></li>
          </ul>
        </div>
        <div class="footer-section">
          <h4>Layanan</h4>
          <ul>
            <li>Terapi Wicara</li>
            <li>Terapi Okupasi</li>
            <li>Assessment Medis</li>
            <li>Monitoring Tumbuh Kembang</li>
          </ul>
        </div>
        <div class="info-card">
          <div class="info-icon">📞</div>
          <h4>Kontak Kami</h4>
          <p>📍 Jl. Kesehatan No. 123</p>
          <p>📱 (021) 1234-5678</p>
          <p>✉️ info@sitara-klinik.com</p>
        </div>
      </div>
    </footer>

    <!-- Footer -->
    <footer class="footer">
      <p>&copy; 2026 SITARA - Klinik Tumbuh Kembang Anak. All rights reserved.</p>
    </footer>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const goToStaffLogin = () => {
  router.push('/login')
}

const goToPatientLogin = () => {
  router.push('/pasien/login')
}
</script>

<style scoped>
.landing-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  flex-direction: column;
}

.header {
  background: rgba(255, 255, 255, 0.95);
  padding: 1.5rem 2rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  max-width: 1200px;
  margin: 0 auto;
}

.logo {
  width: 60px;
  height: 60px;
}

.brand-name {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1e40af;
  margin: 0;
}

.brand-tagline {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.main-content {
  flex: 1;
  padding: 3rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

.welcome-section {
  text-align: center;
  margin-bottom: 3rem;
}

.welcome-title {
  font-size: 2.5rem;
  color: white;
  margin-bottom: 0.5rem;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.welcome-subtitle {
  font-size: 1.125rem;
  color: rgba(255, 255, 255, 0.9);
}

.portal-selection {
  display: flex;
  gap: 2rem;
  justify-content: center;
  align-items: stretch;
  margin-bottom: 3rem;
}

.portal-card {
  background: white;
  border-radius: 1rem;
  padding: 2.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  min-width: 350px;
}

.portal-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.staff-portal:hover {
  border: 3px solid #3b82f6;
}

.patient-portal:hover {
  border: 3px solid #10b981;
}

.portal-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.portal-card h3 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  color: #1e293b;
}

.portal-card p {
  color: #64748b;
  margin-bottom: 1.5rem;
}

.btn-portal {
  padding: 0.875rem 2rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-staff {
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  color: white;
}

.btn-staff:hover {
  background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
  transform: scale(1.05);
}

.btn-patient {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.btn-patient:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: scale(1.05);
}

.portal-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
  font-weight: bold;
}

.portal-divider::before,
.portal-divider::after {
  content: '';
  flex: 1;
  border-bottom: 2px solid rgba(255, 255, 255, 0.5);
  margin: 0 1rem;
}

.info-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-top: 3rem;
}

.info-card {
  background: rgba(255, 255, 255, 0.95);
  padding: 2rem;
  border-radius: 1rem;
  text-align: center;
}

.info-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.info-card h4 {
  color: #1e293b;
  margin-bottom: 1rem;
}

.info-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.info-card li {
  color: #64748b;
  padding: 0.25rem 0;
}

.info-card p {
  color: #64748b;
  margin: 0.25rem 0;
}

.footer {
  background: rgba(0, 0, 0, 0.2);
  color: white;
  text-align: center;
  padding: 1.5rem;
}

@media (max-width: 768px) {
  .portal-selection {
    flex-direction: column;
  }
  
  .portal-card {
    min-width: 100%;
  }
  
  .portal-divider {
    margin: 1rem 0;
  }
  
  .welcome-title {
    font-size: 2rem;
  }
  
  .info-section {
    grid-template-columns: 1fr;
  }
}
</style>
