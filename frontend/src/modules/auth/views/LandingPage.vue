<template>
  <div class="landing-page">
    <!-- Navigation -->
    <nav class="navbar">
      <div class="container">
        <div class="nav-brand">
          <img src="@/assets/SITARA_logo.png" alt="SITARA" class="logo" />
          <div>
            <h1 class="brand-title">SITARA</h1>
            <p class="brand-subtitle">Sistem Informasi Terpadu Assessment dan Rekam Anak</p>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
      <div class="container">
        <div class="hero-content">
          <h2 class="hero-title">Selamat Datang di Klinik SITARA</h2>
          <p class="hero-subtitle">Pusat layanan terpadu untuk assessment dan tumbuh kembang anak dengan tim profesional berpengalaman</p>
          
          <!-- Portal Selection -->
          <div class="portal-selection">
            <div class="portal-card" @click="goToStaff">
              <div class="portal-icon staff">
                <span>👨‍⚕️</span>
              </div>
              <h3>Portal Staff</h3>
              <p>Masuk untuk Admin, Dokter, dan Terapis</p>
              <button class="btn btn-staff">Masuk Sekarang →</button>
            </div>

            <div class="portal-card" @click="goToPatient">
              <div class="portal-icon patient">
                <span>👶</span>
              </div>
              <h3>Portal Pasien</h3>
              <p>Login atau Daftar sebagai Pasien</p>
              <button class="btn btn-patient">Masuk Sekarang →</button>
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
            <div class="service-icon">🧠</div>
            <h3>Terapi Wicara</h3>
            <p>Terapi untuk mengatasi gangguan bicara dan bahasa pada anak</p>
          </div>
          <div class="service-card">
            <div class="service-icon">👶</div>
            <h3>Terapi Okupasi</h3>
            <p>Meningkatkan kemandirian anak dalam aktivitas sehari-hari</p>
          </div>
          <div class="service-card">
            <div class="service-icon">📋</div>
            <h3>Assessment Medis</h3>
            <p>Evaluasi menyeluruh perkembangan anak oleh dokter spesialis</p>
          </div>
          <div class="service-card">
            <div class="service-icon">📈</div>
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
            <span class="contact-icon">📍</span>
            <div>
              <h4>Alamat</h4>
              <p>Jl. Kesehatan No. 123<br/>Jakarta, Indonesia</p>
            </div>
          </div>
          <div class="contact-item">
            <span class="contact-icon">📞</span>
            <div>
              <h4>Telepon</h4>
              <p>(021) 1234-5678</p>
            </div>
          </div>
          <div class="contact-item">
            <span class="contact-icon">✉️</span>
            <div>
              <h4>Email</h4>
              <p>info@sitara-klinik.com</p>
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
/* Base Styles */
.landing-page {
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Navbar */
.navbar {
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 1.5rem 0;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo {
  width: 60px;
  height: 60px;
}

.brand-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1e40af;
  margin: 0;
}

.brand-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

/* Hero Section */
.hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 5rem 0;
}

.hero-content {
  text-align: center;
}

.hero-title {
  font-size: 3rem;
  color: white;
  margin-bottom: 1rem;
  font-weight: bold;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.hero-subtitle {
  font-size: 1.25rem;
  color: rgba(255, 255, 255, 0.95);
  margin-bottom: 3rem;
  max-width: 700px;
  margin-left: auto;
  margin-right: auto;
}

/* Portal Selection */
.portal-selection {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-top: 3rem;
}

.portal-card {
  background: white;
  border-radius: 1rem;
  padding: 2.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.portal-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.portal-icon {
  width: 100px;
  height: 100px;
  margin: 0 auto 1.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
}

.portal-icon.staff {
  background: linear-gradient(135deg, #3b82f6, #1e40af);
}

.portal-icon.patient {
  background: linear-gradient(135deg, #10b981, #059669);
}

.portal-card h3 {
  font-size: 1.75rem;
  color: #1e293b;
  margin-bottom: 0.75rem;
}

.portal-card p {
  color: #64748b;
  margin-bottom: 1.5rem;
}

.btn {
  padding: 1rem 2rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-staff {
  background: linear-gradient(135deg, #3b82f6, #1e40af);
  color: white;
}

.btn-staff:hover {
  background: linear-gradient(135deg, #1e40af, #1e3a8a);
  transform: scale(1.05);
}

.btn-patient {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-patient:hover {
  background: linear-gradient(135deg, #059669, #047857);
  transform: scale(1.05);
}

/* Services Section */
.services {
  padding: 5rem 0;
  background: #f8fafc;
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  color: #1e293b;
  margin-bottom: 3rem;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.service-card {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  text-align: center;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  transition: all 0.3s;
}

.service-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.service-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.service-card h3 {
  color: #1e293b;
  margin-bottom: 0.75rem;
}

.service-card p {
  color: #64748b;
  line-height: 1.6;
}

/* Contact Section */
.contact {
  padding: 5rem 0;
  background: white;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.contact-item {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.contact-icon {
  font-size: 2rem;
}

.contact-item h4 {
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.contact-item p {
  color: #64748b;
  line-height: 1.6;
}

/* Footer */
.footer {
  background: #1e293b;
  color: white;
  text-align: center;
  padding: 2rem 0;
}

/* Responsive */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
  
  .portal-selection {
    grid-template-columns: 1fr;
  }
  
  .services-grid,
  .contact-grid {
    grid-template-columns: 1fr;
  }
}
</style>
