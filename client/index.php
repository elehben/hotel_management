<?php
include "client.php";
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sistem Reservasi Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.css" rel="stylesheet">        
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
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
<div class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="#">Hotel Management</a>
    <?php if (isset($_COOKIE['jwt'])) { ?>
        <ul class="nav">
          <li><a href="?page=home"><i class="icon-home"></i> Home</a></li>
          
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-folder-open"></i> Master Data <b class="caret"></b></a>
            <ul class="dropdown-menu">
               <li><a href="?page=data-tamu">Data Tamu</a></li>
               <li><a href="?page=data-kamar">Data Kamar</a></li>
               <li><a href="?page=data-layanan">Data Layanan</a></li>
            </ul>
          </li>

          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-shopping-cart"></i> Transaksi <b class="caret"></b></a>
            <ul class="dropdown-menu">
               <li><a href="?page=tambah">Buat Reservasi</a></li>
               <li><a href="?page=data-transaksi">List Transaksi</a></li>
            </ul>
          </li>
        </ul>
        <ul class="nav pull-right">
          <li><a href="#"><i class="icon-user"></i> Halo, <?= isset($_COOKIE['username']) ? '<strong>'.$_COOKIE['username'].'</strong>' : 'User';?></a></li>
          <li><a href="proses.php?aksi=logout" onclick="return confirm('Logout?')"><i class="icon-off"></i> Logout</a></li>
        </ul>
    <?php } else { ?> 
        <?php } ?>
  </div>
</div>

<div class="container">
<fieldset>

