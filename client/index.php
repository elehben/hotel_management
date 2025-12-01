<?php
include "client.php";
// Jika user sudah login, siapkan data list untuk modal tambah reservasi
if (isset($_COOKIE['jwt'])) {
    $list_kamar = $abc->get_list_kamar($_COOKIE['jwt']);
    $list_service = $abc->get_list_service($_COOKIE['jwt']);
    $list_tamu = $abc->get_list_tamu($_COOKIE['jwt']);
}
?>
<?php if (!isset($_COOKIE['jwt'])) { ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sistem Reservasi Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link type="image/png" sizes="32x32" rel="icon" href="img/icons8-hotel-55.png">
    <style>
        body { background: linear-gradient(135deg,#eef2f5,#f8fafc); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial; height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { width:100%; max-width:420px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.08); padding:28px; }
        .brand { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
        .brand img{ width:48px; height:48px; }
        .brand h1{ font-size:1.25rem; margin:0; color:#1a3a52; }
        .form-control:focus { box-shadow:0 0 0 3px rgba(26,58,82,0.06); border-color:#1a3a52; }
        .login-actions { display:flex; justify-content:space-between; align-items:center; gap:12px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <img src="img/icons8-hotel-55.png" alt="logo">
            <h1>Sistem Management Hotel</h1>
        </div>
        <p class="text-muted">Silakan masuk untuk melanjutkan.</p>
        <form method="POST" action="proses.php" novalidate>
            <input type="hidden" name="aksi" value="login" />
            <div class="mb-3">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="addon-wrapping"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="addon-wrapping"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
            </div>
            <hr>
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">Masuk</button>
            </div>
        </form>
    </div>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php exit; } ?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sistem Reservasi Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.min.css" rel="stylesheet">        
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link type="image/png" sizes="32x32" rel="icon" href="img/icons8-hotel-55.png">
	<!-- <link type="image/png" sizes="32x32" rel="icon" href="https://img.icons8.com/external-smashingstocks-isometric-smashing-stocks/55/external-hotel-travel-summer-vacation-smashingstocks-isometric-smashing-stocks.png"> -->

    <style>
        /* Menimpa settingan default Bootstrap 2 */
        .form-horizontal .control-label {
            text-align: left !important;
            width: 120px; /* Sesuaikan lebar label jika perlu */
        }
        
        /* Menyesuaikan margin input agar pas dengan label yang rata kiri */
        .form-horizontal .controls {
            margin-left: 140px; /* Harus lebih besar sedikit dari width label */
        }
        
        /* Khusus tampilan mobile, reset margin */
        @media (max-width: 767px) {
            .form-horizontal .control-label {
                width: auto;
                margin-bottom: 5px;
            }
            .form-horizontal .controls {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-light" data-bs-theme="light">
        <div class="container-fluid">
                <a class="navbar-brand" href="?page=home">Hotel Management</a>
                <div class="d-flex ms-auto align-items-center">
                        <?php if (isset($_COOKIE['jwt'])) { ?>
                                <span class="me-3"> <i class="bi bi-person"></i> Halo, <strong><?=isset($_COOKIE['username'])?htmlspecialchars($_COOKIE['username']):'User'?></strong></span>
                                <a class="btn btn-outline-danger btn-sm" href="proses.php?aksi=logout" onclick="return confirm('Logout?')"><i class="bi bi-power"></i> Logout</a>
                        <?php } ?>
                </div>
        </div>
</nav>

<!-- Sidebar Static -->
<div id="sidebar" class="sidebar expanded">
    <div class="sidebar-header d-flex align-items-center justify-content-between px-3 py-2">
        <span class="fw-bold">Menu</span>
        <button class="btn btn-sm btn-light" id="sidebarToggle" type="button" title="Toggle sidebar"><i class="bi bi-list"></i></button>
    </div>
    <div class="sidebar-body">
        <?php if (isset($_COOKIE['jwt'])) { ?>
            <ul class="nav nav-pills flex-column">
                <li class="nav-item"><a class="nav-link" href="?page=home"><i class="bi bi-house"></i> <span class="sidebar-label">Home</span></a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#reservasiModal"><i class="bi bi-plus-circle"></i> <span class="sidebar-label">Buat Reservasi</span></a></li>
                <li class="nav-item"><a class="nav-link" href="?page=data-tamu"><i class="bi bi-people"></i> <span class="sidebar-label">Data Tamu</span></a></li>
                <li class="nav-item"><a class="nav-link" href="?page=data-kamar"><i class="bi bi-door-open"></i> <span class="sidebar-label">Data Kamar</span></a></li>
                <li class="nav-item"><a class="nav-link" href="?page=data-layanan"><i class="bi bi-list-check"></i> <span class="sidebar-label">Data Layanan</span></a></li>
                <li class="nav-item"><a class="nav-link" href="?page=data-transaksi"><i class="bi bi-receipt"></i> <span class="sidebar-label">List Transaksi</span></a></li>
            </ul>
        <?php } ?>
    </div>
</div>

<style>
    body {
        padding-left: 260px;
        transition: padding-left 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    body.no-transition, body.no-transition * {
        transition: none !important;
    }
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 260px;
        background: #f8f9fa;
        border-right: 1px solid #dee2e6;
        z-index: 1040;
        transition: width 0.3s cubic-bezier(0.4,0,0.2,1), background 0.2s;
        overflow-x: hidden;
    }
    body.no-transition .sidebar,
    body.no-transition .sidebar *,
    body.no-transition .sidebar-label {
        transition: none !important;
    }
    .sidebar .sidebar-header {
        height: 56px;
        border-bottom: 1px solid #dee2e6;
        transition: padding 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .sidebar .sidebar-body {
        padding: 1rem 0.5rem;
        transition: padding 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .sidebar .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px;
        border-radius: 6px;
        color: #333;
        font-size: 1rem;
        transition: background 0.15s, color 0.15s, padding 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .sidebar .nav-link.active, .sidebar .nav-link:hover {
        background: #e9ecef;
        color: #0d6efd;
    }

    .sidebar-label {
        display: inline-block;
        opacity: 1;
        max-width: 200px;
        transition: opacity 0.2s, max-width 0.3s cubic-bezier(0.4,0,0.2,1);
        white-space: nowrap;
        overflow: hidden;
    }
    .sidebar.collapsed {
        align-items: center;
        width: 70px;
        transition: width 0.3s cubic-bezier(0.4,0,0.2,1), background 0.2s;
    }
    .sidebar.collapsed .sidebar-label {
        opacity: 0;
        max-width: 0;
        overflow: hidden;
        visibility: hidden;
        transition: opacity 0.15s, max-width 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .sidebar.collapsed .sidebar-header span {
        opacity: 0;
        max-width: 0;
        transition: opacity 0.15s, max-width 0.3s cubic-bezier(0.4,0,0.2,1);
        display: inline-block;
        overflow: hidden;
        white-space: nowrap;
    }
    .sidebar.collapsed .sidebar-header {
        justify-content: center !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    @media (max-width: 767px) {
        /* Keep sidebar visible on mobile. Allow collapse to icons-only but
           don't hide the sidebar entirely. */
        .sidebar {
            left: 0;
            width: 260px;
            transition: width 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        .sidebar.collapsed {
            width: 70px;
        }
        .sidebar.expanded {
            left: 0;
        }
        body {
            /* keep content shifted so sidebar remains visible */
            padding-left: 260px;
            transition: padding-left 0.3s cubic-bezier(0.4,0,0.2,1);
        }
    }
</style>

<script>
    // Hilangkan animasi saat halaman load/refresh
    document.body.classList.add('no-transition');
    window.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.body.classList.remove('no-transition');
        }, 10);
    });

    // Sidebar collapse/expand
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    function setSidebarState(collapsed) {
        document.body.classList.remove('no-transition');
        if (collapsed) {
            sidebar.classList.add('collapsed');
            document.body.style.paddingLeft = '70px';
            localStorage.setItem('sidebar-collapsed', '1');
        } else {
            sidebar.classList.remove('collapsed');
            document.body.style.paddingLeft = '260px';
            localStorage.setItem('sidebar-collapsed', '0');
        }
    }
    sidebarToggle.addEventListener('click', function() {
        setSidebarState(!sidebar.classList.contains('collapsed'));
    });
    // Responsive: show/hide sidebar on mobile
    function handleResize() {
        // Keep sidebar visible on mobile: mirror desktop behavior but allow
        // collapsed state to be respected on small screens as well.
        sidebar.classList.add('expanded');
        if (localStorage.getItem('sidebar-collapsed') === '1') {
            sidebar.classList.add('collapsed');
            document.body.style.paddingLeft = '70px';
        } else {
            sidebar.classList.remove('collapsed');
            document.body.style.paddingLeft = '260px';
        }
    }
    window.addEventListener('resize', handleResize);
    // Initial state
    if (window.innerWidth >= 768) {
        sidebar.classList.add('expanded');
        if (localStorage.getItem('sidebar-collapsed') === '1') {
            sidebar.classList.add('collapsed');
            document.body.style.paddingLeft = '70px';
        }
    }
    handleResize();

    // Set active sidebar item based on current page
    function setActiveSidebar() {
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = urlParams.get('page');
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        
        navLinks.forEach(function(link) {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href && href.includes('page=')) {
                const linkPage = new URLSearchParams(href.split('?')[1]).get('page');
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            }
        });
    }
    setActiveSidebar();

    // Add click event to set active state
    document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
        link.addEventListener('click', function() {
            document.querySelectorAll('.sidebar .nav-link').forEach(function(l) {
                l.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
</script>

<div class="container">
<fieldset>

<?php   
// ==================================================================
// MODUL DATA TAMU
// ==================================================================
if (isset($_GET['page']) && $_GET['page']=='data-tamu' && isset($_COOKIE['jwt'])) { 
    $data = $abc->get_general($_COOKIE['jwt'], 'tampil_tamu');
?>
<div class="mt-5 mb-4 rounded-3">
    <legend>Data Tamu <a href="?page=tambah-tamu" class="btn btn-primary btn-sm float-end"><i class="bi bi-plus me-1"></i> Tambah Tamu</a></legend>
    <div class="rounded-3 overflow-hidden">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <td>No</td>
                <td>ID (KTP)</td>
                <td>Nama Lengkap</td>
                <td>Email</td>
                <td>No HP</td>
                <td>Aksi</td>
            </tr>
        </thead>
        <tbody>
        <?php $no=1; foreach($data as $r) { ?>
            <tr>
                <td><?=$no++?></td>
                <td><?=$r->guest_id?></td>
                <td><?=$r->full_name?></td>
                <td><?=$r->email?></td>
                <td><?=$r->phone_number?></td>
                <td style="text-align:center">
                    <div class="btn-group" role="group">
                        <a href="?page=ubah-tamu&id=<?=$r->guest_id?>" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                        <a href="proses.php?aksi=hapus_tamu&guest_id=<?=$r->guest_id?>&jwt=<?=$_COOKIE['jwt']?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus tamu ini?')"><i class="bi bi-x"></i></a>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    </div>
</div>
<?php 
} elseif (isset($_GET['page']) && ($_GET['page']=='tambah-tamu' || $_GET['page']=='ubah-tamu') && isset($_COOKIE['jwt'])) {
    $is_edit = ($_GET['page']=='ubah-tamu');
    $r = $is_edit ? $abc->get_general($_COOKIE['jwt'], 'detail_tamu', $_GET['id']) : null;
?>
<div class="mt-5 mb-4 rounded-3">
    <legend><?=$is_edit ? 'Ubah' : 'Tambah'?> Data Tamu</legend>
    <form method="POST" action="proses.php">
        <input type="hidden" name="aksi" value="<?=$is_edit ? 'ubah_tamu' : 'tambah_tamu'?>">
        <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>">
        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">ID Tamu (KTP)</label>
            <div class="col-sm-9">
                <input type="text" name="guest_id" class="form-control" value="<?=$r->guest_id?>" <?=$is_edit?'readonly':''?> required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Nama Lengkap</label>
            <div class="col-sm-9">
                <input type="text" name="full_name" class="form-control" value="<?=$r->full_name?>" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Email</label>
            <div class="col-sm-9">
                <input type="email" name="email" class="form-control" value="<?=$r->email?>" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">No HP</label>
            <div class="col-sm-9">
                <input type="text" name="phone_number" class="form-control" value="<?=$r->phone_number?>" required>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="?page=data-tamu" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?php 
// ==================================================================
// MODUL DATA KAMAR
// ==================================================================
} elseif (isset($_GET['page']) && $_GET['page']=='data-kamar' && isset($_COOKIE['jwt'])) { 
    $data = $abc->get_general($_COOKIE['jwt'], 'tampil_kamar');
?>
<div class="mt-5 mb-4 rounded-3">
    <legend>Data Kamar <a href="?page=tambah-kamar" class="btn btn-primary btn-sm float-end"><i class="bi bi-plus me-1"></i> Tambah Kamar</a></legend>
    <div class="rounded-3 overflow-hidden">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <td>No Kamar</td>
                <td>Tipe</td>
                <td>Harga Dasar</td>
                <td>Kapasitas</td>
                <td>Status</td>
                <td>Aksi</td>
            </tr>
        </thead>
        <tbody>
        <?php foreach($data as $r) { ?>
            <tr>
                <td><?=$r->room_id?></td>
                <td><?=$r->room_type_name?></td>
                <td style="text-align:right">Rp <?=number_format($r->base_price)?></td>
                <td><?=$r->max_occupancy?> Org</td>
                <?php $roomStatusClass = $r->status=='Available' ? 'bg-success' : 'bg-warning text-dark'; ?>
                <td><span class="badge <?=$roomStatusClass?>"><?=$r->status?></span></td>
                <td style="text-align:center">
                    <div class="btn-group" role="group">
                        <a href="?page=ubah-kamar&id=<?=$r->room_id?>" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                        <a href="proses.php?aksi=hapus_kamar&room_id=<?=$r->room_id?>&jwt=<?=$_COOKIE['jwt']?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kamar ini?')"><i class="bi bi-x"></i></a>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    </div>
</div>
<?php 
} elseif (isset($_GET['page']) && ($_GET['page']=='tambah-kamar' || $_GET['page']=='ubah-kamar') && isset($_COOKIE['jwt'])) {
    $is_edit = ($_GET['page']=='ubah-kamar');
    $r = $is_edit ? $abc->get_general($_COOKIE['jwt'], 'detail_kamar', $_GET['id']) : null;
?>
<div class="mt-5 mb-4 rounded-3">
    <legend><?=$is_edit ? 'Ubah' : 'Tambah'?> Data Kamar</legend>
    <form method="POST" action="proses.php">
        <input type="hidden" name="aksi" value="<?=$is_edit ? 'ubah_kamar' : 'tambah_kamar'?>">
        <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>">

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Nomor Kamar (ID)</label>
            <div class="col-sm-9">
                <input type="text" name="room_id" class="form-control" value="<?=$r->room_id?>" <?=$is_edit?'readonly':''?> placeholder="Contoh: 101" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Tipe Kamar</label>
            <div class="col-sm-9">
                <input type="text" name="room_type_name" class="form-control" value="<?=$r->room_type_name?>" placeholder="e.g. Deluxe, Suite" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Harga Dasar</label>
            <div class="col-sm-9">
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="base_price" class="form-control" value="<?=$r->base_price?>" required>
                </div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Maks Tamu</label>
            <div class="col-sm-9">
                <input type="number" name="max_occupancy" class="form-control" value="<?=$r->max_occupancy?>" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Status</label>
            <div class="col-sm-9">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusAvailable" value="Available" <?=($r->status=='Available')?'checked':''?>>
                    <label class="form-check-label" for="statusAvailable">Available</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusCleaning" value="Cleaning" <?=($r->status=='Cleaning')?'checked':''?>>
                    <label class="form-check-label" for="statusCleaning">Cleaning</label>
                </div><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusMaintenance" value="Maintenance" <?=($r->status=='Maintenance')?'checked':''?>>
                    <label class="form-check-label" for="statusMaintenance">Maintenance</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusOccupied" value="Occupied" <?=($r->status=='Occupied')?'checked':''?>>
                    <label class="form-check-label" for="statusOccupied">Occupied</label>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="?page=data-kamar" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?php 
// ==================================================================
// MODUL DATA LAYANAN
// ==================================================================
} elseif (isset($_GET['page']) && $_GET['page']=='data-layanan' && isset($_COOKIE['jwt'])) { 
    $data = $abc->get_general($_COOKIE['jwt'], 'tampil_layanan');
?>
<div class="mt-5 mb-4 rounded-3">
    <legend>Data Layanan <a href="?page=tambah-layanan" class="btn btn-primary btn-sm float-end"><i class="bi bi-plus me-1"></i> Tambah Layanan</a></legend>
    <div class="rounded-3 overflow-hidden">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <td>ID</td>
                <td>Nama Layanan</td>
                <td>Kategori</td>
                <td>Harga</td>
                <td>Status</td>
                <td>Aksi</td>
            </tr>
        </thead>
        <tbody>
        <?php foreach($data as $r) { ?>
            <tr>
                <td><?=$r->service_id?></td>
                <td><?=$r->service_name?></td>
                <td><?=$r->category?></td>
                <td style="text-align:right">Rp <?=number_format($r->price)?></td>
                <td><?php echo $r->is_available ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non-Aktif</span>'; ?></td>
                <td style="text-align:center">
                    <div class="btn-group" role="group">
                        <a href="?page=ubah-layanan&id=<?=$r->service_id?>" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i></a>
                        <a href="proses.php?aksi=hapus_layanan&service_id=<?=$r->service_id?>&jwt=<?=$_COOKIE['jwt']?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus layanan ini?')"><i class="bi bi-x"></i></a>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    </div>
</div>
<?php 
} elseif (isset($_GET['page']) && ($_GET['page']=='tambah-layanan' || $_GET['page']=='ubah-layanan') && isset($_COOKIE['jwt'])) {
    $is_edit = ($_GET['page']=='ubah-layanan');
    $r = $is_edit ? $abc->get_general($_COOKIE['jwt'], 'detail_layanan', $_GET['id']) : null;
?>
<div class="mt-5 mb-4 rounded-3">
    <legend><?=$is_edit ? 'Ubah' : 'Tambah'?> Data Layanan</legend>
    <form method="POST" action="proses.php">
        <input type="hidden" name="aksi" value="<?=$is_edit ? 'ubah_layanan' : 'tambah_layanan'?>">
        <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>">

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">ID Layanan</label>
            <div class="col-sm-9">
                <input type="text" name="service_id" class="form-control" value="<?=$r->service_id?>" <?=$is_edit?'readonly':''?> placeholder="001" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Nama Layanan</label>
            <div class="col-sm-9">
                <input type="text" name="service_name" class="form-control" value="<?=$r->service_name?>" required>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Deskripsi</label>
            <div class="col-sm-9">
                <textarea name="description" class="form-control"><?=$r->description?></textarea>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Harga</label>
            <div class="col-sm-9">
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="price" class="form-control" value="<?=$r->price?>" required>
                </div>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Kategori</label>
            <div class="col-sm-9">
                <select name="category" class="form-select">
                    <option value="F&B" <?=($r->category=='F&B')?'selected':''?>>F&B</option>
                    <option value="Laundry" <?=($r->category=='Laundry')?'selected':''?>>Laundry</option>
                    <option value="Spa" <?=($r->category=='Spa')?'selected':''?>>Spa</option>
                    <option value="Transport" <?=($r->category=='Transport')?'selected':''?>>Transport</option>
                    <option value="General" <?=($r->category=='General')?'selected':''?>>General</option>
                </select>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">Status</label>
            <div class="col-sm-9">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_available" id="avail_yes" value="1" <?=($r->is_available==1 || !$is_edit)?'checked':''?>>
                    <label class="form-check-label" for="avail_yes">Aktif</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_available" id="avail_no" value="0" <?=($r->is_available==0 && $is_edit)?'checked':''?>>
                    <label class="form-check-label" for="avail_no">Non-Aktif</label>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="?page=data-layanan" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?php 
} else if (isset($_GET['page']) && $_GET['page']=='tambah' && isset($_COOKIE['jwt'])) { 
    // Jika user membuka ?page=tambah, arahkan ke modal: buka modal dan hapus param URL
?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    function openModalWhenReady(){
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var el = document.getElementById('reservasiModal');
            if (el) {
                var m = new bootstrap.Modal(el);
                m.show();
            }
        } else {
            setTimeout(openModalWhenReady, 100);
        }
    }
    openModalWhenReady();

    // Hapus parameter ?page=tambah agar URL bersih (tanpa reload)
    if (window.history && window.history.replaceState) {
        try {
            var u = new URL(window.location);
            u.searchParams.delete('page');
            window.history.replaceState({}, document.title, u.pathname + u.search + u.hash);
        } catch (e) {
            // fallback: coba hapus dengan replaceState sederhana
            var href = window.location.href.replace(/[?&]page=tambah/, '');
            window.history.replaceState({}, document.title, href);
        }
    }
});
</script>
<?php 
} elseif (isset($_GET['page']) && $_GET['page']=='ubah' && isset($_COOKIE['jwt'])) {    
    $data_req = array("jwt"=>$_COOKIE['jwt'], "reservation_id"=>$_GET['id']); 
    $result = $abc->tampil_data($data_req); 
    
    $r = $result->reservasi;
    $selected_svcs = $result->selected_services; // Array ID services yg sudah dipilih

    $list_kamar = $abc->get_list_kamar($_COOKIE['jwt']);
    $list_service = $abc->get_list_service($_COOKIE['jwt']);
?>
<div class="mt-5 mb-4 rounded-3">
<legend>Ubah Data Reservasi</legend>  
    <form name="form1" method="post" action="proses.php" class="form-horizontal">
        <input type="hidden" name="aksi" value="ubah"/>
        <input type="hidden" name="reservation_id" value="<?=$r->reservation_id?>" />
        <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>"/>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">ID Reservasi</label>
                    <div>
                        <input type="text" class="form-control" value="<?=$r->reservation_id?>" readonly disabled>
                        <small class="text-muted">Tamu: <strong><?=$r->guest_id?></strong></small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Check In</label>
                    <input type="date" id="check_in_date" name="check_in_date" class="form-control" value="<?=$r->check_in_date?>" onchange="calculateTotal()">
                </div>

                <div class="mb-3">
                    <label class="form-label">Check Out</label>
                    <input type="date" id="check_out_date" name="check_out_date" class="form-control" value="<?=$r->check_out_date?>" onchange="calculateTotal()">
                </div>

                <div class="mb-3">
                    <label class="form-label">Pilih Kamar</label>
                    <select name="room_id" id="room_id" class="form-select" onchange="calculateTotal()">
                        <?php foreach($list_kamar as $kamar) { 
                            $selected = ($kamar->room_id == $r->room_id) ? 'selected' : '';
                        ?>
                            <option value="<?=$kamar->room_id?>" data-price="<?=$kamar->base_price?>" <?=$selected?>>
                                <?=$kamar->room_type_name?> (Rp <?=number_format($kamar->base_price)?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Layanan</label>
                    <div>
                        <?php foreach($list_service as $svc) { 
                            $checked = in_array($svc->service_id, $selected_svcs) ? 'checked' : '';
                        ?>
                            <div class="form-check">
                                <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                        value="<?=$svc->service_id?>" data-price="<?=$svc->price?>" data-name="<?=$svc->service_name?>" onchange="calculateTotal()" <?=$checked?>>
                                <label class="form-check-label"><?=$svc->service_name?> (+ Rp <?=number_format($svc->price)?>)</label>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Pending" <?=($r->status=='Pending')?'selected':''?>>Pending</option>
                        <option value="Confirmed" <?=($r->status=='Confirmed')?'selected':''?>>Confirmed</option>
                        <option value="Checked-in" <?=($r->status=='Checked-in')?'selected':''?>>Checked-in</option>
                        <option value="Cancelled" <?=($r->status=='Cancelled')?'selected':''?>>Cancelled</option>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" name="ubah" class="btn btn-primary">Simpan</button>
                    <a href="?page=data-transaksi" class="btn btn-secondary ms-2">Batal</a>
                </div>
            </div>
        </div>
    </form>
    <script>calculateTotal();</script>
<?php 
} else if (isset($_GET['page']) && $_GET['page']=='data-transaksi' && isset($_COOKIE['jwt'])) {
?>
<div class="mt-5 mb-4 rounded-3">
<legend>Daftar Transaksi Tamu</legend>

    <div class="rounded-3 overflow-hidden">
    <table class="table table-bordered table-striped table-hover ">
    <thead class="table-dark">
        <tr style="background-color: #f5f5f5;">
            <td rowspan="2" style="vertical-align: middle; text-align:center">No</td>
            <td rowspan="2" style="vertical-align: middle; text-align:center">ID Res</td>
            <td rowspan="2" style="vertical-align: middle; text-align:center">Tamu & Kamar</td>
            <td rowspan="2" style="vertical-align: middle; text-align:center">Tanggal</td>
            <td colspan="3" style="text-align: center;">Rincian Tagihan</td>
            <td rowspan="2" style="vertical-align: middle; text-align:center">Status</td>
            <td rowspan="2" style="vertical-align: middle; text-align:center">Aksi</td>
        </tr>
        <tr>
            <td style="text-align:center">Kamar</td>
            <td style="text-align:center">Layanan</td>
            <td style="text-align:center">Total</td>
        </tr>
    </thead>
    <tbody>
    <?php   
        $no = 1;
        $data = $abc->tampil_semua_data($_COOKIE['jwt']);
        
        if($data) {
            foreach ($data as $r)   {
    ?>  <tr>
            <td style="text-align:center"><?=$no?></td>
            <td style="text-align:center"><?=$r->reservation_id?></td>
            <td>
                <label style="font-weight: 600;"><?=$r->full_name?></label><br>
                <small class="text-muted">Room: <?=$r->room_type_name?></small>
            </td>
            <td>
                <small>In: <?=$r->check_in_date?></small><br>
                <small>Out: <?=$r->check_out_date?></small>
            </td>
            
            <td style="text-align:right">Rp <?=number_format($r->total_room_cost)?></td>
            <td style="text-align:right">
                Rp <?=number_format($r->total_service_cost)?>
                <br>
                <small style="color:gray; font-size:0.8em; font-style:italic;"><?=$r->list_layanan ? $r->list_layanan : '- null -'?></small>
            </td>
            <td style="text-align:right; font-weight: 600;">Rp <?=number_format($r->grand_total)?></td>
            
            <td style="text-align:center">
                <?php 
                    // Badge Status Reservasi (Bootstrap 5 classes)
                    $badge = 'bg-warning text-dark';
                    if($r->status == 'Confirmed') $badge = 'bg-info text-dark';
                    if($r->status == 'Checked-in') $badge = 'bg-success';
                    if($r->status == 'Cancelled') $badge = 'bg-danger';
                ?>
                <span class="badge <?=$badge?>" style="margin-bottom: 5px; display:inline-block; width:80px;"><?=$r->status?></span>
                <br>

                <?php if ($r->payment_status == 'Unpaid') { ?>
                    <a href="proses.php?aksi=ubah_bayar&reservation_id=<?=$r->reservation_id?>&status_baru=Paid&jwt=<?=$_COOKIE['jwt']?>" 
                       class="btn  btn-danger"
                       style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .70rem;" 
                       onclick="return confirm('Tandai reservasi ini LUNAS (Paid)?')"
                       title="Klik untuk melunasi">
                       <i class="bi bi-x-circle-fill me-1"></i> Unpaid
                    </a>
                <?php } else { ?>
                    <a href="proses.php?aksi=ubah_bayar&reservation_id=<?=$r->reservation_id?>&status_baru=Unpaid&jwt=<?=$_COOKIE['jwt']?>" 
                       class="btn btn-success" 
                       style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .70rem;"
                       onclick="return confirm('Ubah status kembali menjadi BELUM BAYAR (Unpaid)?')"
                       title="Klik untuk membatalkan pelunasan">
                       <i class="bi bi-check-circle-fill me-1"></i> Paid
                    </a>
                <?php } ?>
            </td>
			
            <td style="text-align:center;">
                <div class="btn-group" role="group">
                    <a href="?page=ubah&id=<?=$r->reservation_id?>" class="btn btn-success btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                    <a href="proses.php?aksi=hapus&reservation_id=<?=$r->reservation_id?>&jwt=<?=$_COOKIE['jwt']?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini beserta tagihannya?')" title="Hapus"><i class="bi bi-x"></i></a>
                </div>
            </td>
        </tr>
    <?php   
            $no++;
            } 
        } else {
            echo "<tr><td colspan='9' style='text-align:center; padding: 20px;'><strong>Belum ada data transaksi.</strong></td></tr>";
        }
        unset($data,$r,$no,$abc);
    ?>
    </tbody>
    </table>
    </div>
</div>
<?php } else { ?>
<!-- <legend>Selamat Datang</legend> -->
    <div class="p-5 mb-4 rounded-3">
        <h2>Sistem Management Hotel</h2>
        <p>Sistem informasi untuk mengelola reservasi kamar, layanan tambahan, dan tagihan tamu.</p>

            <?php if(!isset($_COOKIE['jwt'])) { ?>
            <p><a class="btn btn-primary btn-lg" href="?page=login">Login Staff &raquo;</a></p>
        <?php } else if (isset($_GET['page']) && $_GET['page']=='ubah' && isset($_COOKIE['jwt'])) { ?>
        <?php } else if (isset($_GET['page']) && $_GET['page']=='data-transaksi' && isset($_COOKIE['jwt'])) { ?>
        <?php } else { ?>
            <p>Halo, Selamat Datang <strong><?=$_COOKIE['username']?></strong> (<?=$_COOKIE['role']?>)</p>
            <a class="btn btn-info" href="#" data-bs-toggle="modal" data-bs-target="#reservasiModal">Buat Reservasi Baru</a>
            <a class="btn btn-secondary ms-2" href="?page=data-transaksi">Lihat Transaksi</a>
        <?php } ?>
    </div>
</fieldset>
</div>
<?php } ?>

<script src="js/jquery.js"></script>
<?php if (isset($_COOKIE['jwt'])) { ?>
<!-- Modal Buat Reservasi -->
<div class="modal fade" id="reservasiModal" tabindex="-1" aria-labelledby="reservasiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reservasiModalLabel">Buat Reservasi Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form name="form_modal_reservasi" method="POST" action="proses.php" novalidate>
            <input type="hidden" name="aksi" value="tambah"/>
            <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>"/>

            <div class="mb-3">
                <label class="form-label">Nama Tamu</label>
                <select name="guest_id" required class="form-select">
                    <option value="">-- Pilih Tamu --</option>
                    <?php if(!empty($list_tamu)) foreach($list_tamu as $tamu) { ?>
                        <option value="<?=$tamu->guest_id?>"><?=$tamu->full_name?> (<?=$tamu->phone_number?>)</option>
                    <?php } ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Check In</label>
                    <input type="date" id="check_in_date" name="check_in_date" class="form-control" onchange="calculateTotal()" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Check Out</label>
                    <input type="date" id="check_out_date" name="check_out_date" class="form-control" onchange="calculateTotal()" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Pilih Kamar</label>
                <select name="room_id" id="room_id" class="form-select" onchange="calculateTotal()" required>
                    <option value="" data-price="0">-- Pilih Tipe Kamar --</option>
                    <?php if(!empty($list_kamar)) foreach($list_kamar as $kamar) { ?>
                        <option value="<?=$kamar->room_id?>" data-price="<?=$kamar->base_price?>">
                            <?=$kamar->room_type_name?> (Rp <?=number_format($kamar->base_price)?> / malam)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Layanan Tambahan</label>
                <div>
                    <?php if(!empty($list_service)) { foreach($list_service as $svc) { ?>
                        <div class="form-check">
                            <input class="form-check-input service-checkbox" type="checkbox" name="services[]" 
                                         value="<?=$svc->service_id?>" data-price="<?=$svc->price?>" data-name="<?=$svc->service_name?>" onchange="calculateTotal()">
                            <label class="form-check-label"><?=$svc->service_name?> (+ Rp <?=number_format($svc->price)?>)</label>
                        </div>
                    <?php } } else { echo "Tidak ada layanan tersedia"; } ?>
                </div>
            </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" name="simpan" class="btn btn-primary">Simpan Reservasi</button>
      </div>
        </form>
    </div>
  </div>
</div>
<?php } ?>
<script src="js/bootstrap.bundle.min.js"></script>

<script src="js/jqBootstrapValidation.js"></script>
<script>
    $(function () { $("input,select,textarea").not("[type=submit]").jqBootstrapValidation(); } );

	function calculateTotal() {
		// 1. Ambil Harga Kamar
		var roomSelect = document.getElementById('room_id');
		var roomPrice = 0;
		if (roomSelect.value !== "") {
			// Mengambil atribut data-price dari option yang dipilih
			var selectedOption = roomSelect.options[roomSelect.selectedIndex];
			if(selectedOption.getAttribute('data-price')) {
				roomPrice = parseFloat(selectedOption.getAttribute('data-price'));
			}
		}

		// 2. Hitung Durasi (Check-out - Check-in)
		var dateIn = document.getElementById('check_in_date').value;
		var dateOut = document.getElementById('check_out_date').value;
		var days = 0;

		if(dateIn && dateOut) {
			var d1 = new Date(dateIn);
			var d2 = new Date(dateOut);
			var timeDiff = d2.getTime() - d1.getTime();
			days = timeDiff / (1000 * 3600 * 24); 
		}
		
		if (days < 1) days = 0; 

		// Total Harga Kamar = Harga per malam * Durasi
		var totalRoomCost = roomPrice * days;

		// 3. Ambil Harga Service yang dicentang
		var serviceCheckboxes = document.querySelectorAll('.service-checkbox:checked');
		var serviceTotal = 0;
		var serviceDetails = [];
		
		serviceCheckboxes.forEach(function(checkbox) {
			var price = parseFloat(checkbox.getAttribute('data-price'));
			var name = checkbox.getAttribute('data-name');
			serviceTotal += price;
			serviceDetails.push(name);
		});

		// 4. Update Tampilan HTML (Estimasi)
		document.getElementById('display_days').innerText = days + " Malam";
		document.getElementById('display_room_price').innerText = "Rp " + totalRoomCost.toLocaleString('id-ID');
		document.getElementById('display_service_total').innerText = "Rp " + serviceTotal.toLocaleString('id-ID');
		
		var detailText = serviceDetails.length > 0 ? serviceDetails.join(", ") : "-";
		document.getElementById('display_service_details').innerText = detailText;

		// Grand Total
		var grandTotal = totalRoomCost + serviceTotal;
		document.getElementById('display_grand_total').innerText = "Rp " + grandTotal.toLocaleString('id-ID');
	}
</script>

</body>
</html>