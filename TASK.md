Daftar Fitur Sistem Informasi Penyaluran Bantuan Gizi Lansia
Dokumen ini berisi daftar fitur sistem berdasarkan role pengguna pada Sistem Informasi Penyaluran Bantuan Gizi Lansia.

Operator / Kader RW (Mobile App)
Authentication
•	Login
•	Logout
Pendataan Lansia
•	Input data lansia
•	Nama
•	NIK
•	Usia
•	Alamat
•	RW
•	Jenis kelamin
•	Kondisi kesehatan
•	Update/edit data lansia
•	Hapus data lansia
•	Lihat detail data lansia
•	Pencarian data lansia
•	Filter data berdasarkan nama
•	Filter data berdasarkan RW
•	Filter data berdasarkan kondisi kesehatan
•	Filter data berdasarkan status bantuan
Verifikasi Dokumen
•	Upload foto KTP lansia
•	Validasi kelengkapan data sebelum submit
Monitoring Data
•	Melihat daftar lansia terdaftar
•	Melihat status penerimaan bantuan
•	Melihat notifikasi berhasil/gagal input data
Sinkronisasi
•	Sinkronisasi data mobile ke server
•	Penyimpanan real-time saat internet tersedia

Admin Kelurahan (Web Dashboard Laravel => 1 repo dengan API)
Authentication
•	Login
•	Logout
Manajemen Data Lansia
•	Lihat seluruh data lansia
•	Verifikasi data lansia
•	Validasi identitas lansia
•	Kelola data lansia
•	Edit data lansia
•	Hapus data lansia
•	Pencarian & filter data
Manajemen Bantuan Gizi
•	Menentukan jumlah maksimal penerima bantuan
•	Mengatur kuota bantuan berdasarkan anggaran
•	Melakukan perankingan penerima bantuan otomatis berdasarkan usia
•	Melakukan perankingan penerima bantuan otomatis berdasarkan kondisi kesehatan
•	Menentukan prioritas penerima bantuan
Dashboard & Analytics
•	Dashboard jumlah lansia
•	Statistik lansia per RW
•	Distribusi usia
•	Statistik kondisi kesehatan
•	Monitoring data real-time
•	Monitoring penerima bantuan
Laporan
•	Generate laporan otomatis
•	Cetak laporan
•	Download laporan
•	Filter laporan berdasarkan RW
•	Filter laporan berdasarkan kondisi kesehatan
•	Filter laporan berdasarkan jenis laporan
•	Filter laporan berdasarkan jumlah data
•	Rekap penyaluran bantuan
Monitoring Operasional
•	Monitoring hasil input operator
•	Monitoring distribusi bantuan
•	Evaluasi ketepatan sasaran bantuan

Lurah (Web Laravel Blade => 1 repo dengan API)
Authentication
•	Login
•	Logout
Monitoring
•	Melihat dashboard laporan
•	Monitoring jumlah lansia
•	Monitoring distribusi usia
•	Monitoring kondisi kesehatan
•	Monitoring penerima bantuan per RW
Approval
•	Persetujuan akhir penerima bantuan gizi
•	Persetujuan laporan penyaluran bantuan
Reporting
•	Lihat laporan bantuan
•	Monitoring hasil evaluasi bantuan
Catatan: Dokumen ini disusun berdasarkan analisis kebutuhan sistem dari proposal penelitian.


ERD :
USER
- user_id
- nama
- username
- password_hash
- role
- is_active
- created_at

LANSIA
- lansia_id
- nik
- nama
- tanggal_lahir
- jenis_kelamin
- alamat
- rt
- rw
- foto_ktp
- created_by
- created_at
- updated_at
- deleted_at

PEMERIKSAAN_KESEHATAN
- pemeriksaan_id
- lansia_id
- tanggal_periksa
- berat_badan
- tekanan_darah
- hasil_periksa
- catatan

PENDATAAN
- pendataan_id
- lansia_id
- user_id
- status_verifikasi
- verified_by
- verified_at
- tanggal_input

BANTUAN_GIZI
- bantuan_id
- lansia_id
- periode_bulan
- periode_tahun
- skor_ranking
- status_penerima
- approved_by
- approved_at