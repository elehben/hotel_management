<?php
error_reporting(1); // error ditampilkan

class Client
{   
    private $url;

    // Diload pertama kali
    public function __construct($url)
    {   
        $this->url = $url;
    }   

    // --- FUNGSI LOGIN ---
    public function login($data)
    {   
        // SESUAIKAN: Login menggunakan username (bukan email) sesuai tabel Users
        $payload = json_encode(array(
            "username" => $data['username'],
            "password" => $data['password'],
            "aksi"     => $data['aksi']
        ));

        return $this->send_post_request($payload);
    }

    // --- FUNGSI TAMPIL DATA (GET) ---

    // 1. Menampilkan Tabel Transaksi Utama
    public function tampil_semua_data($jwt)
    {   
        $url_req = $this->url . "?aksi=tampil_transaksi&jwt=" . $jwt;
        return $this->send_get_request($url_req);
    }
    
    // 2. Menampilkan Detail Satu Reservasi (Untuk Edit)
    public function tampil_data($data)
    {   
        $id = $data['reservation_id'];
        $jwt = $data['jwt'];
        $url_req = $this->url . "?aksi=detail&id=" . $id . "&jwt=" . $jwt;
        return $this->send_get_request($url_req);      
    }   

    // 3. Helper: Mengambil Data List Kamar (Untuk Dropdown)
    public function get_list_kamar($jwt)
    {
        $url_req = $this->url . "?aksi=list_kamar&jwt=" . $jwt;
        return $this->send_get_request($url_req);
    }

    // 4. Helper: Mengambil Data List Service (Untuk Checkbox/Dropdown)
    public function get_list_service($jwt)
    {
        $url_req = $this->url . "?aksi=list_service&jwt=" . $jwt;
        return $this->send_get_request($url_req);
    }

    // 5. BARU: Helper Mengambil Data List Tamu (Untuk Dropdown saat Tambah Reservasi)
    public function get_list_tamu($jwt)
    {
        $url_req = $this->url . "?aksi=list_tamu&jwt=" . $jwt;
        return $this->send_get_request($url_req);
    }

    // --- FUNGSI MANIPULASI DATA (POST) ---
    public function tambah_data($data)
    {   
        $payload = json_encode(array(
            // HAPUS: reservation_id tidak dikirim, server yang generate
            // HAPUS: total_room_cost tidak dikirim, database trigger yang hitung
            
            "guest_id"       => $data['guest_id'],
            "room_id"        => $data['room_id'],
            "check_in_date"  => $data['check_in_date'],
            "check_out_date" => $data['check_out_date'],
            
            // Array ID Services (contoh: ["001", "003"])
            "services"       => isset($data['services']) ? $data['services'] : [], 
            
            "jwt"            => $data['jwt'],
            "aksi"           => $data['aksi']
        ));
        return $this->send_post_request($payload);
    }

    public function ubah_data($data)
    {   
        $payload = json_encode(array(
            "reservation_id" => $data['reservation_id'], // ID diperlukan untuk WHERE clause
            "room_id"        => $data['room_id'],
            "check_in_date"  => $data['check_in_date'],
            "check_out_date" => $data['check_out_date'],
            "status"         => $data['status'],
            
            // HAPUS: total_room_cost tidak perlu diupdate manual
            
            "services"       => isset($data['services']) ? $data['services'] : [], 
            "jwt"            => $data['jwt'],
            "aksi"           => $data['aksi']
        ));
        return $this->send_post_request($payload);
    }
    
    public function hapus_data($data)
    {   
        $payload = json_encode(array(
            "reservation_id" => $data['reservation_id'],
            "jwt"            => $data['jwt'],
            "aksi"           => $data['aksi']
        ));

        return $this->send_post_request($payload);
    }

    // --- GENERAL CRUD HELPER (Untuk mempersingkat kode) ---
    public function crud_general($data) {
        // Mengirim semua data POST ke API
        return $this->send_post_request(json_encode($data));
    }

    public function get_general($jwt, $aksi, $id = null) {
        $url = $this->url . "?aksi=" . $aksi . "&jwt=" . $jwt;
        if ($id) {
            $url .= "&id=" . $id;
        }
        return $this->send_get_request($url);
    }
    
    // --- HELPER PRIVATE UNTUK CURL ---
    private function send_post_request($payload)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $this->url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($c);
        curl_close($c);
        return json_decode($response);
    }

    private function send_get_request($url_target)
    {
        $client = curl_init($url_target);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($client);
        curl_close($client);
        return json_decode($response);    
    }

    public function filter($data)
    {   
        $data = preg_replace('/[^a-zA-Z0-9]/','',$data);
        return $data;
    }

    public function __destruct()
    {   
        unset($this->url);  
    }
}

// Setup URL API 
// Pastikan nama file servernya (api.php atau server.php) SESUAI dengan yang ada di folder server Anda
// $url = 'http://192.168.56.2/hotel_management/server/server.php'; 
$url = 'http://192.168.1.185/hotel_management/server/server.php'; 

// Buat objek baru dari class Client
$abc = new Client($url);
?>