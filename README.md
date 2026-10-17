# Pesantren CMS — Core Bootstrap

Modular enterprise CMS untuk instansi pendidikan/pesantren. Dibangun dengan
PHP native (tanpa framework), target PHP 8.4+.

## Setup

1. Install dependencies:
   ```bash
   composer install
   ```

2. Salin `.env.example` menjadi `.env` dan sesuaikan kredensial database:
   ```bash
   cp .env.example .env
   ```

3. Buat database MySQL kosong sesuai `DB_DATABASE` di `.env`.

4. Jalankan migration:
   ```bash
   php bin/console migrate
   ```

5. Arahkan document root web server ke folder `public/`, atau untuk
   development cepat pakai PHP built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```

## Perintah CLI yang tersedia

```bash
php bin/console migrate            # jalankan migration yang pending
php bin/console migrate:rollback   # rollback batch migration terakhir
php bin/console migrate:status     # lihat status semua migration
php bin/console db:seed            # seed content_modules + roles/permissions default
php bin/console make:user          # buat user baru + assign role (interaktif)
```

### Urutan setup awal yang disarankan

```bash
php bin/console migrate     # buat semua tabel
php bin/console db:seed     # daftarkan 6 modul pesantren + role + theme/layout/widget default
php bin/console make:user   # buat akun super-admin pertama
php -S localhost:8000 -t public
php bin/console queue:work  # (di terminal terpisah) proses image resize di background
```

Lalu buka `http://localhost:8000/` — homepage sudah render HTML sungguhan dari Theme Engine.
Login admin di `http://localhost:8000/admin/login`, lalu kelola konten di
`http://localhost:8000/admin/panel`.

## Struktur Migration (urutan penting)

Urutan file migration mengikuti dependency foreign key:

1. `0001_create_media_tables` — media_folders, media, media_usages
2. `0002_create_users_table` — users, password_reset_tokens, user_sessions
   (bergantung pada `media` untuk avatar)
3. `0003_create_menu_tables` — menus, menu_items (Target Provider pattern)
4. `0004_create_theme_tables` — themes, theme_options
5. `0005_create_presentation_tables` — layouts, templates, widget_areas,
   widgets, widget_instances
6. `0006_create_rbac_tables` — roles, permissions, role_permissions,
   model_has_roles, model_has_permissions (harus ada sebelum #7 karena
   trigger auto-permission menulis ke tabel `permissions`)
7. `0007_create_content_modules_tables` — content_modules,
   module_categories, module_contents + trigger auto-permission generation
