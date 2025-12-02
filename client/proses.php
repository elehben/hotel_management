<?php
include "client.php";

// Menangani semua request POST (Tambah & Ubah)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Login logic (tetap sama)
	// --- 1. PROSES LOGIN ---
    if ($_POST['aksi'] == 'login') {
        // ... (kode login lama) ...
		$data = array(
        "username" => $_POST['username'], // Ubah email jadi username (sesuai tabel Users)
        "password" => $_POST['password'], 
        "aksi"     => $_POST['aksi']
		);      
		
		// Memanggil fungsi login di client.php
		$data2 = $abc->login($data);

		// Jika login berhasil (token diterima)
		if (isset($data2->jwt)) {
			// Simpan token dan data user ke cookie
			setcookie('jwt', $data2->jwt, time()+3600, "/"); 
			
			// Simpan data User (Admin/Staff) bukan Guest
			setcookie('user_id', $data2->user_id, time()+3600, "/"); 
			setcookie('username', $data2->username, time()+3600, "/");
			setcookie('role', $data2->role, time()+3600, "/"); // Simpan role (Admin/Receptionist)
			
			// Redirect ke halaman data transaksi
			header('location:index.php?page=home'); 
		} else {
			// Login gagal
			header('location:index.php?page=login&pesan=gagal'); 
		}   

	// --- 2. PROSES TAMBAH RESERVASI ---
	} else if ($_POST['aksi'] == 'tambah')
	{   
		$data = array(
			// reservation_id DIHAPUS (Auto-generate di Server/DB)
			"guest_id"       => $_POST['guest_id'], 
			"room_id"        => $_POST['room_id'],
			"check_in_date"  => $_POST['check_in_date'],
			"check_out_date" => $_POST['check_out_date'],
			// total_room_cost DIHAPUS (Dihitung Trigger DB)
			
			// Ambil array services (checkbox layanan)
			"services"       => isset($_POST['services']) ? $_POST['services'] : [],
			
			"jwt"            => $_POST['jwt'],
			"aksi"           => $_POST['aksi']
		);      
		
		$abc->tambah_data($data);
		header('location:index.php?page=data-transaksi'); 

	// --- 3. PROSES UBAH RESERVASI ---
	} else if ($_POST['aksi'] == 'ubah')
	{   
		$data = array(
			"reservation_id" => $_POST['reservation_id'],
			"room_id"        => $_POST['room_id'],
			"check_in_date"  => $_POST['check_in_date'],
			"check_out_date" => $_POST['check_out_date'],
			// total_room_cost DIHAPUS
			
			"status"         => $_POST['status'],
			
			// Ambil array services (checkbox layanan)
			"services"       => isset($_POST['services']) ? $_POST['services'] : [],
			
			"jwt"            => $_POST['jwt'],
			"aksi"           => $_POST['aksi']
		);
		
		$abc->ubah_data($data);
		header('location:index.php?page=data-transaksi'); 

    // Logic Umum untuk Tambah/Ubah Data (Tamu, Kamar, Layanan, Reservasi)
    } else {
        // Kumpulkan semua data POST
        $data = $_POST; 
        // Kirim ke Client -> API
        $abc->crud_general($data);

        // Redirect sesuai aksi
        if (strpos($_POST['aksi'], 'tamu') !== false) header('location:index.php?page=data-tamu');
        elseif (strpos($_POST['aksi'], 'kamar') !== false) header('location:index.php?page=data-kamar');
        elseif (strpos($_POST['aksi'], 'layanan') !== false) header('location:index.php?page=data-layanan');
        else header('location:index.php?page=data-transaksi');
    }

// Menangani request GET (Hapus & Logout)
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {

	// --- 5. LOGOUT ---
    if ($_GET['aksi'] == 'logout') {
        // Hapus semua cookie yang diset saat login
		setcookie('jwt', '', time()-3600, "/"); 
		setcookie('user_id', '', time()-3600, "/");
		setcookie('username', '', time()-3600, "/");
		setcookie('role', '', time()-3600, "/");

        header('location:index.php?page=login'); 
    
	// --- 4. PROSES HAPUS RESERVASI ---
	} else if ($_GET['aksi'] == 'hapus') {
		$data = array(
			"reservation_id" => $_GET['reservation_id'],
			"jwt"            => $_GET['jwt'],
			"aksi"           => $_GET['aksi']
		);
		
		$abc->hapus_data($data);
		header('location:index.php?page=data-transaksi'); 

    // --- LOGIC UBAH BAYAR ---
	} else if ($_GET['aksi'] == 'ubah_bayar') {
		$data = array(
			"jwt" => $_GET['jwt'],
			"aksi" => "ubah_bayar",
			"reservation_id" => $_GET['reservation_id'],
			"status_baru" => $_GET['status_baru'] // 'Paid' atau 'Unpaid'
		);
		
		// Kirim ke API
		$abc->crud_general($data);
		
		// Kembali ke halaman transaksi
		header('location:index.php?page=data-transaksi');
		
	} else {
        // Logic Hapus General
        $data = array(
            "jwt" => $_GET['jwt'],
            "aksi" => $_GET['aksi']
        );

        // Mapping ID untuk hapus
        if (isset($_GET['guest_id'])) $data['guest_id'] = $_GET['guest_id'];
        if (isset($_GET['room_id'])) $data['room_id'] = $_GET['room_id'];
        if (isset($_GET['service_id'])) $data['service_id'] = $_GET['service_id'];
        if (isset($_GET['reservation_id'])) $data['reservation_id'] = $_GET['reservation_id'];

        $abc->crud_general($data); // Menggunakan helper crud_general yang kita buat di client.php (hapus juga pakai POST di curl backend, tapi trigger via GET di frontend)
        
        // Redirect
        if (strpos($_GET['aksi'], 'tamu') !== false) header('location:index.php?page=data-tamu');
        elseif (strpos($_GET['aksi'], 'kamar') !== false) header('location:index.php?page=data-kamar');
        elseif (strpos($_GET['aksi'], 'layanan') !== false) header('location:index.php?page=data-layanan');
        else header('location:index.php?page=data-transaksi');
    }
}

