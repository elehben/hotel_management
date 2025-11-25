<?php
error_reporting(0); // Matikan error reporting untuk produksi agar tidak merusak format JSON

// Pastikan file-file library JWT tersedia
include_once 'core.php'; 
include_once 'lib/php-jwt/src/BeforeValidException.php';
include_once 'lib/php-jwt/src/ExpiredException.php';
include_once 'lib/php-jwt/src/SignatureInvalidException.php';
include_once 'lib/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;

include_once "database.php";
$abc = new Database();

// --- HEADER CORS ---
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Content-Type: application/json; charset=UTF-8");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 3600'); 
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

$postdata = file_get_contents("php://input");
$data = json_decode($postdata);

// ============================================================================
// LOGIC LOGIN (ADMIN / STAFF)
// ============================================================================
if ($_SERVER['REQUEST_METHOD']=='POST' and ($data->aksi=='login'))
{ 
    // Menggunakan username sesuai tabel Users
    $input_login['username'] = $data->username;
    $input_login['password'] = $data->password;
  
    // Cek login ke tabel Users (Admin/Receptionist)
    $user_data = $abc->login($input_login);

    if ($user_data){    
        // Generate JSON Web Token (JWT)
        $token = array(
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "iss" => $issuer,
            "data" => array(
                "user_id" => $user_data['user_id'], 
                "username" => $user_data['username'],
                "role" => $user_data['role'] // Penting untuk hak akses frontend
            )
        ); 
        
        http_response_code(200); 
        $jwt = JWT::encode($token, $key);
        
        echo json_encode(
            array(
                "pesan" => "Login sukses",
                "user_id" => $user_data['user_id'],
                "username" => $user_data['username'],
                "role" => $user_data['role'],
                "jwt" => $jwt
            )
        ); 
    } else { 
        http_response_code(401); 
        echo json_encode(array("pesan" => "Login gagal: Username atau Password salah"));
    }
}

// ============================================================================
// LOGIC POST (TAMBAH, UBAH, HAPUS RESERVASI)
// ============================================================================
elseif ($_SERVER['REQUEST_METHOD']=='POST')
{   
    // Ambil token dari header atau body (disesuaikan dengan cara frontend kirim)
    // Asumsi di sini dikirim via body atau header Authorization (perlu penyesuaian jika via header)
    $jwt = isset($data->jwt) ? $data->jwt : "";

    try { 
        // Verifikasi Token
        // Jika via Header Authorization: $headers = apache_request_headers(); $jwt = $headers['Authorization'];
        JWT::decode($jwt, $key, array('HS256')); 

        $aksi = $data->aksi;

        if ($aksi == 'tambah'){ 
            $input_data = array(
                // reservation_id DIBUANG, karena generate otomatis di database.php
                'guest_id'       => $data->guest_id,
                'room_id'        => $data->room_id,
                'check_in'       => $data->check_in_date,
                'check_out'      => $data->check_out_date
                // total_cost tidak dikirim, DB trigger yang menghitung
            );
            
            // Array service ID, misal: ["001", "003"]
            $services = isset($data->services) ? $data->services : []; 
            
            $abc->tambah_reservasi($input_data, $services);
            $pesan = "Reservasi berhasil disimpan";

        } elseif ($aksi == 'ubah') { 
            $input_data = array(
                'reservation_id' => $data->reservation_id, // ID diperlukan untuk WHERE
                'room_id'        => $data->room_id,
                'check_in'       => $data->check_in_date,
                'check_out'      => $data->check_out_date,
                'status'         => $data->status
            );
            $services = isset($data->services) ? $data->services : [];

            $abc->ubah_reservasi($input_data, $services);
            $pesan = "Reservasi berhasil diperbarui";
        } 
        elseif ($aksi == 'hapus') { 
            $abc->hapus_reservasi($data->reservation_id);
            $pesan = "Data reservasi dihapus";
        }

        // --- TAMU ---
        elseif ($aksi == 'tambah_tamu') {
            $abc->tambah_tamu((array)$data);
            $pesan = "Data Tamu berhasil disimpan";
        } elseif ($aksi == 'ubah_tamu') {
            $abc->ubah_tamu((array)$data);
            $pesan = "Data Tamu berhasil diubah";
        } elseif ($aksi == 'hapus_tamu') {
            $abc->hapus_tamu($data->guest_id);
            $pesan = "Data Tamu dihapus";
        }

        // --- KAMAR ---
        elseif ($aksi == 'tambah_kamar') {
            $abc->tambah_kamar((array)$data);
            $pesan = "Data Kamar berhasil disimpan";
        } elseif ($aksi == 'ubah_kamar') {
            $abc->ubah_kamar((array)$data);
            $pesan = "Data Kamar berhasil diubah";
        } elseif ($aksi == 'hapus_kamar') {
            $abc->hapus_kamar($data->room_id);
            $pesan = "Data Kamar dihapus";
        }

        // --- LAYANAN ---
        elseif ($aksi == 'tambah_layanan') {
            $abc->tambah_layanan((array)$data);
            $pesan = "Data Layanan berhasil disimpan";
        } elseif ($aksi == 'ubah_layanan') {
            $abc->ubah_layanan((array)$data);
            $pesan = "Data Layanan berhasil diubah";
        } elseif ($aksi == 'hapus_layanan') {
            $abc->hapus_layanan($data->service_id);
            $pesan = "Data Layanan dihapus";
        }

        // --- UPDATE STATUS PEMBAYARAN ---
        elseif ($aksi == 'ubah_bayar') {
            // Menerima reservation_id dan status_baru (Paid/Unpaid)
            $abc->ubah_status_pembayaran($data->reservation_id, $data->status_baru);
            $pesan = "Status pembayaran diperbarui menjadi " . $data->status_baru;
        }
        
        // --- MANIPULASI USER ---
        elseif ($aksi == 'tambah_user') {
            $abc->tambah_user((array)$data);
            $pesan = "User baru berhasil ditambahkan";
        } 
        elseif ($aksi == 'hapus_user') {
            $result = $abc->hapus_user($data->user_id);
            if($result) {
                $pesan = "User berhasil dihapus";
            } else {
                http_response_code(400); // Bad Request
                $pesan = "Gagal: Akun Super Admin tidak boleh dihapus!";
            }
        }

        http_response_code(200); 
        echo json_encode(array("pesan" => $pesan));
    
    } catch (Exception $e) {   
        http_response_code(401);   
        echo json_encode(array("pesan" => "Akses Ditolak: Token Invalid atau Kadaluarsa"));
    }
}

