# MOTOCARE

## Kelompok 2

**Anggota:** Carlos, Cindi, Kenneth, Nicole, Samuel

------------------------------------------------------------------------

## 1. Latar Belakang {#1-latar-belakang}

Perkembangan teknologi semakin berkembang dengan pesat seiring zamannya.
Banyak sektor bisnis atau pekerjaan di zaman sekarang beralih
menggunakan teknologi untuk mempermudah pekerjaan, termasuk dalam
pelayanan jasa.

Dalam prosesnya, pelanggan masih dapat mengalami kesulitan dalam
melakukan booking service, memperoleh informasi mengenai layanan dan
suku cadang, serta mengetahui riwayat perawatan kendaraan. Hal ini
terutama menjadi kendala bagi pelanggan awam.

Berdasarkan permasalahan tersebut, dikembangkan sebuah sistem pelayanan
bengkel yang menyediakan fitur-fitur untuk memudahkan pelanggan dalam
melakukan pemesanan layanan, memperoleh informasi perawatan, serta
menjaga kendaraan agar tetap terawat secara lebih terencana.

## 2. Topik {#2-topik}

### Masalah di Bengkel Saat Ini

-   Antrean lama.
-   Pelanggan tidak mengetahui progres servis.
-   Pelanggan sering lupa melakukan servis.
-   Kekurangan sparepart.

### Solusi yang Ditemukan

Membuat sistem digital untuk mempermudah seluruh proses servis motor
dari awal hingga selesai sehingga pelanggan tidak perlu membuang waktu
dan pihak bengkel dapat mengelola operasional dengan lebih teratur.

## 3. Tujuan {#3-tujuan}

1.  **Mengotomatisasi pencatatan servis:** Menghilangkan pencatatan
    manual berbasis kertas agar riwayat perbaikan kendaraan tersimpan
    rapi dan mudah dilacak.
2.  **Meningkatkan efisiensi inventaris:** Memantau keluar-masuk suku
    cadang secara real-time guna mencegah kehabisan stok maupun selisih
    barang.
3.  **Transparansi transaksi:** Mempercepat proses pembuatan estimasi
    biaya, penagihan (faktur), dan pelaporan pendapatan bagi pemilik
    bengkel.

## 4. Manfaat {#4-manfaat}

Sistem ini bermanfaat untuk memudahkan pelanggan dalam melakukan booking
dan memantau servis kendaraan, menyimpan riwayat perawatan, serta
mendapatkan pengingat jadwal perawatan berdasarkan jarak tempuh.

Bagi bengkel, sistem membantu mengelola antrean dan data servis
pelanggan dengan lebih teratur.

Manfaat utama bagi pelanggan meliputi:

-   Perawatan kendaraan.
-   Booking atau penjadwalan servis.
-   Riwayat servis kendaraan.

## 5. Fitur-Fitur {#5-fitur-fitur}

Fitur-fitur ini berfungsi sebagai navigasi dan pemantauan pada sistem
bengkel. Selain itu, sistem memberikan informasi terkait suku cadang dan
perawatan motor.

### 5.1 Katalog Sparepart {#51-katalog-sparepart}

Menyediakan informasi seputar sparepart motor berdasarkan model motor
yang dimiliki, termasuk informasi ketersediaan barang.

### 5.2 Jenis Servis {#52-jenis-servis}

Pelanggan dapat memilih jenis servis yang akan dikerjakan pada saat
melakukan booking.

### 5.3 Booking Servis {#53-booking-servis}

Fitur utama untuk mengambil antrean servis pada jadwal tertentu.

### 5.4 Sign-In dan Model Motor {#54-sign-in-dan-model-motor}

Customer dapat mendaftar akun agar bisa menggunakan fitur yang tersedia.
Satu akun dapat digunakan untuk beberapa model motor, dengan maksimal 5
unit.

### 5.5 Status Servis {#55-status-servis}

Pelanggan dapat memantau keadaan antrean selama proses servis, dengan
status:

1.  Menunggu antrean.
2.  Sedang diperiksa.
3.  Menunggu persetujuan pelanggan.
4.  Sedang dikerjakan.
5.  Selesai.
6.  Sudah diambil.

### 5.6 Riwayat Servis dan Estimasi Perawatan {#56-riwayat-servis-dan-estimasi-perawatan}

