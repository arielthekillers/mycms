<?php
use App\Core\Application;
$app = Application::getInstance();

$user = $app->auth->user();
$userName = htmlspecialchars($user['name'] ?? 'Administrator');

// Stats Queries
$statArticles = $app->db->selectOne("SELECT COUNT(*) as c FROM articles WHERE status='published'")['c'] ?? 0;
$statDrafts = $app->db->selectOne("SELECT COUNT(*) as c FROM articles WHERE status='draft'")['c'] ?? 0;
$statPages = $app->db->selectOne("SELECT COUNT(*) as c FROM pages WHERE status='published'")['c'] ?? 0;
$statComments = $app->db->selectOne("SELECT COUNT(*) as c FROM comments WHERE status='pending'")['c'] ?? 0;
$statViews = $app->db->selectOne("SELECT SUM(views) as c FROM statistics_daily")['c'] ?? 0;
$statUniques = $app->db->selectOne("SELECT SUM(unique_visitors) as c FROM statistics_daily")['c'] ?? 0;

// Recent Data
$recentArticles = $app->db->select("SELECT title, status, created_at FROM articles ORDER BY created_at DESC LIMIT 4");
$recentComments = $app->db->select("SELECT author_name, content, created_at, status FROM comments ORDER BY created_at DESC LIMIT 3");
?>

<div style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0 0 0.25rem 0;">Selamat Datang, <?= $userName ?> 👋</h2>
    <p style="color: var(--admin-muted); font-size: 0.95rem; margin: 0;">Berikut adalah ringkasan aktivitas situs Anda hari ini.</p>
</div>

