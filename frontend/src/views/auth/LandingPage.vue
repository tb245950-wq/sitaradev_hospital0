<template>
  <div class="landing-page">
    <!-- Decorative Circles -->
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <!-- Header Buttons -->
    <div class="header-buttons">
      <router-link to="/register" class="btn btn-outline">Daftar</router-link>
      <router-link to="/login" class="btn btn-primary">Masuk</router-link>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="logo-container">
        <img src="/logo-sitara.png" alt="SITARA Logo" class="logo" />
        <h1 class="title">SITARA</h1>
        <p class="subtitle">Sistem Informasi Terpadu</p>
        <p class="subtitle-small">Assessment dan Rekam Anak</p>
      </div>

      <div class="welcome-text">
        <h2>Selamat Datang</h2>
        <p>Sistem Informasi Terpadu Assessment dan Rekam Anak</p>
      </div>
    </div>
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

.btn-outline {
  background: transparent;
  color: #1e40af;
  border: 2px solid #1e40af;
}

.btn-outline:hover {
  background: #1e40af;
  color: white;
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
}

.logo-container {
  text-align: center;
  margin-bottom: 3rem;
}

.logo {
  width: 150px;
  height: 150px;
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
}
</style>