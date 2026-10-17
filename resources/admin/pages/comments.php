<?php
use App\Core\Application;
$app = Application::getInstance();

$filter = $_GET['filter'] ?? 'all';
$whereClause = "";
$params = [];

if ($filter === 'pending') {
    $whereClause = "WHERE c.status = 'pending'";
} elseif ($filter === 'approved') {
    $whereClause = "WHERE c.status = 'approved'";
} elseif ($filter === 'spam') {
    $whereClause = "WHERE c.status = 'spam'";
}

$query = "
    SELECT c.*, a.title as article_title 
    FROM comments c 
    JOIN articles a ON c.article_id = a.id 
    $whereClause
    ORDER BY c.created_at DESC
";
$comments = $app->db->select($query, $params);

// Get counts
$pendingCount = $app->db->selectOne("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'")['count'] ?? 0;
?>
<div class="toolbar-modern">
    <div>
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Interaksi & Komentar</h2>
        <p style="margin: 0.25rem 0 0 0; color: var(--admin-muted); font-size: 0.9rem;">Kelola tanggapan pengunjung dan interaksi pada artikel Anda.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <select class="admin-input" style="margin: 0; height: 34px; min-width: 150px; font-size: 0.85rem;" onchange="window.location.href='?filter='+this.value">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Semua Komentar</option>
            <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Menunggu Moderasi (<?= $pendingCount ?>)</option>
            <option value="approved" <?= $filter === 'approved' ? 'selected' : '' ?>>Disetujui</option>
            <option value="spam" <?= $filter === 'spam' ? 'selected' : '' ?>>Spam</option>
        </select>
    </div>
</div>

<style>
.comment-card {
    background: #fff;
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    gap: 1.5rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    transition: all 0.2s ease;
}
.comment-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-color: #cbd5e1;
}
.comment-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #f1f5f9;
    color: var(--admin-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 700;
    flex-shrink: 0;
}
.comment-content {
    flex: 1;
}
.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}
.comment-author {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
}
.comment-meta {
    font-size: 0.8rem;
    color: var(--admin-muted);
    margin-top: 0.2rem;
}
.comment-text {
    color: #334155;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0.5rem 0 1rem 0;
}
.comment-article {
    font-size: 0.85rem;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #f8fafc;
    padding: 0.4rem 0.75rem;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}
.comment-actions {
    display: flex;
    gap: 0.5rem;
}
.btn-comment {
    padding: 0.4rem 0.75rem;
    font-size: 0.85rem;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.btn-approve {
    background: #dcfce7;
    color: #166534;
}
.btn-approve:hover { background: #bbf7d0; }
.btn-reply {
    background: #f1f5f9;
    color: #475569;
}
.btn-reply:hover { background: #e2e8f0; color: #1e293b; }
.btn-spam {
    background: #fef2f2;
    color: #ef4444;
}
.btn-spam:hover { background: #fee2e2; }

/* Status Badges */
.badge-pending {
    background: #fef9c3;
    color: #854d0e;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}
.badge-approved {
    background: #dcfce7;
    color: #166534;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}
.badge-spam {
    background: #fee2e2;
    color: #b91c1c;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}
</style>

<div style="margin-top: 1.5rem;">

    <?php if (empty($comments)): ?>
        <div style="text-align: center; padding: 3rem; background: #fff; border: 1px solid var(--admin-border); border-radius: 12px; color: var(--admin-muted);">
            Tidak ada komentar yang ditemukan.
        </div>
    <?php else: ?>
        <?php foreach ($comments as $c): ?>
            <div class="comment-card" style="<?= $c['status'] === 'pending' ? 'border-left: 4px solid #eab308;' : '' ?>">
                <div class="comment-avatar" style="<?= $c['status'] === 'approved' ? 'background: #e0e7ff; color: #4338ca;' : '' ?>">
                    <?= strtoupper(substr($c['author_name'], 0, 1)) ?>
                </div>
                <div class="comment-content">
                    <div class="comment-header">
                        <div>
                            <div class="comment-author">
                                <?= htmlspecialchars($c['author_name']) ?> 
                                <?php if ($c['status'] === 'pending'): ?>
                                    <span class="badge-pending" style="margin-left: 0.5rem;">Menunggu</span>
                                <?php elseif ($c['status'] === 'approved'): ?>
                                    <span class="badge-approved" style="margin-left: 0.5rem;">Disetujui</span>
                                <?php elseif ($c['status'] === 'spam'): ?>
                                    <span class="badge-spam" style="margin-left: 0.5rem;">Spam</span>
                                <?php endif; ?>
                            </div>
                            <div class="comment-meta"><?= htmlspecialchars($c['author_email']) ?> • <?= date('d M Y H:i', strtotime($c['created_at'])) ?></div>
                        </div>
                    </div>
                    <p class="comment-text"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                    <div class="comment-article">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <?= htmlspecialchars($c['article_title']) ?>
                    </div>
                </div>
                <div class="comment-actions" style="flex-direction: column; align-items: flex-end;">
                    <?php if ($c['status'] !== 'approved'): ?>
                        <button class="btn-comment btn-approve" style="width: 100%;" onclick="updateCommentStatus(<?= $c['id'] ?>, 'approved')">Setujui</button>
                    <?php else: ?>
                        <button class="btn-comment" style="width: 100%; background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2;" onclick="updateCommentStatus(<?= $c['id'] ?>, 'pending')">Tarik Izin</button>
                    <?php endif; ?>
                    
                    <button class="btn-comment btn-reply" style="width: 100%; margin-top: 0.25rem;" onclick="replyComment(<?= $c['id'] ?>)">Balas</button>

                    <?php if ($c['status'] !== 'spam'): ?>
                        <button class="btn-comment btn-spam" style="width: 100%; margin-top: auto;" onclick="updateCommentStatus(<?= $c['id'] ?>, 'spam')">Spam</button>
                    <?php endif; ?>
                    
                    <button class="btn-comment" style="width: 100%; background: #f1f5f9; color: #ef4444; border: 1px solid #e2e8f0; margin-top: 0.25rem;" onclick="deleteComment(<?= $c['id'] ?>)">Hapus</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
async function updateCommentStatus(id, status) {
    try {
        const res = await fetch(`/admin/api/comments/${id}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status })
        });
        if (res.ok) {
            window.location.reload();
        } else {
            alert('Gagal mengubah status');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
}

async function deleteComment(id) {
    if (!confirm('Yakin ingin menghapus komentar ini secara permanen?')) return;
    
    try {
        const res = await fetch(`/admin/api/comments/${id}`, {
            method: 'DELETE'
        });
        if (res.ok) {
            window.location.reload();
        } else {
            alert('Gagal menghapus komentar');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
}

async function replyComment(id) {
    const text = prompt('Tulis balasan Anda (sebagai Admin):');
    if (!text) return;

    try {
        const res = await fetch(`/admin/api/comments/${id}/reply`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ content: text })
        });
        if (res.ok) {
            alert('Balasan berhasil dikirim! Komentar pengunjung juga otomatis disetujui.');
            window.location.reload();
        } else {
            const data = await res.json();
            alert(data.error || 'Gagal mengirim balasan');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
}

// Add query params parsing
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'all';
    document.querySelector('select.admin-input').value = filter;
});
</script>
