<template>
  <div class="activities-card">
    <div class="activities-header">
      <h3 class="activities-title">Aktivitas Terbaru</h3>
      <button class="view-all-btn">Lihat Semua</button>
    </div>
    
    <div class="table-container">
      <table class="activities-table">
        <thead>
          <tr>
            <th>Waktu</th>
            <th>Pasien</th>
            <th>Aktivitas</th>
            <th>Petugas</th>
            <th>Poli</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="activities.length === 0">
            <td colspan="6" class="empty-cell">Belum ada aktivitas terbaru</td>
          </tr>
          <tr v-for="(activity, index) in activities" :key="index">
            <td class="time-cell">{{ activity.time }}</td>
            <td>
              <div class="patient-info">
                <div class="patient-name">{{ activity.patient.name }}</div>
                <div class="patient-nik">NIK: {{ activity.patient.nik }}</div>
              </div>
            </td>
            <td>{{ activity.activity }}</td>
            <td>{{ activity.staff }}</td>
            <td>{{ activity.poli }}</td>
            <td>
              <span :class="['status-badge', getStatusClass(activity.status)]">
                {{ activity.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  activities: { type: Array, required: true }
})

const getStatusClass = (status) => {
  const s = status.toLowerCase()
  if (s === 'selesai' || s === 'completed' || s === 'active') return 'status-success'
  if (s === 'berlangsung' || s === 'process' || s === 'pending') return 'status-warning'
  if (s === 'baru' || s === 'new' || s === 'waiting') return 'status-info'
  return ''
}
</script>

<style scoped>
.activities-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #f1f5f9;
}

.activities-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.activities-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.view-all-btn {
  padding: 0.375rem 0.75rem;
  background: transparent;
  color: #3b82f6;
  border: 1px solid #3b82f6;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.view-all-btn:hover {
  background: #eff6ff;
}

.table-container {
  overflow-x: auto;
}

.activities-table {
  width: 100%;
  border-collapse: collapse;
}

.activities-table th {
  text-align: left;
  padding: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  border-bottom: 1px solid #f1f5f9;
}

.activities-table td {
  padding: 1rem 0.75rem;
  font-size: 0.8125rem;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
}

.time-cell {
  font-weight: 600;
  color: #1e293b;
}

.patient-info {
  display: flex;
  flex-direction: column;
}

.patient-name {
  font-weight: 500;
  color: #1e293b;
}

.patient-nik {
  font-size: 0.7rem;
  color: #94a3b8;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
}

.status-success {
  background: #dcfce7;
  color: #15803d;
}

.status-warning {
  background: #fef3c7;
  color: #b45309;
}

.status-info {
  background: #dbeafe;
  color: #1d4ed8;
}

.empty-cell {
  text-align: center;
  padding: 3rem;
  color: #94a3b8;
  font-style: italic;
}
</style>
