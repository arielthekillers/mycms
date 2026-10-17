# Panduan Pembuatan Plugin Pesantren CMS

Sistem Plugin dirancang agar siapa saja dapat mengembangkan fitur tambahan untuk CMS tanpa perlu mengubah *core code* (kode inti). Plugin yang dibuat bisa didistribusikan dalam format `.zip` dan dipasang melalui panel admin.

## 1. Struktur Dasar Plugin

Sebuah plugin wajib berada dalam satu folder unik (misalnya `PluginAgenda/`) dan memiliki struktur file dasar sebagai berikut:

```text
PluginAgenda/
├── plugin.json      (Wajib: Berisi metadata plugin)
└── routes.php       (Opsional: Mendefinisikan rute dan logika)
```

## 2. File `plugin.json`

File ini adalah identitas dari plugin Anda. Wajib diletakkan di *root* folder plugin Anda.

```json
{
    "name": "Plugin Agenda",
    "description": "Mengelola data acara dan agenda kegiatan pesantren.",
    "version": "1.0.0",
    "admin_url": "/admin/panel/agenda"
}
```

- **`name`**: Nama plugin yang akan tampil di halaman admin.
- **`description`**: Deskripsi singkat plugin.
- **`version`**: Versi plugin saat ini.
- **`admin_url`** *(opsional)*: URL ke halaman pengaturan plugin Anda. Jika diisi, sistem otomatis akan menampilkan tombol **"Kelola"** pada daftar plugin (tepat di sebelah tombol "Nonaktifkan") apabila plugin sedang aktif.

- **`name`** (Wajib): Nama plugin yang akan tampil di halaman admin.
- **`description`** (Opsional): Penjelasan singkat mengenai fungsi plugin.
- **`version`** (Opsional): Versi plugin saat ini.

## 3. File `routes.php`

File ini akan dieksekusi secara otomatis setiap kali aplikasi (CMS) berjalan, asalkan plugin Anda dalam status **Aktif**. Di sini Anda bisa mendaftarkan endpoint/URL, menjalankan *hook*, atau mendaftarkan halaman menu admin.

### Mendefinisikan Rute
CMS telah menyediakan variabel `$router` yang bisa langsung Anda gunakan di dalam `routes.php`.

```php
<?php
// Mendaftarkan URL publik: /agenda
$router->get('/agenda', function ($request, $response) {
    return $response->html('<h1>Selamat datang di Halaman Agenda!</h1>');
});
```

### Mengakses Core CMS (Database, Session, Auth)
Karena Pesantren CMS berbasis arsitektur *Singleton* pada Application, Anda dapat memanggil segala fasilitas CMS dengan cara berikut:

```php
<?php
use App\Core\Application;

$app = Application::getInstance();

$router->get('/api/agenda', function ($request, $response) use ($app) {
    // Menggunakan fasilitas Database bawaan CMS
    $agenda = $app->db->select("SELECT * FROM agenda WHERE status = 'active'");
    
    return $response->json(['data' => $agenda]);
});
```

## 4. File `uninstall.php` (Opsional tapi Sangat Disarankan)

Jika plugin Anda membuat tabel database baru atau menyimpan data, Anda sebaiknya menyertakan file `uninstall.php` di dalam root direktori plugin. File ini otomatis akan dieksekusi oleh CMS ketika admin menekan tombol **"Hapus"** pada plugin tersebut di halaman Admin. Ini memastikan tidak ada tabel atau data "sampah" yang tertinggal.

Contoh `uninstall.php`:
```php
<?php
declare(strict_types=1);

/** @var \App\Core\Database $db */
if (isset($db)) {
    // Hapus tabel terkait secara permanen
    $db->execute("DROP TABLE IF EXISTS plugin_galeri_items");
}
```

## 5. Tips & Praktik Terbaik (Best Practices)

> [!TIP]
> - **Gunakan Prefix Unik:** Jika plugin Anda membuat tabel database baru, gunakan prefix yang spesifik agar tidak bentrok dengan plugin lain, misalnya `agenda_events` atau `ext_agenda_data`.
> - **Gunakan Namespace:** Jika plugin Anda kompleks dan menggunakan sistem Class/OOP, deklarasikan *autoloader* Anda sendiri menggunakan `spl_autoload_register` di dalam `routes.php`, atau cukup *require* file PHP kelas Anda.
> - **Amankan Rute Admin:** Gunakan _Middleware_ jika ingin membuat halaman yang hanya bisa diakses admin.
> ```php
> use App\Http\Middleware\AuthMiddleware;
> 
> $app = Application::getInstance();
> $router->get('/admin/panel/agenda', function($req, $res) {
>     return $res->html('Halaman Pengaturan Agenda Khusus Admin');
> }, [new AuthMiddleware($app->auth)]);
> ```

## 5. Membuat File `.zip` Instalasi
Setelah selesai melakukan _coding_ plugin di komputer lokal Anda:
1. *Block* (pilih) file `plugin.json` beserta folder/file pendukung lainnya.
2. Klik kanan lalu jadikan satu buah file berformat `.zip` (misal: `AgendaV1.zip`).
3. Plugin siap diunggah oleh pengguna melalui menu **Sistem Plugin -> Upload & Install ZIP**.
