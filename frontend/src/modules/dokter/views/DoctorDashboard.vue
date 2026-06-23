<template>
  <div>
    <h1>Doctor Dashboard</h1>
    <p>Welcome, Dr. {{ doctorName }}!</p>
  </div>
</template>

<script>
export default {
  name: 'DoctorDashboard',
  data() {
    return {
      doctorName: 'John Doe' // Replace with actual doctor name from authentication
    }
  }
}
</script>