Sistem memberitahu kapan perlu dilakukan penggantian sparepart atau
servis berdasarkan jarak tempuh kendaraan. Sistem juga membuat arsip
atau menyimpan riwayat servis yang pernah dilakukan.

### 5.7 Reminder Notification {#57-reminder-notification}

Memberikan informasi kepada pelanggan bahwa sudah tiba waktunya untuk
melakukan servis.

### 5.8 Loyalty Membership {#58-loyalty-membership}

Memberikan hadiah kepada customer atas kesetiaan mereka karena sering
melakukan transaksi.

## 6. System Request {#6-system-request}

### 6.1 Project Sponsor {#61-project-sponsor}

**Workshop Owner / Operations Manager (MotoCare)**

### 6.2 Business Requirements {#62-business-requirements}

1.  Pendaftaran akun dan manajemen profil motor pelanggan (maksimal 5
    unit).
2.  Fitur booking servis online dan pemilihan jenis layanan.
3.  Pelacakan status servis secara real-time: antre, diperiksa,
    dikerjakan, dan selesai.
4.  Manajemen katalog sparepart dan pemotongan stok otomatis.
5.  Penyimpanan riwayat servis dan notifikasi pengingat servis berkala.

### 6.3 Business Value {#63-business-value}

**Tangible:**

-   Mengurangi waktu tunggu atau antrean manual.
-   Memangkas waktu pencatatan kasir.
-   Menekan kerugian akibat selisih inventaris.

**Intangible:**

-   Meningkatkan kepuasan dan loyalitas pelanggan.
-   Meningkatkan transparansi estimasi biaya.
-   Meningkatkan profesionalitas citra bengkel.

### 6.4 Special Issue {#64-special-issue}

-   Sistem awal berfokus pada platform web responsif yang dapat diakses
    melalui browser HP atau laptop.
-   Membutuhkan sinkronisasi data real-time antara portal booking
    pelanggan dan dashboard mekanik/kasir.
-   Memerlukan keamanan data pribadi dan riwayat kendaraan pelanggan.

## 7. Ruang Lingkup dan Batasan Sistem {#7-ruang-lingkup-dan-batasan-sistem}

### 7.1 Problem Evidence {#71-problem-evidence}

Keluhan pelanggan terkait antrean yang lama, minimnya informasi progres
pengerjaan kendaraan, kasus kehabisan stok sparepart saat servis, serta
pencatatan nota fisik yang rentan hilang.

### 7.2 Primary Stakeholders {#72-primary-stakeholders}

-   Pelanggan motor.
-   Front desk/admin.
-   Mekanik.
-   Petugas gudang/inventaris.
-   Kasir.
-   Pemilik bengkel (owner).

### 7.3 In Scope {#73-in-scope}

-   Autentikasi user dan profil multi-motor.
-   Modul booking servis online.
-   Katalog sparepart.
-   Pelacakan status servis.
-   Pencatatan riwayat perawatan.
-   Modul kasir/billing dasar.

### 7.4 Out of Scope {#74-out-of-scope}

-   Modul penggajian teknisi (Payroll/HRIS).
-   Marketplace e-commerce untuk pengiriman barang ke luar kota.
-   Fitur derek darurat berbasis IoT/GPS.
-   Aplikasi native mobile Android/iOS.

### 7.5 Key Assumptions {#75-key-assumptions}

-   Pelanggan dan staf bengkel memiliki koneksi internet yang stabil.
-   Mekanik dibekali perangkat (tablet/smartphone bengkel) untuk
    memperbarui status pengerjaan secara langsung.

### 7.6 Initial Risks {#76-initial-risks}

-   Mekanik lambat atau enggan memperbarui status pengerjaan di sistem.
-   Pelanggan memasukkan data jarak tempuh (odometer) yang tidak akurat.
-   Koneksi internet bengkel terputus saat jam operasional.

## 8. Feasibility Summary {#8-feasibility-summary}

### Technical

Sangat layak (feasible) menggunakan teknologi web standar, yaitu
frontend responsif dan backend database.

### Economic

Layak (feasible) untuk skala proyek prototipe mahasiswa atau sistem
bengkel UMKM.

### Organizational

Layak (feasible) dengan dukungan owner dan kemudahan antarmuka bagi staf
bengkel.

### Recommendations

**PROCEED WITH CONDITIONS** --- Lanjutkan ke tahap analisis detail
setelah menyepakati skema alur status pengerjaan mekanik dan
standardisasi katalog harga jasa/suku cadang.

