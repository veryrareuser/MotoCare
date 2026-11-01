**System Request**  
**Motocare**

1. **System Request**

| Section | Content |
| ----- | ----- |
| Project Sponsor | Owner Bengkel |
| Business Need | Antrean bengkel menumpuk karena pencatatan manual, pelanggan kesulitan mengetahui jadwal dan progres pengerjaan secara transparan, serta bengkel sering mengalami kendala selisih atau kekurangan stok suku cadang.  |
| Business Requirements | Pendaftaran akun & manajemen profil motor pelanggan (maksimal 5 unit). Fitur booking servis online dan pemilihan jenis layanan. Pelacakan status servis secara real-time Antre, Diperiksa, Dikerjakan, Selesai). Manajemen katalog sparepart dan pemotongan stok otomatis. Penyimpanan riwayat servis & notifikasi pengingat servis berkala. |
| Business Value | Tangible: Mengurangi waktu tunggu/antrean manual, memangkas waktu pencatatan kasir, dan menekan kerugian akibat selisih inventaris. Intangible: Meningkatkan kepuasan dan loyalitas pelanggan, transparansi estimasi biaya, serta profesionalitas citra bengkel.  |
| Special Issues | Sistem awal berfokus pada platform Web Responsive (dapat diakses browser HP/laptop); membutuhkan sinkronisasi data real-time antara portal booking pelanggan dan dashboard mekanik/kasir; keamanan data pribadi dan riwayat kendaraan pelanggan.  |

2. **Supporting Initiation information**

| Section | Content |
| ----- | ----- |
| Problem Evidence | Keluhan pelanggan terkait antrean yang lama, minimnya info progres pengerjaan kendaraan, kasus kehabisan stok sparepart saat servis, serta pencatatan nota fisik yang rentan hilang.  |
| Primary Stakeholders | Pelanggan Bengkel Owner: Operational Manager bengkel Admin: Sebagai pengelola sistem webiste bengkel Petugas Gudang: Pengelola Inventaris bengkel (stok Sparepart) Kasir: Sebagai Pencatat Transaksi dan pengelola Administrasi Mekanik: Sebagai Pekerja memperbaiki motor dan update progress servis |
| In Scope | Autentikasi user & multi-motor profile; modul booking servis online; katalog sparepart; tracking status servis; pencatatan riwayat perawatan; modul kasir/billing dasar.  |
| Out of Scope | Modul penggajian teknisi (Payroll/HRIS); marketplace e-commerce pengiriman barang ke luar kota; fitur derek darurat berbasis IoT/GPS; aplikasi native mobile (Android/iOS murni).  |
| Key Assumptions | Pelanggan dan staf bengkel memiliki koneksi internet yang stabil; mekanik dibekali perangkat (tablet/smartphone bengkel) untuk memperbarui status pengerjaan secara langsung.  |
| Initial Risk | Mekanik lambat/enggan memperbarui status pengerjaan di sistem; pelanggan memasukkan data jarak tempuh (odometer) yang tidak akurat; koneksi internet bengkel terputus saat jam operasional.  |
| Feasibility Summary | Technical: Sistem menggunakan *responsive web stack* standar seperti HTML/CSS/JavaScript, Laravel/Node.js, dan MySQL/PostgreSQL sehingga pengembangannya relatif mudah dan risikonya rendah. Sistem dapat diakses melalui PC, laptop, tablet, atau smartphone tanpa membutuhkan perangkat keras khusus, dengan fitur utama seperti booking, stok otomatis, dan *live tracking* yang masih dalam kemampuan teknis tim. Economic: Biaya pengembangan dan operasional relatif rendah karena menggunakan teknologi *in-house* serta hanya membutuhkan hosting/VPS dan domain. Sistem berpotensi mengurangi kerugian akibat kesalahan stok, mempercepat pelayanan, dan meningkatkan *repeat order* melalui pengingat servis sehingga memberikan potensi ROI yang baik. Organizational: Sistem mendukung modernisasi operasional bengkel dari proses manual berbasis kertas menjadi sistem digital yang lebih terstruktur dan transparan. *Role-based dashboard* yang sederhana memudahkan pelanggan, kasir, mekanik, dan bagian gudang dalam menggunakan sistem sesuai tugas masing-masing  |
| Recommendation |  |

