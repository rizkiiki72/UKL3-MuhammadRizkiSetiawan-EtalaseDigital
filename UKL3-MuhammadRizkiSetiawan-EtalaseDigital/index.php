<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$cari = trim($_GET['cari'] ?? '');
$urut = $_GET['urut'] ?? 'terbaru';

// Urutan hanya dari daftar tetap (aman dari SQL injection)
$orderMap = [
    'terbaru' => 'projects.created_at DESC, projects.id DESC',
    'terlama' => 'projects.created_at ASC, projects.id ASC',
    'judul'   => 'projects.nama_project ASC',
];
if (!isset($orderMap[$urut])) {
    $urut = 'terbaru';
}
$order = $orderMap[$urut];

if ($cari !== '') {
    $stmt = $pdo->prepare("SELECT projects.*, users.nama AS pembuat
                           FROM projects
                           JOIN users ON projects.user_id = users.id
                           WHERE projects.nama_project LIKE :k1 OR users.nama LIKE :k2
                           ORDER BY $order");
    $stmt->execute([':k1' => "%$cari%", ':k2' => "%$cari%"]);
} else {
    $stmt = $pdo->query("SELECT projects.*, users.nama AS pembuat
                         FROM projects
                         JOIN users ON projects.user_id = users.id
                         ORDER BY $order");
}
$projects = $stmt->fetchAll();

// Angka ringkasan
$totalKarya = (int) $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$totalSiswa = (int) $pdo->query("SELECT COUNT(DISTINCT user_id) FROM projects")->fetchColumn();
$mine = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = :u");
$mine->execute([':u' => $_SESSION['user_id']]);
$karyaSaya = (int) $mine->fetchColumn();

$base   = '';
$active = 'galeri';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Karya - Etalase Karya Riski</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/popup.css">
</head>
<body>

<?php require __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="topbar">
        <div>
            <h2>Galeri Karya</h2>
            <p>Kumpulan karya dan pameran digital siswa. Klik kartu untuk melihat detail.</p>
        </div>
        <form method="GET" action="index.php" class="topbar-tools">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="cari" class="form-control" placeholder="Cari karya / pembuat..." value="<?= e($cari) ?>">
            </div>
            <select name="urut" class="form-select" onchange="this.form.submit()" aria-label="Urutkan">
                <option value="terbaru" <?= $urut === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                <option value="terlama" <?= $urut === 'terlama' ? 'selected' : '' ?>>Terlama</option>
                <option value="judul"   <?= $urut === 'judul'   ? 'selected' : '' ?>>Judul A-Z</option>
            </select>
            <button type="submit" class="btn btn-primary">Cari</button>
            <?php if ($cari !== ''): ?>
                <a href="index.php?urut=<?= e($urut) ?>" class="btn btn-outline-secondary" title="Reset pencarian"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-4"><div class="stat-card"><div class="icon-box"><i class="bi bi-images"></i></div><div><div class="num"><?= $totalKarya ?></div><div class="lbl">Total Karya</div></div></div></div>
        <div class="col-sm-4"><div class="stat-card"><div class="icon-box"><i class="bi bi-people"></i></div><div><div class="num"><?= $totalSiswa ?></div><div class="lbl">Siswa Berkarya</div></div></div></div>
        <div class="col-sm-4"><div class="stat-card"><div class="icon-box"><i class="bi bi-person-check"></i></div><div><div class="num"><?= $karyaSaya ?></div><div class="lbl">Karya Saya</div></div></div></div>
    </div>

    <?= render_flash() ?>

    <div class="result-info mb-3">
        <?php if ($cari !== ''): ?>
            Menampilkan <b><?= count($projects) ?></b> hasil untuk &ldquo;<b><?= e($cari) ?></b>&rdquo;
        <?php else: ?>
            Menampilkan <b><?= count($projects) ?></b> karya
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <?php if (count($projects) > 0): ?>
            <?php foreach ($projects as $p): ?>
                <?php $canManage = is_staff() || $_SESSION['user_id'] == $p['user_id']; ?>
                <div class="col-sm-6 col-xl-4">
                    <!-- Klik kartu => popup detail (data dibaca oleh JS di bawah) -->
                    <article class="project-card" tabindex="0" role="button"
                         data-id="<?= (int) $p['id'] ?>"
                         data-judul="<?= e($p['nama_project']) ?>"
                         data-pembuat="<?= e($p['pembuat']) ?>"
                         data-inisial="<?= e(initial_of($p['pembuat'])) ?>"
                         data-deskripsi="<?= e($p['deskripsi']) ?>"
                         data-link="<?= e($p['link_project']) ?>"
                         data-gambar="uploads/<?= e($p['gambar']) ?>"
                         data-tanggal="<?= e(date('d M Y', strtotime($p['created_at']))) ?>"
                         data-manage="<?= $canManage ? '1' : '0' ?>">
                        <img src="uploads/<?= e($p['gambar']) ?>" class="art-img" alt="Gambar <?= e($p['nama_project']) ?>">
                        <span class="art-date"><?= e(date('d M Y', strtotime($p['created_at']))) ?></span>
                        <div class="art-overlay">
                            <h5 class="art-title"><?= e($p['nama_project']) ?></h5>
                            <div class="art-author">
                                <span class="avatar avatar-sm"><?= e(initial_of($p['pembuat'])) ?></span>
                                <span><?= e($p['pembuat']) ?></span>
                            </div>
                            <p class="art-desc"><?= e(mb_strimwidth($p['deskripsi'], 0, 100, '...')) ?></p>
                            <span class="art-cta">Lihat detail <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-folder-x"></i>
                    <p class="mt-3 mb-2">Tidak ada project atau karya yang ditemukan.</p>
                    <?php if ($cari !== ''): ?>
                        <a href="index.php" class="btn btn-sm btn-outline-primary">Reset Pencarian</a>
                    <?php else: ?>
                        <a href="project_tambah.php" class="btn btn-sm btn-primary">Tambah Karya Pertama</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

<!-- Popup detail project (panel samping) -->
<div id="projectPopup" class="popup-overlay" aria-hidden="true">
    <div class="popup-content" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <button type="button" class="popup-close" data-popup-close aria-label="Tutup">&times;</button>

        <div class="popup-media">
            <img id="popupImg" class="popup-img" src="" alt="Detail Karya">
            <span id="popupCounter" class="popup-counter"></span>
        </div>

        <div class="popup-body">
            <div class="popup-author">
                <span id="popupAvatar" class="avatar"></span>
                <div>
                    <div id="popupAuthor" class="popup-author-name"></div>
                    <div id="popupDate" class="popup-meta"></div>
                </div>
            </div>

            <h2 id="popupTitle" class="popup-title"></h2>
            <p id="popupDesc" class="popup-desc"></p>

            <a id="popupLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary w-100">
                Buka Tautan Project <i class="bi bi-box-arrow-up-right ms-1"></i>
            </a>

            <div id="popupActions" class="popup-actions" style="display: none;">
                <a id="popupEdit" href="#" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
                <form id="popupDeleteForm" method="POST" action="project_hapus.php" onsubmit="return confirm('Hapus karya ini?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="popupDeleteId" value="">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
                </form>
            </div>

            <div class="popup-nav">
                <button type="button" id="popupPrev" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i> Sebelumnya</button>
                <button type="button" id="popupNext" class="btn btn-outline-secondary btn-sm">Berikutnya <i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/popup.js"></script>
<script>
(function () {
    var popup = document.getElementById('projectPopup');
    var cards = Array.prototype.slice.call(document.querySelectorAll('.project-card'));
    var current = -1;

    function openProject(index) {
        if (!cards.length) { return; }
        current = (index + cards.length) % cards.length;
        var d = cards[current].dataset;

        document.getElementById('popupImg').src = d.gambar;
        document.getElementById('popupTitle').textContent = d.judul;
        document.getElementById('popupDesc').textContent = d.deskripsi;
        document.getElementById('popupAvatar').textContent = d.inisial;
        document.getElementById('popupAuthor').textContent = d.pembuat;
        document.getElementById('popupDate').textContent = 'Diunggah ' + d.tanggal;
        document.getElementById('popupCounter').textContent = (current + 1) + ' / ' + cards.length;
        document.getElementById('popupLink').href = d.link;

        var actions = document.getElementById('popupActions');
        if (d.manage === '1') {
            document.getElementById('popupEdit').href = 'project_edit.php?id=' + encodeURIComponent(d.id);
            document.getElementById('popupDeleteId').value = d.id;
            actions.style.display = 'flex';
        } else {
            actions.style.display = 'none';
        }

        document.querySelector('.popup-nav').style.display = cards.length > 1 ? 'flex' : 'none';
        RiskiPopup.open(popup);
    }

    cards.forEach(function (card, i) {
        card.addEventListener('click', function () { openProject(i); });
        card.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); openProject(i); }
        });
    });

    document.getElementById('popupPrev').addEventListener('click', function () { openProject(current - 1); });
    document.getElementById('popupNext').addEventListener('click', function () { openProject(current + 1); });

    document.addEventListener('keydown', function (ev) {
        if (!popup.classList.contains('active')) { return; }
        if (ev.key === 'ArrowLeft')  { openProject(current - 1); }
        if (ev.key === 'ArrowRight') { openProject(current + 1); }
    });
})();
</script>

</body>
</html>
