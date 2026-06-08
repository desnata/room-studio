// ==========================================
// CONFIGURATION: SUPABASE INITIALIZATION
// ==========================================

// Inisialisasi Klien Supabase secara Global
const db = window.supabase.createClient(
  'https://vhsrmfmvblfqolhwgwbt.supabase.co', 
  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZoc3JtZm12YmxmcW9saHdnd2J0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzU2ODQ4NjAsImV4cCI6MjA5MTI2MDg2MH0.jxVo-xAjpEyaqGiROMlWbdwmCga6GKNz_rnNrRYPpWQ'
);