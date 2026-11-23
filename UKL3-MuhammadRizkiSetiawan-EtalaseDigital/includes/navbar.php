<?php
// Variabel dari halaman pemanggil: $base ('' atau '../'), $active ('galeri' | 'siswa' | 'users')
// Menampilkan sidebar dan MEMBUKA <div class="app-content"> (ditutup di includes/footer.php).
$base   = $base ?? '';
$active = $active ?? '';
?>
<div class="mobile-topbar">
    <button type="button" class="burger" id="sidebarToggle" aria-label="Buka menu"><i class="bi bi-list"></i></button>
    <span class="brand-mark"><img src="<?= $base ?>assets/img/logo.png" alt="Logo" class="brand-logo"></span>
    <span>Etalase <b>Riski</b></span>
</div>

<aside class="sidebar" id="sidebar">
    <a class="side-brand" href="<?= $base ?>index.php">
        <span class="brand-mark"><img src="<?= $base ?>assets/img/logo.png" alt="Logo Telkom Schools" class="brand-logo"></span>
        <span>
            <span class="brand-title d-block">Etalase <b>Riski</b></span>
            <span class="brand-sub">UKK LEVEL 3</span>
        </span>
    </a>

    <div class="side-label">Menu</div>
    <nav class="side-menu">
        <a class="side-link <?= $active === 'galeri' ? 'active' : '' ?>" href="<?= $base ?>index.php"><i class="bi bi-grid-fill"></i> Galeri Karya</a>
        <a class="side-link" href="<?= $base ?>project_tambah.php"><i class="bi bi-plus-square-fill"></i> Tambah Project</a>
        <?php if (is_staff()): ?>
            <a class="side-link <?= $active === 'siswa' ? 'active' : '' ?>" href="<?= $base ?>admin/siswa.php"><i class="bi bi-people-fill"></i> Kelola Siswa</a>
        <?php endif; ?>
        <?php if (is_super_admin()): ?>
            <a class="side-link <?= $active === 'users' ? 'active' : '' ?>" href="<?= $base ?>admin/users.php"><i class="bi bi-shield-lock-fill"></i> Kelola User</a>
        <?php endif; ?>
    </nav>

    <div class="side-user">
        <div class="who">
            <span class="avatar"><?= e(initial_of($_SESSION['nama'] ?? '')) ?></span>
            <div>
                <div class="name"><?= e($_SESSION['nama']) ?></div>
                <div class="role"><?= e(str_replace('_', ' ', $_SESSION['role'])) ?></div>
            </div>
        </div>
        <a href="<?= $base ?>auth/logout.php" class="btn btn-logout btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<script>
(function () {
    var sb = document.getElementById('sidebar'), bd = document.getElementById('sidebarBackdrop');
    function toggle(open) { sb.classList.toggle('open', open); bd.classList.toggle('show', open); }
    document.getElementById('sidebarToggle').addEventListener('click', function () { toggle(!sb.classList.contains('open')); });
    bd.addEventListener('click', function () { toggle(false); });
})();
</script>

<div class="app-content">
