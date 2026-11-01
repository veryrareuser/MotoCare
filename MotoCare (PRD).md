Berikut adalah dokumen lengkap **Product Requirements Document (PRD)** untuk sistem **MotoCare**, yang disusun mengikuti struktur dan standar acuan perkuliahan Sistem Informasi:

### **1\. Product Overview**

MotoCare adalah platform sistem informasi bengkel berbasis web responsif yang mengintegrasikan layanan pendaftaran dan reservasi servis online bagi pelanggan, pemantauan status pengerjaan servis secara real-time, manajemen katalog serta pemotongan stok suku cadang otomatis, dan rekapitulasi riwayat servis kendaraan digital.

### **2\. Product Goals**

* Memberikan kemudahan bagi pelanggan dalam reservasi jadwal servis dan pemantauan status perbaikan secara transparan.  
* Menghilangkan antrean manual yang tidak teratur melalui sistem nomor antrean digital.  
* Mengotomatisasi pemotongan stok suku cadang langsung saat pengerjaan servis untuk mencegah selisih persediaan.  
* Menyediakan catatan riwayat servis terpusat untuk memprediksi jadwal perawatan berkala berikutnya.  
* Mempercepat proses pembuatan estimasi biaya, faktur kasir, dan penutupan transaksi servis.

### **3\. User Roles**

| Section | Content |
| :---- | :---- |
| **Pelanggan (Customer)** | Mendaftarkan akun, mengelola hingga 5 profil motor, melakukan booking servis, memantau live tracking pengerjaan, dan melihat histori servis.  |
| **Front Desk / Admin** | Memverifikasi antrean booking, menerima kedatangan walk-in, dan menerbitkan tiket *Work Order* (WO) ke mekanik.  |
| **Mekanik** | Menginput hasil inspeksi, mencatat suku cadang dan jenis jasa yang digunakan, serta memperbarui status pengerjaan kendaraan.  |
| **Petugas Gudang** | Mengelola katalog suku cadang, memperbarui data stok awal/restock, serta memantau notifikasi stok menipis.  |
| **Kasir** | Memeriksa ringkasan WO selesai, memproses invoice/penagihan, mencatat pembayaran, dan mencetak bukti pembayaran digital/fisik.  |
| **Owner / Manajemen** | Melihat ringkasan laporan transaksi harian/bulanan, omzet servis, dan analitik penggunaan sparepart.  |

### **4\. Core Features**

| Section | Content |
| :---- | :---- |
| **Customer & Bike Profile** | Manajemen identitas pelanggan dan pencatatan hingga 5 nomor polisi/model motor per akun.  |
| **Online Booking & Queue** | Penjadwalan tanggal dan jam kunjungan servis bengkel yang menghasilkan nomor tiket antrean digital.  |
| **Live Service Tracker** | Modul pemantauan tahapan pengerjaan kendaraan secara berkala (*Antre*, *Diperiksa*, *Dikerjakan*, *Selesai*).  |
| **Inventory & Sparepart Catalog** | Pencatatan master data suku cadang dengan sinkronisasi pemotongan kuantitas stok otomatis saat dipakai pada WO.  |
| **Cashier & Billing System** | Kalkulasi total tarif jasa dan harga suku cadang secara otomatis beserta pembuatan faktur pembayaran.  |
| **Service History & Auto-Reminder** | Penyimpanan riwayat perawatan lampau serta kalkulasi jadwal servis berikutnya berdasarkan jarak tempuh atau waktu pemakaian.  |

### **5\. Functional Requirements**

| ID | Functional Requirement |
| :---- | :---- |
| **FR-01** | Sistem harus memungkinkan pelanggan mendaftar akun dan menyimpan data maksimal 5 model motor.  |
| **FR-02** | Sistem harus menyediakan formulir pemilihan jenis servis, tanggal, dan slot jam kedatangan yang tersedia.  |
| **FR-03** | Sistem harus menerbitkan nomor antrean digital unik setelah pelanggan mengonfirmasi booking servis.  |
| **FR-04** | Sistem harus memungkinkan mekanik mencatat item sparepart dan jasa yang digunakan ke dalam tiket pengerjaan.  |
| **FR-05** | Sistem harus memperbarui status pengerjaan secara real-time (*Menunggu Antrean*, *Sedang Diperiksa*, *Sedang Dikerjakan*, *Selesai*).  |
| **FR-06** | Sistem harus secara otomatis memotong stok sparepart di database gudang ketika item disahkan pada tiket pengerjaan.  |
| **FR-07** | Sistem harus menampilkan rincian biaya (jasa \+ suku cadang) pada dashboard kasir ketika status servis diset menjadi *Selesai*.  |
| **FR-08** | Sistem harus menyimpan rekap faktur pembayaran ke dalam menu Riwayat Servis pada profil pelanggan bersangkutan.  |
| **FR-09** | Sistem harus mengirimkan notifikasi pengingat perawatan berkala berdasarkan kalkulasi tanggal servis terakhir atau estimasi jarak tempuh.  |
| **FR-10** | Sistem harus membatasi akses modul dan data berdasarkan hak akses pengguna (*Role-Based Access Control*).  |

