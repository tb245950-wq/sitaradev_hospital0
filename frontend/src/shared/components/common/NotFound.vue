<template>
  <div class="not-found-page">
    <div class="content">
      <div class="icon">🔍</div>
      <h1>404 - Halaman Tidak Ditemukan</h1>
      <p>Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
      <router-link to="/dashboard" class="btn-home">
        Kembali ke Dashboard
      </router-link>
    </div>
  </div>
</template>

<style scoped>
.not-found-page {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  text-align: center;
}

.content {
  background: white;
  padding: 3rem;
  border-radius: 1rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

h1 {
  font-size: 2rem;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

p {
  color: #64748b;
  margin-bottom: 2rem;
}

.btn-home {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 0.5rem;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-home:hover {
  background: #2563eb;
  transform: translateY(-2px);
}
</style>
