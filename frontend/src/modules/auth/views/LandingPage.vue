<template>
  <div class="landing-page">
    <!-- Decorative Circles -->
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <!-- Header Navigation -->
    <header class="header">
      <div class="logo-small">
        <img src="../../../assets/SITARA_RM_BG.png" alt="SITARA" />
        <span>SITARA</span>
      </div>
      <nav class="nav-links">
        <a href="#layanan">Layanan</a>
        <a href="#tentang">Tentang</a>
        <a href="#kontak">Kontak</a>
      </nav>
    </header>

    <!-- Hero Section dengan 2 Tombol Akses -->
    <section class="hero">
      <div class="hero-content">
        <img src="../../../assets/SITARA_RM_BG.png" alt="SITARA Logo" class="hero-logo" />
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
    </section>

    <!-- Section: Layanan Kami -->
    <section id="layanan" class="services-section">
      <div class="section-container">
        <h2 class="section-title">Layanan Kami</h2>
        <p class="section-subtitle">Layanan profesional untuk tumbuh kembang optimal anak Anda</p>
        
        <div class="services-grid">
          <div class="service-card">
            <div class="service-icon">🧠</div>
            <h3>Terapi Wicara</h3>
            <p>Terapi untuk mengatasi gangguan bicara dan bahasa pada anak, meningkatkan kemampuan komunikasi verbal dan non-verbal.</p>
          </div>
          
          <div class="service-card">
            <div class="service-icon">👶</div>
            <h3>Terapi Okupasi</h3>
            <p>Terapi untuk meningkatkan kemandirian anak dalam aktivitas sehari-hari, motorik halus, dan keterampilan hidup.</p>
          </div>
          
          <div class="service-card">
            <div class="service-icon">📋</div>
            <h3>Assessment Medis</h3>
            <p>Evaluasi menyeluruh perkembangan anak oleh dokter spesialis untuk diagnosis dan rekomendasi terapi yang tepat.</p>
          </div>
          
          <div class="service-card">
            <div class="service-icon">📈</div>
            <h3>Monitoring Progress</h3>
            <p>Pemantauan berkala perkembangan anak selama menjalani program terapi untuk memastikan kemajuan optimal.</p>
          </div>
          
          <div class="service-card">
            <div class="service-icon">🌱</div>
            <h3>Poli Tumbuh Kembang</h3>
            <p>Layanan khusus untuk pemantauan tumbuh kembang anak dengan pendekatan holistik dan terintegrasi.</p>
          </div>
          
          <div class="service-card">
            <div class="service-icon">🧩</div>
            <h3>Psikologi Anak</h3>
            <p>Layanan psikologis untuk anak dengan fokus pada perkembangan emosi, perilaku, dan sosial.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Section: Tentang Kami -->
    <section id="tentang" class="about-section">
      <div class="section-container">
        <h2 class="section-title">Tentang SITARA</h2>
        <div class="about-content">
          <div class="about-text">
            <p>
              <strong>SITARA (Sistem Informasi Terpadu Assessment dan Rekam Anak)</strong> 
              adalah klinik tumbuh kembang anak yang menyediakan layanan komprehensif untuk 
              mendukung perkembangan optimal anak Anda.
            </p>
            <p>
              Dengan tim profesional yang terdiri dari dokter spesialis, psikolog, terapis wicara, 
              terapis okupasi, dan tenaga medis berpengalaman, kami berkomitmen memberikan 
              layanan terbaik untuk tumbuh kembang anak.
            </p>
            <div class="about-stats">
              <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Pasien Dilayani</div>
              </div>
              <div class="stat-item">
                <div class="stat-number">15+</div>
                <div class="stat-label">Tenaga Profesional</div>
              </div>
              <div class="stat-item">
                <div class="stat-number">10+</div>
                <div class="stat-label">Tahun Pengalaman</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section: Kontak -->
    <section id="kontak" class="contact-section">
      <div class="section-container">
        <h2 class="section-title">Kontak Kami</h2>
        <div class="contact-grid">
          <div class="contact-item">
            <div class="contact-icon">📍</div>
            <div class="contact-info">
              <h4>Alamat</h4>
              <p>Jl. Kesehatan Anak No. 123<br/>Jakarta Selatan, DKI Jakarta 12345</p>
            </div>
          </div>
          
          <div class="contact-item">
            <div class="contact-icon">📞</div>
            <div class="contact-info">
              <h4>Telepon</h4>
              <p>(021) 1234-5678<br/>+62 812-3456-7890 (WhatsApp)</p>
            </div>
          </div>
          
          <div class="contact-item">
            <div class="contact-icon">✉️</div>
            <div class="contact-info">
              <h4>Email</h4>
              <p>info@sitara-klinik.com<br/>admin@sitara-klinik.com</p>
            </div>
          </div>
          
          <div class="contact-item">
            <div class="contact-icon">🕐</div>
            <div class="contact-info">
              <h4>Jam Operasional</h4>
              <p>Senin - Jumat: 08.00 - 17.00<br/>Sabtu: 08.00 - 14.00<br/>Minggu: Tutup</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer-content">
        <div class="footer-section">
          <img src="../../../assets/SITARA_RM_BG.png" alt="SITARA" class="footer-logo" />
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
            <li>Poli Tumbuh Kembang</li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 SITARA - Klinik Tumbuh Kembang Anak. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