## 9. Business Need {#9-business-need}

MotoCare memberikan solusi digital untuk mengatasi masalah dalam
pelayanan dan pengelolaan bengkel, seperti antrean yang lama, pelanggan
yang sulit mengetahui progres servis, sering lupa jadwal perawatan, dan
kekurangan sparepart.

Sistem ini membantu pelanggan melakukan booking servis, memantau status
pengerjaan kendaraan, mendapatkan pengingat servis, serta membantu pihak
bengkel mengelola stok dan riwayat servis secara lebih teratur.

## 10. Business Documents Requirements {#10-business-documents-requirements}

### 10.1 Business Context {#101-business-context}

MotoCare bertujuan untuk membantu pengelolaan pelayanan pada sistem
bengkel seperti proses antrean, inventaris sparepart, hingga pemantauan
proses pengerjaan. Sistem ini juga membantu memberikan informasi untuk
melakukan servis berkala.

### 10.2 Business Objective {#102-business-objective}

**PROCEED WITH CONDITIONS** --- Lanjutkan ke tahap analisis detail
setelah menyepakati skema alur status pengerjaan mekanik dan
standardisasi katalog harga jasa/suku cadang.

### 10.3 Business Problem {#103-business-problem}

Pada sistem bengkel saat ini, masih banyak bengkel yang belum menerapkan
teknologi sehingga banyak kegiatan dilakukan secara manual, seperti
antrean, pencatatan inventaris, dan transaksi.

Terkadang pelanggan juga belum mengetahui kapan harus melakukan servis
berikutnya. Hal ini membuat data mudah keliru, hilang, dan berantakan.
Oleh karena itu, kelompok mengembangkan program MotoCare.

### 10.4 AS-IS Business Process {#104-as-is-business-process}

Alur proses bisnis bengkel saat ini:

1.  Customer datang.
2.  Customer melakukan antrean.
3.  Admin mencatat data servis.
4.  Mekanik memeriksa kendaraan.
5.  Customer menunggu informasi.
6.  Mekanik melakukan servis.
7.  Customer melakukan pembayaran.
8.  Customer mengambil kendaraan.

### 10.5 Process Diagnosis {#105-process-diagnosis}

-   **Waiting:** Antrean dan proses pencatatan masih manual.
-   **Visibility:** Customer tidak dapat mengetahui progres servis.
-   **Inventory:** Stok sparepart belum terpantau secara terintegrasi.
-   **Data:** Riwayat servis masih sulit dilacak dan berisiko hilang.

### 10.6 TO-BE Business Process {#106-to-be-business-process}

Alur proses bisnis yang diusulkan:

1.  Customer melakukan booking online.
2.  Workshop menerima booking.
3.  Mekanik memeriksa kendaraan.
4.  Status servis diperbarui.
5.  Sparepart digunakan dan stok diperbarui.
6.  Servis selesai.
7.  Customer melakukan pembayaran.
8.  Riwayat servis tersimpan.
9.  Sistem memberikan maintenance reminder.

### 10.7 Business Process Improvements {#107-business-process-improvements}

-   **Online Booking:** Mengurangi antrean.
-   **Service Status Tracking:** Meningkatkan visibilitas proses servis.
-   **Sparepart Monitoring:** Membantu kontrol stok.
-   **Digital Service History:** Memudahkan pelacakan riwayat servis.
-   **Maintenance Reminder:** Membantu customer melakukan servis
    berkala.

### 10.8 Business Requirements {#108-business-requirements}

-   **Account & Motor Profile:** Registrasi akun dan data kendaraan.
-   **Online Service Booking:** Booking servis dan pemilihan jenis
    servis.
-   **Service Status Tracking:** Pemantauan status pengerjaan.
-   **Sparepart Management:** Katalog dan monitoring stok.
-   **Service History:** Penyimpanan riwayat servis.
-   **Maintenance Reminder:** Pengingat maintenance.
-   **Basic Billing:** Estimasi dan transaksi servis.

------------------------------------------------------------------------

## Catatan Sumber

Dokumen ini disusun berdasarkan isi presentasi **MotoCare -- Kelompok
2** (12 halaman). Struktur dan istilah utama presentasi dipertahankan.
Informasi teknis yang tidak tercantum dalam presentasi tidak
ditambahkan.