// // --- 1. PROSES LOGIN ---
// if ($_POST['aksi'] == 'login')
// {   
//     $data = array(
//         "username" => $_POST['username'], // Ubah email jadi username (sesuai tabel Users)
//         "password" => $_POST['password'], 
//         "aksi"     => $_POST['aksi']
//     );      
    
//     // Memanggil fungsi login di client.php
//     $data2 = $abc->login($data);

//     // Jika login berhasil (token diterima)
//     if (isset($data2->jwt)) {
//         // Simpan token dan data user ke cookie
//         setcookie('jwt', $data2->jwt, time()+3600, "/"); 
        
//         // Simpan data User (Admin/Staff) bukan Guest
//         setcookie('user_id', $data2->user_id, time()+3600, "/"); 
//         setcookie('username', $data2->username, time()+3600, "/");
//         setcookie('role', $data2->role, time()+3600, "/"); // Simpan role (Admin/Receptionist)
        
//         // Redirect ke halaman data transaksi
//         header('location:index.php?page=data-transaksi'); 
//     } else {
//         // Login gagal
//         header('location:index.php?page=login&pesan=gagal'); 
//     }   

// // --- 2. PROSES TAMBAH RESERVASI ---
// } else if ($_POST['aksi'] == 'tambah')
// {   
//     $data = array(
//         // reservation_id DIHAPUS (Auto-generate di Server/DB)
//         "guest_id"       => $_POST['guest_id'], 
//         "room_id"        => $_POST['room_id'],
//         "check_in_date"  => $_POST['check_in_date'],
//         "check_out_date" => $_POST['check_out_date'],
//         // total_room_cost DIHAPUS (Dihitung Trigger DB)
        
//         // Ambil array services (checkbox layanan)
//         "services"       => isset($_POST['services']) ? $_POST['services'] : [],
        
//         "jwt"            => $_POST['jwt'],
//         "aksi"           => $_POST['aksi']
//     );      
    
//     $abc->tambah_data($data);
//     header('location:index.php?page=data-transaksi'); 

// // --- 3. PROSES UBAH RESERVASI ---
// } else if ($_POST['aksi'] == 'ubah')
// {   
//     $data = array(
//         "reservation_id" => $_POST['reservation_id'],
//         "room_id"        => $_POST['room_id'],
//         "check_in_date"  => $_POST['check_in_date'],
//         "check_out_date" => $_POST['check_out_date'],
//         // total_room_cost DIHAPUS
        
//         "status"         => $_POST['status'],
        
//         // Ambil array services (checkbox layanan)
//         "services"       => isset($_POST['services']) ? $_POST['services'] : [],
        
//         "jwt"            => $_POST['jwt'],
//         "aksi"           => $_POST['aksi']
//     );
    
//     $abc->ubah_data($data);
//     header('location:index.php?page=data-transaksi'); 

// // --- 4. PROSES HAPUS RESERVASI ---
// } else if ($_GET['aksi'] == 'hapus')
// {   
//     $data = array(
//         "reservation_id" => $_GET['reservation_id'],
//         "jwt"            => $_GET['jwt'],
//         "aksi"           => $_GET['aksi']
//     );
    
//     $abc->hapus_data($data);
//     header('location:index.php?page=data-transaksi'); 

// // --- 5. LOGOUT ---
// } else if ($_GET['aksi'] == 'logout')
// {   
//     // Hapus semua cookie yang diset saat login
//     setcookie('jwt', '', time()-3600, "/"); 
//     setcookie('user_id', '', time()-3600, "/");
//     setcookie('username', '', time()-3600, "/");
//     setcookie('role', '', time()-3600, "/");
    
//     header('location:index.php?page=login'); 
// } 

// Bersihkan variabel
unset($abc, $data, $data2);
?>