<style>
/* Dashboard Specific Styles */
.dash-grid-top {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.stat-card-pro {
    border: none;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card-pro:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
}
.stat-card-blue { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color: #1e3a8a; }
.stat-card-green { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); color: #14532d; }
.stat-card-purple { background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); color: #4c1d95; }
.stat-card-yellow { background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%); color: #713f12; }

.stat-icon-wrapper {
    width: 54px;
    height: 54px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-icon-blue { background: rgba(59, 130, 246, 0.15); color: #2563eb; }
.stat-icon-green { background: rgba(16, 185, 129, 0.15); color: #059669; }
.stat-icon-purple { background: rgba(139, 92, 246, 0.15); color: #7c3aed; }
.stat-icon-yellow { background: rgba(234, 179, 8, 0.15); color: #d97706; }

.stat-content {
    display: flex;
    flex-direction: column;
}
.stat-value {
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 0.2rem;
}
.stat-label {
    font-size: 0.8rem;
    font-weight: 700;
    opacity: 0.75;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dash-grid-main {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 1024px) {
    .dash-grid-main { grid-template-columns: 1fr; }
}

.dash-panel {
    background: #fff;
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    overflow: hidden;
}
.dash-panel-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--admin-border);
    background: #fafafa;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.dash-panel-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.dash-panel-body {
    padding: 1.5rem;
}

.quick-action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}
.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1.25rem 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    color: #334155;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s;
}
.quick-action-btn:hover {
    background: #fff;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    color: var(--admin-primary);
}

.list-item {
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}
.list-item:last-child { border-bottom: none; padding-bottom: 0; }
.list-item-icon {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #64748b;
}
.list-item-content { flex: 1; min-width: 0; }
.list-item-title {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
    margin-bottom: 0.2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.list-item-meta {
    font-size: 0.8rem;
    color: #94a3b8;
}
.status-badge {
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}
</style>

<!-- Top Stats Grid -->
<div class="dash-grid-top">
    <!-- Tayangan -->
    <div class="stat-card-pro stat-card-blue">
        <div class="stat-icon-wrapper stat-icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h4l2-9 5 18 3-10h4"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format((int)$statViews) ?></div>
            <div class="stat-label">Total Tayangan</div>
        </div>
    </div>
    
    <!-- Pengunjung -->
    <div class="stat-card-pro stat-card-green">
        <div class="stat-icon-wrapper stat-icon-green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format((int)$statUniques) ?></div>
            <div class="stat-label">Pengunjung Unik</div>
        </div>
    </div>

    <!-- Artikel -->
    <div class="stat-card-pro stat-card-purple">
        <div class="stat-icon-wrapper stat-icon-purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format((int)$statArticles) ?></div>
            <div class="stat-label">Artikel Terbit</div>
        </div>
    </div>

    <!-- Komentar Pending -->
    <div class="stat-card-pro stat-card-yellow">
        <div class="stat-icon-wrapper stat-icon-yellow">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format((int)$statComments) ?></div>
            <div class="stat-label">Komentar Pending</div>
        </div>
    </div>
</div>

<div class="dash-grid-main">
    
    <!-- Left Column -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Quick Actions -->
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    Aksi Cepat
                </h3>
            </div>
            <div class="dash-panel-body">
                <div class="quick-action-grid">
                    <a href="/admin/panel/articles" class="quick-action-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        Tulis Artikel
                    </a>
                    <a href="/admin/panel/pages" class="quick-action-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        Buat Halaman
                    </a>
                    <a href="/admin/panel/themes" class="quick-action-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                        Ganti Tema
                    </a>
                    <a href="/admin/panel/settings" class="quick-action-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        Pengaturan
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Comments -->
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Komentar Terbaru
                </h3>
                <a href="/admin/panel/comments" style="font-size: 0.85rem; color: var(--admin-primary); text-decoration: none; font-weight: 600;">Lihat Semua &rarr;</a>
            </div>
            <div class="dash-panel-body" style="padding-top: 0.5rem;">
                <?php if (empty($recentComments)): ?>
                    <p style="color: var(--admin-muted); text-align: center; padding: 2rem 0; margin: 0; font-size: 0.9rem;">Belum ada komentar.</p>
                <?php else: ?>
                    <?php foreach ($recentComments as $c): ?>
                        <div class="list-item">
                            <div class="list-item-icon" style="<?= $c['status'] === 'pending' ? 'background: #fef9c3; color: #a16207;' : 'background: #f0fdf4; color: #15803d;' ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div class="list-item-content">
                                <div class="list-item-title">
                                    <?= htmlspecialchars($c['author_name']) ?>
                                    <?php if ($c['status'] === 'pending'): ?>
                                        <span class="status-badge" style="background: #fef9c3; color: #854d0e; margin-left: 0.25rem;">Pending</span>
                                    <?php endif; ?>
                                </div>
                                <div class="list-item-meta" style="color: #334155; margin-bottom: 0.25rem;"><?= htmlspecialchars(substr($c['content'], 0, 80)) ?>...</div>
                                <div class="list-item-meta"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Right Column -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Draft & Recent Articles -->
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Aktivitas Artikel
                </h3>
            </div>
            <div class="dash-panel-body" style="padding-top: 0.5rem;">
                <?php if (empty($recentArticles)): ?>
                    <p style="color: var(--admin-muted); text-align: center; padding: 2rem 0; margin: 0; font-size: 0.9rem;">Belum ada artikel.</p>
                <?php else: ?>
                    <?php foreach ($recentArticles as $a): ?>
                        <div class="list-item">
                            <div class="list-item-content">
                                <div class="list-item-title">
                                    <a href="/admin/panel/articles" style="color: inherit; text-decoration: none;"><?= htmlspecialchars($a['title']) ?></a>
                                </div>
                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.25rem;">
                                    <?php if ($a['status'] === 'published'): ?>
                                        <span class="status-badge" style="background: #dcfce7; color: #166534;">Published</span>
                                    <?php else: ?>
                                        <span class="status-badge" style="background: #f1f5f9; color: #475569;">Draft</span>
                                    <?php endif; ?>
                                    <span class="list-item-meta"><?= date('d M Y', strtotime($a['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if ($statDrafts > 0): ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #cbd5e1; text-align: center;">
                        <a href="/admin/panel/articles" style="font-size: 0.85rem; color: #eab308; font-weight: 700; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.25rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            Anda memiliki <?= $statDrafts ?> draf yang belum terbit
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Info -->
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    Sistem
                </h3>
            </div>
            <div class="dash-panel-body" style="padding: 1rem 1.5rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                    <span style="color: #64748b; font-size: 0.9rem;">Versi CMS</span>
                    <span style="font-weight: 600; color: #1e293b; font-size: 0.9rem;">2.1.0-pro</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                    <span style="color: #64748b; font-size: 0.9rem;">Versi PHP</span>
                    <span style="font-weight: 600; color: #1e293b; font-size: 0.9rem;"><?= phpversion() ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b; font-size: 0.9rem;">Memori Server</span>
                    <span style="font-weight: 600; color: #10b981; font-size: 0.9rem;">Normal & Ringan</span>
                </div>
            </div>
        </div>

    </div>
</div>