// Landing page is static, no logic needed
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
  position: relative;
  overflow-x: hidden;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(59, 130, 246, 0.1);
  z-index: 0;
}

.circle-1 { width: 400px; height: 400px; top: -100px; left: -100px; }
.circle-2 { width: 300px; height: 300px; bottom: -50px; right: -50px; }

/* Header */
.header {
  position: sticky;
  top: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  z-index: 100;
}

.logo-small {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo-small img { width: 40px; height: 40px; }
.logo-small span { font-size: 1.25rem; font-weight: 700; color: #1e40af; }

.nav-links { display: flex; gap: 2rem; }
.nav-links a {
  color: #475569;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}
.nav-links a:hover { color: #1e40af; }

/* Hero Section */
.hero {
  position: relative;
  z-index: 1;
  padding: 4rem 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
}

.hero-content {
  text-align: center;
  max-width: 900px;
}

.hero-logo {
  width: 150px;
  height: 150px;
  margin-bottom: 1.5rem;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  color: #1e40af;
  letter-spacing: 0.1em;
  margin: 0.5rem 0;
}

.hero-subtitle {
  font-size: 1.125rem;
  color: #64748b;
  margin: 0.25rem 0;
  font-weight: 500;
}

.hero-subtitle-small {
  font-size: 0.95rem;
  color: #94a3b8;
  margin-bottom: 2rem;
}

.hero-description {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 2.5rem;
}

.hero-description h2 {
  font-size: 1.75rem;
  color: #1e293b;
  margin-bottom: 0.75rem;
}

.hero-description p {
  color: #64748b;
  line-height: 1.6;
  font-size: 1rem;
}

/* 2 Tombol Akses Utama */
.access-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  max-width: 700px;
  margin: 0 auto;
}

.btn-access {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem 2rem;
  border-radius: 1rem;
  text-decoration: none;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn-access:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.btn-content {
  text-align: left;
}

.btn-content strong {
  display: block;
  font-size: 1.125rem;
  margin-bottom: 0.25rem;
}

.btn-content small {
  font-size: 0.8rem;
  opacity: 0.9;
}

.btn-staff {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
  color: white;
}

.btn-patient {
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
  color: white;
}

/* Section Umum */
.section-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 4rem 2rem;
  position: relative;
  z-index: 1;
}

.section-title {
  font-size: 2.25rem;
  font-weight: 700;
  color: #1e293b;
  text-align: center;
  margin-bottom: 0.75rem;
}

.section-subtitle {
  text-align: center;
  color: #64748b;
  margin-bottom: 3rem;
  font-size: 1.05rem;
}

/* Services */
.services-section {
  background: white;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.service-card {
  background: #f8fafc;
  padding: 2rem;
  border-radius: 1rem;
  text-align: center;
  transition: all 0.3s;
  border: 2px solid transparent;
}

.service-card:hover {
  transform: translateY(-4px);
  border-color: #3b82f6;
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.1);
}

.service-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.service-card h3 {
  color: #1e293b;
  margin-bottom: 0.75rem;
  font-size: 1.25rem;
}

.service-card p {
  color: #64748b;
  line-height: 1.6;
  font-size: 0.95rem;
}

/* About */
.about-section {
  background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
}

.about-text p {
  color: #475569;
  line-height: 1.8;
  margin-bottom: 1rem;
  font-size: 1rem;
}

.about-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e2e8f0;
}

.stat-item { text-align: center; }
.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1e40af;
  margin-bottom: 0.25rem;
}
.stat-label {
  color: #64748b;
  font-size: 0.9rem;
}

/* Contact */
.contact-section {
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
  padding: 1.5rem;
  background: #f8fafc;
  border-radius: 1rem;
}

.contact-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.contact-info h4 {
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.contact-info p {
  color: #64748b;
  line-height: 1.6;
  font-size: 0.9rem;
}

/* Footer */
.footer {
  background: #1e293b;
  color: white;
  padding: 3rem 2rem 1rem;
  position: relative;
  z-index: 1;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 3rem;
  margin-bottom: 2rem;
}

.footer-logo { width: 60px; height: 60px; margin-bottom: 1rem; }
.footer-section p { color: #cbd5e1; line-height: 1.6; font-size: 0.9rem; }
.footer-section h4 { margin-bottom: 1rem; color: white; }
.footer-section ul { list-style: none; padding: 0; }
.footer-section ul li { margin-bottom: 0.5rem; }
.footer-section a { color: #cbd5e1; text-decoration: none; transition: color 0.2s; }
.footer-section a:hover { color: white; }

.footer-bottom {
  text-align: center;
  padding-top: 2rem;
  border-top: 1px solid #334155;
  color: #94a3b8;
  font-size: 0.85rem;
}

/* Responsive */
@media (max-width: 768px) {
  .header { padding: 1rem; }
  .nav-links { display: none; }
  .hero-title { font-size: 2.5rem; }
  .access-buttons { grid-template-columns: 1fr; }
  .btn-access { padding: 1.25rem; }
  .about-stats { grid-template-columns: 1fr; gap: 1rem; }
  .footer-content { grid-template-columns: 1fr; gap: 2rem; }
  .section-title { font-size: 1.75rem; }
}
</style>