// ============================================================================
// LOGIC GET (TAMPIL DATA)
// ============================================================================
elseif ($_SERVER['REQUEST_METHOD']=='GET') 
{   
    $jwt = isset($_GET['jwt']) ? $_GET['jwt'] : ""; 
    $aksi = isset($_GET['aksi']) ? $_GET['aksi'] : "";

    try { 
        JWT::decode($jwt, $key, array('HS256')); 
          
        // 1. TAMPIL SATU DATA (Untuk Edit)
        if ($aksi == 'detail' && isset($_GET['id'])){ 
            $id = $abc->filter($_GET['id']);  
            $reservasi = $abc->get_reservasi_by_id($id);
            $selected_services = $abc->get_selected_services($id);
            
            $data = array(
                'reservasi' => $reservasi,
                'selected_services' => $selected_services
            );
        }
        
        // 2. TAMPIL SEMUA DATA TRANSAKSI (Dashboard Utama)
        elseif ($aksi == 'tampil_transaksi') { 
            $data = $abc->tampil_transaksi_tamu();
        }

        // --- TAMU ---
        elseif ($aksi == 'tampil_tamu') {
            $data = $abc->tampil_semua_tamu();
        } elseif ($aksi == 'detail_tamu') {
            $data = $abc->get_tamu_by_id($_GET['id']);
        }

        // --- KAMAR ---
        elseif ($aksi == 'tampil_kamar') {
            $data = $abc->tampil_semua_kamar();
        } elseif ($aksi == 'detail_kamar') {
            $data = $abc->get_kamar_by_id($_GET['id']);
        }

        // --- LAYANAN ---
        elseif ($aksi == 'tampil_layanan') {
            $data = $abc->tampil_semua_layanan();
        } elseif ($aksi == 'detail_layanan') {
            $data = $abc->get_layanan_by_id($_GET['id']);
        }

        // 3. DATA PENDUKUNG: List Kamar (Dropdown)
        elseif ($aksi == 'list_kamar') { 
            $data = $abc->get_rooms_list();
        }

        // 4. DATA PENDUKUNG: List Service (Checkbox/Dropdown)
        elseif ($aksi == 'list_service') { 
            $data = $abc->get_services_list();
        }

        // 5. DATA PENDUKUNG: List Tamu (Dropdown saat Tambah Reservasi)
        // Fitur baru untuk memilih tamu
        elseif ($aksi == 'list_tamu') { 
            $data = $abc->get_guests_list();
        }

        // --- DATA USER ---
        elseif ($aksi == 'tampil_user') {
            $data = $abc->tampil_semua_user();
        }

        http_response_code(200); 
        echo json_encode($data); 
    
    } catch (Exception $e) {   
        http_response_code(401);   
        echo json_encode(array("pesan" => "Dilarang akses: Token Invalid"));
    }
} else { 
    http_response_code(404); 
    echo json_encode(array("pesan" => "Endpoint tidak ditemukan"));
}

// Bersihkan variabel
unset($abc, $postdata, $data, $input_login, $input_data, $token, $key, $jwt); 
?>