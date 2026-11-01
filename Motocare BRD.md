**Motocare System**  
**Business Requirements Document**

1. **Business Context**

Motocare bertujuan untuk membantu pengelolaan pelayanan pada sistem bengkel seperti; proses antrean, inventaris sparepart, dan pemantauan proses pengerjaan. Sistem ini juga membantu memberikan informasi untuk melakukan servis berkala.

2. **Business Problem**

Pada sistem bengkel saat ini, masih banyak bengkel yang belum menerapkan teknologi kepada sistem mereka sehingga masih banyak hal dilakukan manual, seperti antre, pencatatan inventaris, dan transaksi nya. Bahkan terkadang, masih banyak pelanggan yang belum tahu kapan service berikutnya. Hal ini membuat data mudah keliru, hilang dan berantakan, maka dari itu kami membuat program Motocare ini.

3. **Business Objectives**  
1. Meningkatkan keteraturan proses antrean dan booking servis kendaraan.  
2. Mengurangi koordinasi manual antara pelanggan, staff bengkel, dan mekanik.  
3. Meningkatkan visibilitas terhadap status pengerjaan kendaraan.  
4. Membantu bengkel dalam memantau ketersediaan dan penggunaan sparepart.  
5. Menyediakan informasi riwayat servis dan pengingat servis bagi pelanggan.  
6. Mendukung pemantauan aktivitas dan operasional bengkel secara lebih terstruktur.  
     
4. **Stakeholders**  
1. Pelanggan: Melakukan booking antrean, melihat status pengerjaan / servis, melihat histori servis, menerima pengingat waktu servis, loyalty member, melihat ketersediaan katalog sparepart, dan membeli katalog sparepart secara online melalui website.  
2. Mekanik: Mengubah (memperbarui) status pengerjaan, melakukan pengerjaan servis, mencatat penggunaan sparepart.  
3. Staff: Mengelola daftar antrean, mengelola data pelanggan, mengelola inventaris dan administrasi.  
4. Owner: Memantau aktivitas operasional bengkel dan ketersediaan sparepart  
     
5. **Scope**  
1. **In Scope:** Autentikasi user & multi-motor profile, modul booking servis online, katalog sparepart, tracking status servis, pencatatan riwayat perawatan, modul kasir/billing dasar, dan pengecekan serta pengelolaan inventaris dan administrasi.  
2. **Out Scope:** Modul penggajian teknisi (Payroll/HRIS), marketplace e-commerce pengiriman barang ke luar kota, fitur derek darurat berbasis IoT/GPS, aplikasi native mobile (Android/iOS murni).   
     
6. **Business Requirements**

| BR \- Number | Kebutuhan Bisnis |
| :---- | ----- |
| BR-01 | Pelanggan harus dapat melakukan booking servis sesuai jadwal dan kapasitas pelayanan bengkel.  |
| BR-02 | Bengkel harus bisa melihat dan mengelola antrean servis pelanggan. Dan bengkel harus dapat mencatat dan memantau status pengerjaan kendaraan. |
| BR-03 | Mekanik harus dapat memperbarui informasi mengenai perkembangan pengerjaan servis kendaraan.  |
| BR-04 | Bengkel harus dapat mengelola informasi ketersediaan dan penggunaan sparepart.  |
| BR-05 | Pelanggan harus dapat melihat status pengerjaan kendaraan yang sedang diservis.  |
| BR-06 | Sistem harus dapat menyimpan riwayat servis kendaraan untuk kebutuhan pemantauan dan pelayanan pelanggan.  |
| BR-07 | Sistem harus dapat memberikan informasi pengingat kepada pelanggan untuk melakukan servis kembali berdasarkan kondisi yang telah ditentukan.  |
| BR-08 | Pemilik bengkel harus dapat memperoleh informasi mengenai aktivitas pelayanan dan kondisi operasional bengkel.  |
| BR-09 | Pelanggan harus bisa melihat stok ketersediaan Sparepart |
| BR-10 | Staff harus bisa memantau dan memperbarui Ketersediaan Sparepart. Staff juga harus bisa mengelola Administrasi bengkel, serta memperbarui histori servis Pelanggan pada sistem |

7. **Business Rules**

| BRules \- Number | Kebutuhan Bisnis |
| :---- | ----- |
| BRules-01 | Pelanggan harus dapat melakukan booking servis sesuai jadwal dan kapasitas pelayanan bengkel.  |
| BRules-02 | Bengkel harus bisa melihat dan mengelola antrean servis pelanggan |
| BRules-03 | Bengkel harus dapat mencatat dan memantau status pengerjaan kendaraan.  |
| BRules-04 | Mekanik harus dapat memperbarui informasi mengenai perkembangan pengerjaan servis kendaraan.  |
| BRules-05 | Bengkel harus dapat mengelola informasi ketersediaan dan penggunaan sparepart.  |
| BRules-06 | Pelanggan harus dapat melihat status pengerjaan kendaraan yang sedang diservis.  |
| BRules-07 | Sistem harus dapat menyimpan riwayat servis kendaraan untuk kebutuhan pemantauan dan pelayanan pelanggan.  |

8. **Assumptions, Constraints, and Risks**

| Section | Content |
| :---- | :---- |
| Assumptions | Data pelanggan dan kendaraan tersedia, staff dan mekanik memiliki akses ke sistem, dan informasi servis dapat diperbarui selama proses pengerjaan.  |
| Constraints | Kapasitas pelayanan bengkel terbatas, proses servis bergantung pada ketersediaan mekanik dan sparepart, dan sumber daya pengembangan sistem terbatas.  |
| Risks | Data servis tidak (lupa / gagal) diperbarui, kesalahan pencatatan sparepart, pelanggan tidak mengikuti jadwal booking, informasi stok tidak sesuai dengan kondisi sebenarnya, dan terlalu banyak notifikasi kepada pelanggan.  |

9. **Success Criteria**  
1. Staff dapat melihat dan mengelola antrean pelanggan tanpa harus menggabungkan beberapa pencatatan manual.  
2. Status pengerjaan kendaraan dapat diketahui oleh pihak yang berkepentingan.  
3. Penggunaan dan ketersediaan sparepart dapat dipantau dengan lebih terstruktur.  
4. Riwayat servis kendaraan dapat ditelusuri kembali ketika diperlukan.  
5. Pelanggan dapat memperoleh informasi mengenai perkembangan servis kendaraannya.  
6. Pelanggan menerima pengingat ketika kendaraan sudah waktunya melakukan servis kembali.  
7. Pemilik bengkel dapat memperoleh informasi yang diperlukan untuk memantau operasional bengkel.  
     
10. **BRD Boundary**

Motocare dibuat dengan tujuan membantu customer bengkel supaya mendapatkan kemudahan untuk melakukan booking servis serta bisa memonitoring Kendaraan saat servis dan juga dalam perawatan kendaraannya. Dari segi bengkel, bertujuan untuk membantu bengkel dalam mengelola operasional bengkel, agar lebih teratur dan transparan.