8. `0008_create_articles_tables` — categories, articles, article_categories
9. `0009_create_jobs_table` — background job queue (used by Media Manager's image resize)
10. `0010_create_media_usage_triggers` — keeps `media.usage_count` in sync with `media_usages`
11. `0011_create_pages_table` — static Pages (Menu target_type = 'page')

## Yang sudah dibangun di tahap ini (Core)

- `App\Core\Database` — PDO wrapper (query/select/insert/execute/transaction)
- `App\Core\Migrator` — migration runner (migrate/rollback/status)
- `App\Core\Router` — slug-based dynamic routing + fallback resolver
- `App\Core\Request` / `App\Core\Response` — HTTP layer minimal
- `App\Core\EventBus` — hook system untuk ekstensibilitas modul
- `App\Core\Application` — bootstrap & wiring semua komponen di atas
- `App\Core\Auth\Session` — native session wrapper (secure cookie params, regenerate on login/logout)
- `App\Core\Auth\AuthManager` — attempt/login/logout, password hashing (bcrypt via `password_hash`)
- `App\Core\Auth\Rbac` — `can()` / `hasRole()` / `assignRole()`, dengan super-admin bypass dan
  per-request cache (tidak query berulang untuk user yang sama)
- `App\Http\Middleware\AuthMiddleware` & `PermissionMiddleware` — dipasang per-route lewat
  `Router::get/post(..., $middleware = [])`; `Router` mendukung global middleware
  (dipakai untuk `Session::start()`) dan chain per-route
- `database/seeders/RolePermissionSeeder` — role `super-admin` (semua permission),
  `editor`, `author`
- `database/seeders/DefaultModulesSeeder` — registrasi 6 modul pesantren ke `content_modules`
  (otomatis memicu trigger auto-permission)
- `App\Core\Queue\DatabaseQueue` — job queue berbasis tabel `jobs` (tanpa Redis), dijalankan lewat
  `php bin/console queue:work`
- `App\Media\MediaManager` — upload (validasi, simpan ke `storage/uploads/YYYY/MM/`, insert row `media`),
  `delete()` yang **menolak menghapus file yang masih terpakai** (cek `media_usages` dulu),
  `attachUsage()`/`detachUsage()`/`replaceUsage()` untuk tracking "Used In"
- `App\Media\Jobs\GenerateMediaVariantsJob` — job async (GD) yang generate thumbnail (300px),
  medium (800px), dan copy `.webp` dari original; dipicu otomatis oleh `MediaManager::upload()`
  lewat queue, bukan blocking request
- Trigger `trg_media_usage_increment` / `_decrement` — `media.usage_count` selalu sinkron dengan
  jumlah baris `media_usages` tanpa perlu logika manual di setiap tempat yang attach/detach
- `App\Http\Controllers\MediaController` — endpoint `GET/POST /admin/media`,
  `DELETE /admin/media/{id}` (return 409 kalau masih dipakai), `GET /admin/media/{id}/usages`
- `App\Content\ArticleManager` — CRUD artikel lengkap: slug otomatis + unik (dengan suffix
  `-1`, `-2`, dst kalau bentrok), sync kategori (`article_categories`), dan **otomatis
  memanggil `MediaManager::attachUsage()`/`replaceUsage()`/`detachUsage()`** setiap kali
  `featured_media_id` diset/diganti/artikel dihapus — jadi "Used In" tracking Media Manager
  langsung akurat tanpa kerja tambahan
- `App\Http\Controllers\ArticleController` — endpoint admin lengkap (`GET/POST/PUT/DELETE
  /admin/articles`, `POST /admin/articles/{id}/publish|unpublish`) plus satu endpoint publik
  `GET /artikel/{slug}` (hanya menampilkan artikel `status = published`)
- `App\Content\PageManager` + `PageController` — CRUD Static Pages (`/admin/pages`), pola sama
  seperti Articles (slug unik + wiring featured image ke MediaManager)
- `App\Menu\MenuResolver` — **inti Target Provider**: mengubah `target_type`/`target_id` sebuah
  `menu_items` jadi URL nyata dengan lookup langsung ke tabel terkait (article/category/page/
  archive modul/module content/system/custom_url). Kalau target sudah dihapus, resolver
  mengembalikan `'#'` alih-alih error, supaya render menu tidak pernah crash.
- `App\Menu\MenuManager` — CRUD menu & menu item + `getResolvedTree()` yang membangun struktur
  nested (parent/child) dengan `url` sudah ter-resolve, siap dipakai theme layer
- `App\Content\PublicContentResolver` — **arah sebaliknya**: dari path request masuk
  (`/halaman/{slug}`, `/kategori/{slug}`, atau base_route modul apa pun seperti `/agenda` &
  `/agenda/{slug}`) balik jadi konten. Dipasang sebagai `Router::fallback()`, jadi modul baru
  yang didaftarkan lewat `content_modules` **langsung punya archive & detail page** tanpa
  nambah satu baris route pun
- `GET /menus/{slug}` — endpoint publik untuk ambil pohon menu ter-resolve (mis. `/menus/main-nav`)
- `App\Content\ModuleContentManager` + `ModuleContentController` — **satu implementasi generik**
  untuk keenam modul pesantren (Agenda, Prestasi, Pengajar, Galeri, Fasilitas, Alumni), didorong
  sepenuhnya oleh baris `content_modules`. Endpoint `/admin/modules/{module}/items` bekerja untuk
  modul mana pun tanpa kode tambahan — modul ke-7 di masa depan cukup didaftarkan lewat
  `content_modules` (seperti `DefaultModulesSeeder`), nol perubahan route/controller.
- `App\Http\Middleware\ModulePermissionMiddleware` — permission key dibangun dinamis dari segmen
  URL `{module}` (`module.agenda.create`, `module.alumni.publish`, dst), konsisten dengan trigger
  auto-permission yang sudah kita buat di migration 0007.
- Kategori per-modul (`module_categories`) juga sudah punya endpoint CRUD ringan di controller
  yang sama (`/admin/modules/{module}/categories`).
- `App\Core\View` — renderer PHP native minimal (extract + ob_start + include), tanpa compiler
  template terpisah, konsisten dengan keputusan stack "native PHP"
- `App\Theme\ThemeEngine` — pipeline **Template → Layout → Widget Area → Widgets** sungguhan:
  `render($templateKey, $data)` resolve layout dari tabel `templates`/`layouts`, render widget
  di tiap area (`widget_areas` + `widget_instances`), lalu gabungkan dengan konten utama.
  Fallback ke `full-width` kalau kombinasi theme/template belum di-seed.
- `App\Theme\WidgetInterface` + 2 widget contoh (`RecentArticlesWidget`, `TextHtmlWidget`) —
  pola untuk menambah widget baru: cukup buat class yang implement interface ini, daftarkan
  `provider`-nya di tabel `widgets`, tanpa sentuh `ThemeEngine`
- `resources/themes/pesantren-default/` — theme kerja penuh: 2 layout (`full-width`,
  `content-sidebar`), 6 template (`homepage`, `single-article`, `archive`, `page`,
  `single-module`, `404`), partial header/footer
- `database/seeders/ThemeSeeder` — seed theme aktif + layout/template/widget-area/widget default
  + 1 contoh widget instance (Artikel Terbaru di sidebar)
- **Semua endpoint publik sekarang render HTML sungguhan** (bukan JSON lagi): homepage, artikel,
  halaman statis, arsip kategori, arsip & detail modul — endpoint admin (CRUD) tetap JSON API
- `App\Theme\ThemeOptionsManager` + `ThemeOptionsController` — CRUD `theme_options` (warna, font),
  **terpisah dari pengaturan umum website** sesuai requirement awal. `ThemeEngine` otomatis
  mengubahnya jadi CSS custom properties (`--primary-color`, `--font-heading`, dst) yang di-inject
  ke `<head>` setiap halaman — ganti warna lewat `PUT /admin/theme-options` langsung terlihat
  di seluruh situs tanpa edit file theme
- **Caching Layer** (`App\Core\Cache`) — `CacheInterface` dengan 2 driver: `FileCache` (default,
  serialize ke `storage/cache/`) dan `NullCache` (matikan lewat `.env`: `CACHE_ENABLED=false`).
  Tiga level cache sesuai Core System:
  - **Full-page cache** — homepage, artikel, dan semua halaman lewat `PublicContentResolver`
    (`cachedPage()` di `public/index.php`), key per-URL, TTL dari `CACHE_TTL`
  - **Fragment cache** — tiap widget area (`ThemeEngine::renderWidgetArea()`) di-cache terpisah
  - **Query cache** — lookup Template→Layout yang dipanggil di tiap render juga di-cache
  - **Invalidasi**: sengaja **coarse-grained tapi selalu benar** — middleware global di
    `Application` men-flush seluruh cache setiap ada request non-GET yang sukses. Lebih aman
    untuk v1 daripada cache tagging granular; tinggal diganti tag-based kalau traffic besar.
  - `php bin/console cache:clear` untuk flush manual
- `App\Theme\WidgetManager` + `WidgetController` — CRUD `widget_instances` plus **endpoint
  reorder** (`POST /admin/widget-areas/{area}/reorder`, body `{"order":[id1,id2,id3]}`) yang
  menulis ulang `sort_order` sesuai urutan dikirim — backend siap dipanggil UI drag-drop (mis.
  SortableJS) di admin panel

**Catatan:** setiap kali modul baru ditambahkan setelah seed awal, jalankan ulang
`RolePermissionSeeder` (atau `db:seed`) agar role `editor`/`author` ikut mendapat
permission modul baru tsb — `super-admin` tidak perlu, karena RBAC memberi bypass
penuh untuk role tersebut.

## Yang baru dibangun: Admin Panel UI (server-rendered + vanilla JS)

- `resources/admin/layout.php` — shell bersama (sidebar + topbar), terpisah total dari
  `ThemeEngine` publik (sesuai desain: admin panel memang beda tampilan dari frontend)
- `resources/admin/pages/*.php` — partial konten per halaman: dashboard, articles, pages,
  media, menus, modules, widgets, theme-options — masing-masing di-include oleh layout
- `public/assets/admin/admin.js` — helper bersama: `Admin.request()` (fetch wrapper JSON),
  `Admin.alert()`, dan pengecekan `/admin/me` untuk sembunyikan menu sidebar sesuai
  permission user (role `editor`/`author` tidak akan melihat menu yang bukan haknya)
- `public/assets/admin/pages/*.js` — logic per halaman, semuanya **memanggil endpoint JSON
  yang sudah dibangun sebelumnya** (tidak ada logic bisnis baru, murni presentation layer):
  - **Articles/Pages**: list + filter + form create/edit + publish/unpublish + delete
  - **Media**: grid thumbnail, upload via `FormData`, delete dengan pesan error 409 "masih
    dipakai" langsung ditampilkan ke user
- **Menus**: pilih menu → kelola item — **target dipilih lewat autocomplete pencarian**
    (ketik judul artikel/halaman/kategori/nama modul, klik hasil), bukan input ID manual lagi.
    Tipe `system` pakai dropdown tetap (Login Admin/Cari/Sitemap), tipe `custom_url` tetap
    input teks bebas
  - **Modules**: dropdown pilih salah satu dari 6 modul pesantren → CRUD item generik
    (field `meta` sebagai JSON bebas sesuai `fields_schema` modul tsb)
  - **Widgets**: **drag-drop reorder sungguhan** pakai native HTML5 Drag & Drop API (tanpa
    library eksternal) yang memanggil `POST /admin/widget-areas/{area}/reorder`
  - **Theme Options**: form warna (color picker) + font, langsung terlihat di homepage
    setelah simpan
- Endpoint baru yang ditambahkan untuk mendukung UI ini: `GET /admin/me` (user + permission
  aktif), `GET /uploads/{path*}` (serve file media publik, dengan proteksi path traversal),
  `GET /admin/menus` & `GET /admin/menus/{id}/items` (list, sebelumnya cuma ada resolved
  tree), `GET /admin/modules` (daftar modul untuk dropdown)
- `Router` di-upgrade mendukung pola `{param*}` (greedy match, menangkap slash) khusus untuk
  path upload yang bersarang (`/uploads/2026/01/abc123.jpg`)

Akses panel di `http://localhost:8000/admin/panel` setelah login.

## Belum dibangun (pengembangan lanjutan, di luar fondasi awal)

- Security hardening: CSRF token untuk form admin, rate limiting percobaan login, dan
  validasi input yang lebih ketat di tiap form (saat ini validasi minimal, mengandalkan
  constraint database)
- Role & permission management belum ada UI-nya (assign role ke user masih lewat
  `php bin/console make:user` atau `Rbac::assignRole()` langsung)