<?php if (isset($_GET['page']) && $_GET['page']=='login' && !isset($_COOKIE['jwt'])) { ?>
<legend>Login Sistem (Admin/Staf)</legend>  
    <div class="row-fluid ">
    <div class="span8 alert alert-info">
    <form class="form-horizontal" name="form1" method="POST" action="proses.php" novalidate>
        <input type="hidden" name="aksi" value="login"/>
        <div class="control-group">
            <label class="control-label" for="username">Username</label>
            <div class="controls">
                <input type="text" name="username" class="input-medium" placeholder="Username"
                    rel="tooltip" data-placement="right" title="Masukkan Username"
                    required data-validation-required-message="Harus diisi">                  
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="password">Password</label>
            <div class="controls">
                <input type="password" name="password" class="input-medium" placeholder="Password"
                    rel="tooltip" data-placement="right" title="Masukkan Password"
                    required data-validation-required-message="Harus diisi">
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <button type="submit" name="simpan" class="btn btn-primary"><i class="icon-lock icon-white"></i> Login</button>
            </div>  
        </div>      
    </form> 
    </div>
    </div>

<?php 
// ==================================================================
// MODUL DATA TAMU
// ==================================================================
} elseif (isset($_GET['page']) && $_GET['page']=='data-tamu' && isset($_COOKIE['jwt'])) { 
    $data = $abc->get_general($_COOKIE['jwt'], 'tampil_tamu');
?>
    <legend>Data Tamu <a href="?page=tambah-tamu" class="btn btn-primary btn-small pull-right"><i class="icon-plus icon-white"></i> Tambah Tamu</a></legend>
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr><th>No</th><th>ID (KTP)</th><th>Nama Lengkap</th><th>Email</th><th>No HP</th><th>Aksi</th></tr>
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
                    <a href="?page=ubah-tamu&id=<?=$r->guest_id?>" class="btn btn-medium btn-success"><i class="icon-pencil"></i></a>
                    <a href="proses.php?aksi=hapus_tamu&guest_id=<?=$r->guest_id?>&jwt=<?=$_COOKIE['jwt']?>" class="btn btn-medium btn-danger" onclick="return confirm('Hapus tamu ini?')"><i class="icon-remove"></i></a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

<?php 
} elseif (isset($_GET['page']) && ($_GET['page']=='tambah-tamu' || $_GET['page']=='ubah-tamu') && isset($_COOKIE['jwt'])) {
    $is_edit = ($_GET['page']=='ubah-tamu');
    $r = $is_edit ? $abc->get_general($_COOKIE['jwt'], 'detail_tamu', $_GET['id']) : null;
?>
    <legend><?=$is_edit ? 'Ubah' : 'Tambah'?> Data Tamu</legend>
    <form class="form-horizontal" method="POST" action="proses.php">
        <input type="hidden" name="aksi" value="<?=$is_edit ? 'ubah_tamu' : 'tambah_tamu'?>">
        <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>">
        
        <div class="control-group">
            <label class="control-label">ID Tamu (KTP)</label>
            <div class="controls">
                <input type="text" name="guest_id" class="input-xlarge" value="<?=$r->guest_id?>" <?=$is_edit?'readonly':''?> required>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Nama Lengkap</label>
            <div class="controls">
                <input type="text" name="full_name" class="input-xlarge" value="<?=$r->full_name?>" required>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Email</label>
            <div class="controls">
                <input type="email" name="email" class="input-xlarge" value="<?=$r->email?>" required>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">No HP</label>
            <div class="controls">
                <input type="text" name="phone_number" class="input-xlarge" value="<?=$r->phone_number?>" required>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="?page=data-tamu" class="btn">Batal</a>
        </div>
    </form>

<?php 
// ==================================================================
// MODUL DATA KAMAR
// ==================================================================
} elseif (isset($_GET['page']) && $_GET['page']=='data-kamar' && isset($_COOKIE['jwt'])) { 
    $data = $abc->get_general($_COOKIE['jwt'], 'tampil_kamar');
?>
    <legend>Data Kamar <a href="?page=tambah-kamar" class="btn btn-primary btn-small pull-right"><i class="icon-plus icon-white"></i> Tambah Kamar</a></legend>
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr><th>No Kamar</th><th>Tipe</th><th>Harga Dasar</th><th>Kapasitas</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach($data as $r) { ?>
            <tr>
                <td><strong><?=$r->room_id?></strong></td>
                <td><?=$r->room_type_name?></td>
                <td style="text-align:right">Rp <?=number_format($r->base_price)?></td>
                <td><?=$r->max_occupancy?> Org</td>
                <td><span class="label <?=$r->status=='Available'?'label-success':'label-warning'?>"><?=$r->status?></span></td>
                <td style="text-align:center">
                    <a href="?page=ubah-kamar&id=<?=$r->room_id?>" class="btn btn-medium btn-success"><i class="icon-pencil"></i></a>
                    <a href="proses.php?aksi=hapus_kamar&room_id=<?=$r->room_id?>&jwt=<?=$_COOKIE['jwt']?>" class="btn btn-medium btn-danger" onclick="return confirm('Hapus kamar ini?')"><i class="icon-remove"></i></a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

<?php 
} elseif (isset($_GET['page']) && ($_GET['page']=='tambah-kamar' || $_GET['page']=='ubah-kamar') && isset($_COOKIE['jwt'])) {
    $is_edit = ($_GET['page']=='ubah-kamar');
    $r = $is_edit ? $abc->get_general($_COOKIE['jwt'], 'detail_kamar', $_GET['id']) : null;
?>
    <legend><?=$is_edit ? 'Ubah' : 'Tambah'?> Data Kamar</legend>
    <form class="form-horizontal" method="POST" action="proses.php">
        <input type="hidden" name="aksi" value="<?=$is_edit ? 'ubah_kamar' : 'tambah_kamar'?>">
        <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>">

        <div class="control-group">
            <label class="control-label">Nomor Kamar (ID)</label>
            <div class="controls">
                <input type="text" name="room_id" class="input-small" value="<?=$r->room_id?>" <?=$is_edit?'readonly':''?> placeholder="Contoh: 101" required>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Tipe Kamar</label>
            <div class="controls">
                <input type="text" name="room_type_name" class="input-large" value="<?=$r->room_type_name?>" placeholder="e.g. Deluxe, Suite" required>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Harga Dasar</label>
            <div class="controls">
                <div class="input-prepend">
                    <span class="add-on">Rp</span>
                    <input type="number" name="base_price" class="input-medium" value="<?=$r->base_price?>" required>
                </div>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Maks Tamu</label>
            <div class="controls">
                <input type="number" name="max_occupancy" class="input-mini" value="<?=$r->max_occupancy?>" required>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Status</label>
            <div class="controls">
                <select name="status">
                    <option value="Available" <?=($r->status=='Available')?'selected':''?>>Available</option>
                    <option value="Cleaning" <?=($r->status=='Cleaning')?'selected':''?>>Cleaning</option>
                    <option value="Maintenance" <?=($r->status=='Maintenance')?'selected':''?>>Maintenance</option>
                    <option value="Occupied" <?=($r->status=='Occupied')?'selected':''?>>Occupied</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="?page=data-kamar" class="btn">Batal</a>
        </div>
    </form>

<?php 
// ==================================================================
// MODUL DATA LAYANAN
// ==================================================================
} elseif (isset($_GET['page']) && $_GET['page']=='data-layanan' && isset($_COOKIE['jwt'])) { 
    $data = $abc->get_general($_COOKIE['jwt'], 'tampil_layanan');
?>
    <legend>Data Layanan <a href="?page=tambah-layanan" class="btn btn-primary btn-small pull-right"><i class="icon-plus icon-white"></i> Tambah Layanan</a></legend>
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr><th>ID</th><th>Nama Layanan</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach($data as $r) { ?>
            <tr>
                <td><?=$r->service_id?></td>
                <td><?=$r->service_name?></td>
                <td><?=$r->category?></td>
                <td style="text-align:right">Rp <?=number_format($r->price)?></td>
                <td><?=$r->is_available ? '<span class="label label-success">Aktif</span>' : '<span class="label">Non-Aktif</span>'?></td>
                <td style="text-align:center">
                    <a href="?page=ubah-layanan&id=<?=$r->service_id?>" class="btn btn-medium btn-success"><i class="icon-pencil"></i></a>
                    <a href="proses.php?aksi=hapus_layanan&service_id=<?=$r->service_id?>&jwt=<?=$_COOKIE['jwt']?>" class="btn btn-medium btn-danger" onclick="return confirm('Hapus layanan ini?')"><i class="icon-remove"></i></a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

<?php 
} elseif (isset($_GET['page']) && ($_GET['page']=='tambah-layanan' || $_GET['page']=='ubah-layanan') && isset($_COOKIE['jwt'])) {
    $is_edit = ($_GET['page']=='ubah-layanan');
    $r = $is_edit ? $abc->get_general($_COOKIE['jwt'], 'detail_layanan', $_GET['id']) : null;
?>
    <legend><?=$is_edit ? 'Ubah' : 'Tambah'?> Data Layanan</legend>
    <form class="form-horizontal" method="POST" action="proses.php">
        <input type="hidden" name="aksi" value="<?=$is_edit ? 'ubah_layanan' : 'tambah_layanan'?>">
        <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>">

        <div class="control-group">
            <label class="control-label">ID Layanan</label>
            <div class="controls">
                <input type="text" name="service_id" class="input-small" value="<?=$r->service_id?>" <?=$is_edit?'readonly':''?> placeholder="001" required>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Nama Layanan</label>
            <div class="controls">
                <input type="text" name="service_name" class="input-large" value="<?=$r->service_name?>" required>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Deskripsi</label>
            <div class="controls">
                <textarea name="description" class="input-large"><?=$r->description?></textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Harga</label>
            <div class="controls">
                <div class="input-prepend">
                    <span class="add-on">Rp</span>
                    <input type="number" name="price" class="input-medium" value="<?=$r->price?>" required>
                </div>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Kategori</label>
            <div class="controls">
                <select name="category">
                    <option value="F&B" <?=($r->category=='F&B')?'selected':''?>>F&B</option>
                    <option value="Laundry" <?=($r->category=='Laundry')?'selected':''?>>Laundry</option>
                    <option value="Spa" <?=($r->category=='Spa')?'selected':''?>>Spa</option>
                    <option value="Transport" <?=($r->category=='Transport')?'selected':''?>>Transport</option>
                    <option value="General" <?=($r->category=='General')?'selected':''?>>General</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Status</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="is_available" value="1" <?=($r->is_available==1 || !$is_edit)?'checked':''?>> Aktif
                </label>
                <label class="radio inline">
                    <input type="radio" name="is_available" value="0" <?=($r->is_available==0 && $is_edit)?'checked':''?>> Non-Aktif
                </label>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="?page=data-layanan" class="btn">Batal</a>
        </div>
    </form>

<?php 
} else if (isset($_GET['page']) && $_GET['page']=='tambah' && isset($_COOKIE['jwt'])) { 
    // Load Data dari API
    $list_kamar = $abc->get_list_kamar($_COOKIE['jwt']);
    $list_service = $abc->get_list_service($_COOKIE['jwt']);
    $list_tamu = $abc->get_list_tamu($_COOKIE['jwt']); 
?>
<legend>Buat Reservasi Baru</legend>    
    <div class="row-fluid ">
    <div class="span8 alert alert-info">
    <form class="form-horizontal" name="form1" method="POST" action="proses.php" novalidate>
        <input type="hidden" name="aksi" value="tambah"/>
        <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>"/>

        <div class="row-fluid">
            <div class="span8">
                <div class="control-group">
                    <label class="control-label">Nama Tamu</label>
                    <div class="controls">
                        <select name="guest_id" required class="input-large">
                            <option value="">-- Pilih Tamu --</option>
                            <?php foreach($list_tamu as $tamu) { ?>
                                <option value="<?=$tamu->guest_id?>"><?=$tamu->full_name?> (<?=$tamu->phone_number?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Check In</label>
                    <div class="controls">
                        <input type="date" id="check_in_date" name="check_in_date" onchange="calculateTotal()" required>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Check Out</label>
                    <div class="controls">
                        <input type="date" id="check_out_date" name="check_out_date" onchange="calculateTotal()" required>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Pilih Kamar</label>
                    <div class="controls">
                        <select name="room_id" id="room_id" onchange="calculateTotal()" required>
                            <option value="" data-price="0">-- Pilih Tipe Kamar --</option>
                            <?php foreach($list_kamar as $kamar) { ?>
                                <option value="<?=$kamar->room_id?>" data-price="<?=$kamar->base_price?>">
                                    <?=$kamar->room_type_name?> (Rp <?=number_format($kamar->base_price)?> / malam)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Layanan Tambahan</label>
                    <div class="controls">
                        <?php if($list_service) { foreach($list_service as $svc) { ?>
                            <label class="checkbox">
                                <input type="checkbox" name="services[]" class="service-checkbox" 
                                       value="<?=$svc->service_id?>" 
                                       data-price="<?=$svc->price?>" 
                                       data-name="<?=$svc->service_name?>"
                                       onchange="calculateTotal()"> 
                                <?=$svc->service_name?> (+ Rp <?=number_format($svc->price)?>)
                            </label>
                        <?php }} else { echo "Tidak ada layanan tersedia"; } ?>
                    </div>
                </div>
				<div class="control-group">
					<div class="controls">
						<div class="button-toolbar">
							<button type="submit" name="simpan" class="btn btn-medium pull-left btn-primary">Simpan</button>
							<a href="?page=data-transaksi" class="btn" style="margin-left: 5px;">Batal</a>
						</div>
					</div>
				</div>
            </div>
        </div>   
    </form> 
    </div>
    </div>

<?php 
} elseif (isset($_GET['page']) && $_GET['page']=='ubah' && isset($_COOKIE['jwt'])) {    
    $data_req = array("jwt"=>$_COOKIE['jwt'], "reservation_id"=>$_GET['id']); 
    $result = $abc->tampil_data($data_req); 
    
    $r = $result->reservasi;
    $selected_svcs = $result->selected_services; // Array ID services yg sudah dipilih

    $list_kamar = $abc->get_list_kamar($_COOKIE['jwt']);
    $list_service = $abc->get_list_service($_COOKIE['jwt']);
?>
<legend>Ubah Data Reservasi</legend>  
    <form name="form1" method="post" action="proses.php" class="form-horizontal">
        <input type="hidden" name="aksi" value="ubah"/>
        <input type="hidden" name="reservation_id" value="<?=$r->reservation_id?>" />
        <input type="hidden" name="jwt" value="<?=$_COOKIE['jwt']?>"/>
        
        <div class="row-fluid">
            <div class="span6">
                <div class="control-group">
                    <label class="control-label">ID Reservasi</label>
                    <div class="controls">
                        <input type="text" disabled class="input-small" value="<?=$r->reservation_id?>">
                        <span class="help-inline">Tamu: <strong><?=$r->guest_id?></strong></span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Check In</label>
                    <div class="controls">
                        <input type="date" id="check_in_date" name="check_in_date" value="<?=$r->check_in_date?>" onchange="calculateTotal()">
                    </div>
                </div>
                
				<div class="control-group">
                    <label class="control-label">Check Out</label>
                    <div class="controls">
                        <input type="date" id="check_out_date" name="check_out_date" value="<?=$r->check_out_date?>" onchange="calculateTotal()">
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Pilih Kamar</label>
                    <div class="controls">
                        <select name="room_id" id="room_id" onchange="calculateTotal()">
                            <?php foreach($list_kamar as $kamar) { 
                                $selected = ($kamar->room_id == $r->room_id) ? 'selected' : '';
                            ?>
                                <option value="<?=$kamar->room_id?>" data-price="<?=$kamar->base_price?>" <?=$selected?>>
                                    <?=$kamar->room_type_name?> (Rp <?=number_format($kamar->base_price)?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Layanan</label>
                    <div class="controls">
                        <?php foreach($list_service as $svc) { 
                            // Cek apakah service ini ada di array selected_svcs
                            $checked = in_array($svc->service_id, $selected_svcs) ? 'checked' : '';
                        ?>
                            <label class="checkbox">
                                <input type="checkbox" name="services[]" class="service-checkbox" 
                                       value="<?=$svc->service_id?>" 
                                       data-price="<?=$svc->price?>" 
                                       data-name="<?=$svc->service_name?>"
                                       onchange="calculateTotal()" <?=$checked?>> 
                                <?=$svc->service_name?> (+ Rp <?=number_format($svc->price)?>)
                            </label>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label">Status</label>
                    <div class="controls">
                        <select name="status">
                            <option value="Pending" <?=($r->status=='Pending')?'selected':''?>>Pending</option>
                            <option value="Confirmed" <?=($r->status=='Confirmed')?'selected':''?>>Confirmed</option>
                            <option value="Checked-in" <?=($r->status=='Checked-in')?'selected':''?>>Checked-in</option>
                            <option value="Cancelled" <?=($r->status=='Cancelled')?'selected':''?>>Cancelled</option>
                        </select>
                    </div>
                </div>

				<div class="control-group">
                    <div class="controls">
						<button type="submit" name="ubah" class="btn btn-medium pull-left btn-primary">Simpan</button>
						<a href="?page=data-transaksi" class="btn" style="margin-left: 5px;">Batal</a>
                    </div>
                </div>
			</div>
        </div>
    </form>
    <script>calculateTotal();</script>
    
<?php 
} else if (isset($_GET['page']) && $_GET['page']=='data-transaksi' && isset($_COOKIE['jwt'])) {
?>
<legend>Daftar Transaksi Tamu</legend>

    <table class="table table-hover table-bordered table-striped">
    <thead>
        <tr style="background-color: #f5f5f5;">
            <th rowspan="2" style="vertical-align: middle; text-align:center">No</th>
            <th rowspan="2" style="vertical-align: middle; text-align:center">ID Res</th>
            <th rowspan="2" style="vertical-align: middle; text-align:center">Tamu & Kamar</th>
            <th rowspan="2" style="vertical-align: middle; text-align:center">Tanggal</th>
            <th colspan="3" style="text-align: center;">Rincian Tagihan</th>
            <th rowspan="2" style="vertical-align: middle; text-align:center">Status</th>
            <th rowspan="2" style="vertical-align: middle; text-align:center">Aksi</th>
        </tr>
        <tr>
            <th style="text-align:center">Kamar</th>
            <th style="text-align:center">Layanan</th>
            <th style="text-align:center">Total</th>
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
            <td style="text-align:center"><b><?=$r->reservation_id?></b></td>
            <td>
                <strong><?=$r->full_name?></strong><br>
                <small class="muted">Room: <?=$r->room_type_name?></small>
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
            <td style="text-align:right; font-weight:bold;">Rp <?=number_format($r->grand_total)?></td>
            
            <td style="text-align:center">
                <?php 
                    // Badge Status Reservasi
                    $badge = 'badge-warning';
                    if($r->status == 'Confirmed') $badge = 'badge-info';
                    if($r->status == 'Checked-in') $badge = 'badge-success';
                    if($r->status == 'Cancelled') $badge = 'badge-important';
                ?>
                <span class="badge <?=$badge?>" style="margin-bottom: 5px; display:inline-block; width:80px;"><?=$r->status?></span>
                <br>

                <?php if ($r->payment_status == 'Unpaid') { ?>
                    <a href="proses.php?aksi=ubah_bayar&reservation_id=<?=$r->reservation_id?>&status_baru=Paid&jwt=<?=$_COOKIE['jwt']?>" 
                       class="btn btn-mini btn-danger" 
                       onclick="return confirm('Tandai reservasi ini LUNAS (Paid)?')"
                       title="Klik untuk melunasi">
                       <i class="icon-remove-circle icon-white"></i> Unpaid
                    </a>
                <?php } else { ?>
                    <a href="proses.php?aksi=ubah_bayar&reservation_id=<?=$r->reservation_id?>&status_baru=Unpaid&jwt=<?=$_COOKIE['jwt']?>" 
                       class="btn btn-mini btn-success" 
                       onclick="return confirm('Ubah status kembali menjadi BELUM BAYAR (Unpaid)?')"
                       title="Klik untuk membatalkan pelunasan">
                       <i class="icon-ok-circle icon-white"></i> Paid
                    </a>
                <?php } ?>
            </td>
			
            <td style="text-align:center;">
                <a href="?page=ubah&id=<?=$r->reservation_id?>" class="btn btn-medium btn-success" title="Edit"><i class="icon-pencil"></i></a>
                <a href="proses.php?aksi=hapus&reservation_id=<?=$r->reservation_id?>&jwt=<?=$_COOKIE['jwt']?>" class="btn btn-medium btn-danger" onclick="return confirm('Hapus data ini beserta tagihannya?')" title="Hapus"><i class="icon-remove"></i></a>
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

<?php } else { ?>
<legend>Selamat Datang</legend>
    <div class="hero-unit">
        <h2>Sistem Management Hotel</h2>
        <p>Sistem informasi untuk mengelola reservasi kamar, layanan tambahan, dan tagihan tamu.</p>
        
        <?php if(!isset($_COOKIE['jwt'])) { ?>
            <p><a class="btn btn-primary btn-large" href="?page=login">Login Staff &raquo;</a></p>
		<?php } else if (isset($_GET['page']) && $_GET['page']=='ubah' && isset($_COOKIE['jwt'])) { ?>
		<?php } else if (isset($_GET['page']) && $_GET['page']=='data-transaksi' && isset($_COOKIE['jwt'])) { ?>
        <?php } else { ?>
            <p>Halo, <strong><?=$_COOKIE['username']?></strong> (<?=$_COOKIE['role']?>)</p>
            <a class="btn btn-info" href="?page=tambah">Buat Reservasi Baru</a>
            <a class="btn" href="?page=data-transaksi">Lihat Transaksi</a>
        <?php } ?>
    </div>
</fieldset>
</div>
<?php } ?>

<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/tooltip.js"></script>

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