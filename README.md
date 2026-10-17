# Pesantren CMS

**Pesantren CMS** adalah platform Content Management System (CMS) modular yang dirancang khusus untuk kebutuhan institusi pendidikan Islam, pondok pesantren, dan sekolah. Dibangun dengan arsitektur modern berbasis PHP Native tanpa ketergantungan framework berat, sistem ini menawarkan performa tinggi, efisiensi resource, serta fleksibilitas tinggi dalam pengelolaan informasi publik.

---

## 📌 Tentang Aplikasi

Pesantren CMS hadir sebagai solusi digital terpadu untuk membangun dan mengelola website lembaga pendidikan secara profesional. Sistem ini memadukan kemudahan antarmuka admin dengan arsitektur backend yang modular, memungkinkan lembaga mempublikasikan berita, agenda kegiatan, galeri, fasilitas, profil tenaga pengajar, hingga direktori alumni secara rapi, cepat, dan mudah dipelihara.

---

## ✨ Fitur Utama

### 1. Manajemen Konten Modular
- **Modul Pesantren Siap Pakai**: Dukungan terstruktur untuk berbagai tipe konten, mencakup:
  - **Agenda & Kegiatan**: Jadwal kegiatan santri, kajian, dan acara lembaga.
  - **Profil Pengajar / Asatidz**: Data biodata, keahlian, dan riwayat tenaga pendidik.
  - **Prestasi**: Dokumentasi capaian dan penghargaan santri maupun institusi.
  - **Fasilitas**: Informasi sarana dan prasarana pondok/sekolah.
  - **Galeri**: Dokumentasi foto kegiatan dan lingkungan lembaga.
  - **Alumni**: Jejak rekam dan direktori lulusan.
- **Artikel & Berita**: Pengelolaan artikel/warta lembaga dengan slug otomatis, kategori, dan status publikasi.
- **Halaman Statis (Pages)**: Pembuatan halaman informatif seperti Profil Lembaga, Visi & Misi, dan Struktur Organisasi.

### 2. Theme & Widget Engine
- **Kustomisasi Tema**: Pengaturan opsi tema (palet warna, tipografi) yang langsung terintegrasi ke CSS custom properties secara instan.
- **Widget Area Dinamis**: Tata letak fleksibel dengan kemampuan menyusun dan mengurutkan widget menggunakan antarmuka *drag-and-drop*.
- **Widget Siap Pakai**: Mendukung widget dinamis seperti artikel terbaru, konten teks/HTML bebas, dan widget kustom lainnya.

### 3. Media Manager Cerdas
- **Manajemen Berkas Terpusat**: Upload dan organisasi gambar serta dokumen dalam satu dashboard.
- **Optimasi Gambar Otomatis**: Generator otomatis untuk varian ukuran (thumbnail, medium) dan format modern WebP.
- **Pelacakan Penggunaan (Usage Tracking)**: Proteksi otomatis yang mencegah berkas terhapus jika masih aktif digunakan pada artikel atau halaman.

### 4. Menu & Navigasi Adaptif
- **Target Provider Pattern**: Pembuatan menu yang terhubung langsung dengan target entitas (artikel, kategori, modul, atau halaman), meminimalkan risiko tautan rusak (*broken link*).
- **Struktur Bertingkat**: Pengaturan navigasi multi-level (parent-child menu) yang mudah disesuaikan.

### 5. Keamanan & Hak Akses (RBAC)
- **Role-Based Access Control**: Pengaturan kewenangan berbasis peran (Super Admin, Editor, Author) dengan izin akses (*permissions*) granular.
- **Autentikasi Aman**: Perlindungan sesi dan pembatasan menu admin sesuai level hak akses pengguna yang sedang aktif.

### 6. Performa & Arsitektur Ekstensibel
- **Multi-Level Cache**: Dilengkapi *full-page cache*, *fragment cache*, dan *query cache* bawaan untuk kecepatan akses maksimal.
- **Sistem Plugin**: Dukungan modular untuk memperluas fungsionalitas sistem (seperti Card Builder dan komponen tambahan) tanpa mengotori kode inti (*core*).
