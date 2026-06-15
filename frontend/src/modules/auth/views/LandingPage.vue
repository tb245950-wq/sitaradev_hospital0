<template>
  <div class="landing-page">
    <!-- Decorative Circles -->
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <!-- Header Buttons -->
    <div class="header-buttons">
      <router-link to="/login" class="btn btn-primary">Masuk</router-link>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="logo-container">
        <img src="@/assets/SITARA_RM_BG.png" alt="SITARA Logo" class="logo" />
        <h1 class="title">SITARA</h1>
        <p class="subtitle">Sistem Informasi Terpadu</p>
        <p class="subtitle-small">Assessment dan Rekam Anak</p>
      </div>

      <div class="welcome-text">
        <h2>Selamat Datang</h2>
        <p>Sistem Informasi Terpadu Assessment dan Rekam Anak</p>
      </div>

      <!-- Info Box untuk Staff -->
      <div class="staff-info-box">
        <div class="staff-icon"></div>
        <h3>Akses Khusus Staff</h3>
        <p>
          Sistem ini hanya untuk <strong>Admin, Dokter, dan Terapis</strong> 
          yang telah terdaftar di klinik SITARA.
        </p>
        <p class="staff-note">
          Jika Anda adalah staff dan belum memiliki akun, 
          silakan hubungi administrator klinik.
        </p>
      </div>

      <!-- Features Preview -->
      <div class="features-preview">
        <div class="feature-item">
          <div class="feature-icon">👥</div>
          <h4>Manajemen Pasien</h4>
          <p>Kelola data pasien dengan mudah</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">📋</div>
          <h4>Assessment Medis</h4>
          <p>Rekam assessment dan diagnosis</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">🧠</div>
          <h4>Program Terapi</h4>
          <p>Atur dan pantau program terapi</p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">📊</div>
          <h4>Laporan Lengkap</h4>
          <p>Generate laporan harian & bulanan</p>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="landing-footer">
      <p>&copy; 2026 SITARA - Sistem Informasi Terpadu Assessment dan Rekam Anak</p>
    </footer>
  </div>
</template>

<script setup>
// Component logic here
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(59, 130, 246, 0.1);
  z-index: 0;
}

.circle-1 {
  width: 400px;
  height: 400px;
  top: -100px;
  left: -100px;
}

.circle-2 {
  width: 300px;
  height: 300px;
  bottom: -50px;
  right: -50px;
}

.header-buttons {
  position: absolute;
  top: 2rem;
  right: 2rem;
  display: flex;
  gap: 1rem;
  z-index: 10;
}

.btn {
  padding: 0.75rem 2rem;
  border-radius: 0.5rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s;
  cursor: pointer;
  border: none;
  font-size: 1rem;
}

.btn-primary {
  background: #1e40af;
  color: white;
  box-shadow: 0 4px 6px rgba(30, 64, 175, 0.2);
}

.btn-primary:hover {
  background: #1e3a8a;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
}

.main-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  position: relative;
  z-index: 1;
  padding: 2rem;
  gap: 2rem;
}

.logo-container {
  text-align: center;
  margin-bottom: 1rem;
}

.logo {
  width: 200px;
  height: 160px;
  object-fit: contain;
  margin-bottom: 1rem;
}

.title {
  font-size: 3rem;
  font-weight: 700;
  color: #1e40af;
  margin: 0.5rem 0;
  letter-spacing: 0.1em;
}

.subtitle {
  font-size: 1rem;
  color: #64748b;
  margin: 0.25rem 0;
  font-weight: 500;
}

.subtitle-small {
  font-size: 0.875rem;
  color: #94a3b8;
  margin: 0;
}

.welcome-text {
  text-align: center;
  color: #334155;
}

.welcome-text h2 {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #1e293b;
}

.welcome-text p {
  font-size: 1.25rem;
  color: #64748b;
  max-width: 600px;
  line-height: 1.6;
}

/* Staff Info Box */
.staff-info-box {
  background: white;
  border: 2px solid #1e40af;
  border-radius: 1rem;
  padding: 2rem;
  max-width: 600px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(30, 64, 175, 0.1);
}

.staff-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.staff-info-box h3 {
  font-size: 1.5rem;
  color: #1e40af;
  margin-bottom: 1rem;
  font-weight: 700;
}

.staff-info-box p {
  color: #475569;
  line-height: 1.6;
  margin-bottom: 0.75rem;
}

.staff-note {
  font-size: 0.9rem;
  color: #64748b;
  font-style: italic;
}

/* Features Preview */
.features-preview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  max-width: 900px;
  width: 100%;
  margin-top: 1rem;
}

.feature-item {
  background: white;
  padding: 1.5rem;
  border-radius: 0.75rem;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s;
}

.feature-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.feature-icon {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
}

.feature-item h4 {
  font-size: 1rem;
  color: #1e293b;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.feature-item p {
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.4;
}

/* Footer */
.landing-footer {
  background: #1e293b;
  color: white;
  text-align: center;
  padding: 1.5rem;
  position: relative;
  z-index: 1;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .header-buttons {
    top: 1rem;
    right: 1rem;
    flex-direction: column;
  }

  .title {
    font-size: 2rem;
  }

  .welcome-text h2 {
    font-size: 1.5rem;
  }

  .welcome-text p {
    font-size: 1rem;
  }

  .features-preview {
    grid-template-columns: 1fr;
  }

  .staff-info-box {
    padding: 1.5rem;
  }
}
</style>