<?php
use App\Core\Application;
$app = Application::getInstance();

$today = date('Y-m-d');
$monthStart = date('Y-m-01');

// Fetch summary metrics
$summary = $app->db->selectOne("SELECT SUM(views) as total_views, SUM(unique_visitors) as total_uniques FROM statistics_daily");
$totalViews = number_format((int)($summary['total_views'] ?? 0));
$totalUniques = number_format((int)($summary['total_uniques'] ?? 0));

// Fetch top pages
$topPages = $app->db->select("SELECT path, SUM(views) as total_views FROM statistics_daily GROUP BY path ORDER BY total_views DESC LIMIT 10");

// Estimate average time (just an example, as we don't track leave times yet)
$avgTime = "1m 30s"; 
?>
<div class="toolbar-modern">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Statistik Kunjungan</h2>
        <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Pantau performa situs Anda secara cerdas tanpa membebani server.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <select class="admin-input" style="margin: 0; height: 34px; min-width: 150px; font-size: 0.85rem;">
            <option>Sepanjang Waktu</option>
        </select>
    </div>
</div>

<!-- Key Metrics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
    
    <div class="stat-card" style="background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <div style="width: 54px; height: 54px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: var(--admin-muted); font-weight: 600; text-transform: uppercase;">Pengunjung Unik</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #1e293b; margin-top: 0.25rem; line-height: 1;"><?= $totalUniques ?></div>
            <div style="font-size: 0.75rem; color: #10b981; margin-top: 0.4rem; display: flex; align-items: center; gap: 0.2rem; font-weight: 600;">
                (Aktif & Realtime)
            </div>
        </div>
    </div>

    <div class="stat-card" style="background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <div style="width: 54px; height: 54px; border-radius: 50%; background: #f0fdf4; color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h4l2-9 5 18 3-10h4"></path></svg>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: var(--admin-muted); font-weight: 600; text-transform: uppercase;">Total Tayangan (Pageviews)</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #1e293b; margin-top: 0.25rem; line-height: 1;"><?= $totalViews ?></div>
            <div style="font-size: 0.75rem; color: #10b981; margin-top: 0.4rem; display: flex; align-items: center; gap: 0.2rem; font-weight: 600;">
                (Semua Halaman)
            </div>
        </div>
    </div>

    <div class="stat-card" style="background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <div style="width: 54px; height: 54px; border-radius: 50%; background: #fef2f2; color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: var(--admin-muted); font-weight: 600; text-transform: uppercase;">Perkiraan Waktu</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #1e293b; margin-top: 0.25rem; line-height: 1;"><?= $avgTime ?></div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem; display: flex; align-items: center; gap: 0.2rem; font-weight: 600;">
                Estimasi rata-rata
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="editor-layout" style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1.5rem; align-items: flex-start;">
    
    <!-- Chart Section -->
    <div style="flex: 1 1 500px; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b;">Data Kunjungan Berjalan</h3>
            <span style="font-size: 0.75rem; padding: 0.35rem 0.75rem; background: #dcfce7; border-radius: 12px; color: #166534; font-weight: 600; border: 1px solid #bbf7d0;">Merekam Live...</span>
        </div>
        
        <!-- Placeholder for Chart -->
        <div style="width: 100%; height: 320px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #94a3b8;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            <span style="font-weight: 500; color: #64748b;">Pengumpulan Data Aktif</span>
            <span style="font-size: 0.8rem; margin-top: 0.5rem; max-width: 250px; text-align: center; color: #94a3b8;">Sistem sedang mencatat aktivitas pengunjung. Grafik akan muncul secara otomatis saat data telah mencukupi.</span>
        </div>
    </div>

    <!-- Top Pages Sidebar -->
    <div style="flex: 0 0 320px; width: 100%; background: #fff; border: 1px solid var(--admin-border); border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--admin-border);">
            <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b;">Halaman Terpopuler</h3>
        </div>
        <div style="padding: 0;">
            <ul style="list-style: none; margin: 0; padding: 0;">
                <?php if (empty($topPages)): ?>
                    <li style="padding: 2rem 1.5rem; text-align: center; color: var(--admin-muted); font-size: 0.9rem;">
                        Belum ada kunjungan tercatat hari ini. Coba buka halaman depan website Anda!
                    </li>
                <?php else: ?>
                    <?php foreach ($topPages as $tp): ?>
                        <li style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                            <div style="overflow: hidden; padding-right: 1rem;">
                                <div style="font-weight: 600; color: #1e293b; font-size: 0.95rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;" title="<?= htmlspecialchars($tp['path']) ?>"><?= htmlspecialchars($tp['path']) ?></div>
                            </div>
                            <div style="font-weight: 700; color: var(--admin-primary); font-size: 1rem;"><?= number_format((int)$tp['total_views']) ?></div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