### **6\. Non-Functional Requirements**

| Section | Content |
| :---- | :---- |
| **NFR-01 \- Usability** | Antarmuka pengguna harus intuitif sehingga pelanggan awam dapat menyelesaikan booking dalam kurang dari 3 menit tanpa buku panduan.  |
| **NFR-02 \- Performance** | Waktu muat halaman (*page load*) dan pembaruan live status pengerjaan tidak boleh melebihi 3 detik pada kondisi jaringan stabil.  |
| **NFR-03 \- Security** | Password akun pelanggan dan staf harus dienkripsi dengan standar hash aman, dan transaksi kasir hanya dapat diakses staf berwenang.  |
| **NFR-04 \- Reliability** | Pengurangan stok inventaris dan pencatatan transaksi kasir harus bersifat atomik (tidak boleh terjadi selisih akibat kegagalan koneksi sesaat).  |
| **NFR-05 \- Availability** | Sistem harus memiliki *uptime* operasional minimal 99% selama jam buka bengkel.  |
| **NFR-06 \- Auditability** | Setiap perubahan status tiket pengerjaan dan pengambilan sparepart wajib mencatat identitas staf pelaksana beserta *timestamp*.  |

### **7\. Primary User Flow**

1. Pelanggan melakukan sign-in melalui website MotoCare dan mendaftarkan data profil motor (maksimal 5 unit).

2. Pelanggan memilih motor yang akan di servis, menentukan jenis servis yang diinginkan, dan memilih jadwal kunjungan bengkel.

3. Pelanggan mengkonfirmasi pesanan dan memperoleh nomor tiket antrian digital.

4. Pelanggan tiba di bengkel sesuai jadwal, lalu mekanik melakukan inspeksi kendaraan.

5. Pelanggan memantau progres pengerjaan servis secara real-time melalui sistem.

6. Setelah servis selesai, kasir memproses tagihan biaya jasa dan sparepart yang dipakai (disertai pemotongan stok otomatis).

7. Pelanggan membayar dan mengambil kendaraan; nota otomatis tersimpan ke Riwayat Servis akun pengguna.

8. Sistem menghitung estimasi pemakaian dan otomatis mengirimkan notifikasi pengingat saat tiba jadwal servis berikutnya.

### **8\. Acceptance Criteria Example**

| Section | Content |
| :---- | :---- |
| **Feature** | Booking Servis Online  |
| **Given** | Pelanggan sudah sign-in ke akun MotoCare dan memilih profil motor yang terdaftar.  |
| **When** | Pelanggan memilih jenis servis, tanggal, serta waktu kedatangan yang tersedia, lalu menekan tombol konfirmasi.  |
| **Then** | Sistem menyimpan data pemesanan, mengurangi kuota slot harian bengkel, dan menampilkan nomor antrean digital pada profil pengguna.  |
| **Exception** | Jika kuota slot pada tanggal dan jam tersebut sudah penuh, sistem menampilkan pesan peringatan dan meminta pelanggan memilih jadwal lain.  |

### **9\. Success Metrics**

* Pengurangan rata-rata waktu tunggu pelanggan di ruang tunggu bengkel hingga 30%.

* Pemangkasan waktu administrasi pembuatan nota dan penagihan kasir menjadi di bawah 2 menit per kendaraan.

* Penurunan tingkat selisih stok sparepart (*inventory discrepancy*) dan pencegahan kondisi *stockout* mendadak.

* Tingkat retensi dan kepuasan pelanggan melalui kemudahan akses riwayat servis serta pengingat berkala.

* Persentase kepatuhan mekanik dan staf kasir dalam memperbarui status transaksi secara real-time pada sistem.  
