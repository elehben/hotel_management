<?php
class Database
{   
    private $host = "localhost";  
    private $dbname = "hotel_management"; 
    private $user = "root";
    private $password = ""; 
    private $port = "3306";
    private $conn;
    
    public function __construct()
    {   
        try {   
            $this->conn = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->dbname;charset=utf8",$this->user,$this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);     
        } catch (PDOException $e) {   
            echo "Koneksi gagal: " . $e->getMessage();           
        }
    }   

    public function filter($data)
    {   
        $data = trim($data);
        $data = htmlspecialchars($data); // Lebih aman untuk XSS
        return $data;
    }

    // --- LOGIN (Menggunakan Tabel Users) ---
    public function login($data)
    {   
        // Menggunakan tabel Users sesuai skema SQL
        // Password di SQL menggunakan SHA(), yang setara dengan SHA1 di MySQL
        $query = $this->conn->prepare("SELECT user_id, username, role FROM Users WHERE username=? AND password=SHA1(?)");
        $query->execute(array($data['username'], $data['password']));   
        return $query->fetch(PDO::FETCH_ASSOC);        
    }

    // --- HELPER: Generator ID CHAR(3) ---
    // Fungsi ini membuat ID urut (001, 002, dst) karena database tidak menggunakan Auto Increment pada CHAR(3)
    private function generate_id($table, $column) {
        $q = $this->conn->query("SELECT MAX($column) as max_id FROM $table");
        $row = $q->fetch(PDO::FETCH_ASSOC);
        $last = $row['max_id']; 
        
        if ($last) {
            $num = (int)$last + 1;
        } else {
            $num = 1;
        }
        return sprintf("%03d", $num); // Format 001, 002...
    }

    // --- TAMPIL DATA ---
    public function tampil_transaksi_tamu()
    {   
        $sql = "SELECT 
                    r.reservation_id,
                    g.full_name,
                    rm.room_type_name,
                    rm.base_price,
                    r.check_in_date,
                    r.check_out_date,
                    r.status,
                    
                    -- 1. Biaya Kamar
                    r.total_room_cost, 
                    
                    -- 2. Grand Total (Diambil dari Guest_Bills)
                    COALESCE(gb.total_payment, 0) as grand_total,
                    gb.payment_status,

                    -- 3. PERBAIKAN DISINI: Menghitung Biaya Layanan (Total - Kamar)
                    (COALESCE(gb.total_payment, 0) - r.total_room_cost) as total_service_cost,

                    -- 4. List nama layanan untuk display
                    (
                        SELECT GROUP_CONCAT(CONCAT(s.service_name, ' (', gc.quantity, ')') SEPARATOR ', ')
                        FROM Guest_Charges gc
                        JOIN Services s ON gc.service_id = s.service_id
                        WHERE gc.reservation_id = r.reservation_id
                    ) as list_layanan

                FROM Reservations r
                JOIN Guests g ON r.guest_id = g.guest_id
                JOIN Rooms rm ON r.room_id = rm.room_id
                LEFT JOIN Guest_Bills gb ON r.reservation_id = gb.reservation_id
                ORDER BY r.check_in_date DESC";

        $query = $this->conn->prepare($sql);
        $query->execute();  
        return $query->fetchAll(PDO::FETCH_ASSOC);   
    }

    // --- CRUD RESERVASI ---

    public function tambah_reservasi($data, $services = [])
    {   
        // 1. Generate Reservation ID Baru
        $new_res_id = $this->generate_id('Reservations', 'reservation_id');

        // 2. Insert Reservasi
        // total_room_cost dikirim 0, Trigger 'trg_calc_total_room_cost' akan menghitung otomatis
        $query = $this->conn->prepare("INSERT INTO Reservations (reservation_id, guest_id, room_id, check_in_date, check_out_date, total_room_cost, status) VALUES (?, ?, ?, ?, ?, 0, 'Pending')");
        
        $query->execute(array(
            $new_res_id,
            $data['guest_id'],
            $data['room_id'],
            $data['check_in'],
            $data['check_out']
        ));   

        // 3. Insert Services (Jika ada)
        if (!empty($services)) {
            foreach ($services as $srv_id) {
                // Generate ID Charge per item
                $new_charge_id = $this->generate_id('Guest_Charges', 'charge_id');
                
                // amount_charged dikirim 0, Trigger 'trg_calc_amount_charged' akan menghitung otomatis
                $q_charge = $this->conn->prepare("INSERT INTO Guest_Charges (charge_id, reservation_id, service_id, quantity, amount_charged) VALUES (?, ?, ?, 1, 0)");
                $q_charge->execute([$new_charge_id, $new_res_id, $srv_id]);
            }
        }
        return true;
    }

    public function ubah_reservasi($data, $services = [])
    {   
        // 1. Update Data Reservasi (Kamar/Tanggal)
        // Trigger trg_update_total_room_cost di DB akan jalan otomatis update harga kamar
        $query = $this->conn->prepare("UPDATE Reservations SET room_id=?, check_in_date=?, check_out_date=?, status=? WHERE reservation_id=?");
        $query->execute(array(
            $data['room_id'],
            $data['check_in'],
            $data['check_out'],
            $data['status'],
            $data['reservation_id']
        ));   

        // 2. HAPUS Layanan Lama
        // Trigger trg_recalc_bill_after_delete_service (YANG BARU KITA BUAT) akan jalan
        // Total Payment akan turun kembali ke Harga Kamar saja
        $del = $this->conn->prepare("DELETE FROM Guest_Charges WHERE reservation_id = ?");
        $del->execute([$data['reservation_id']]);

        // 3. INSERT Layanan Baru
        // Trigger trg_recalc_bill_after_insert_service akan jalan
        // Total Payment akan naik sesuai jumlah layanan baru
        if (!empty($services)) {
            foreach ($services as $srv_id) {
                $new_charge_id = $this->generate_id('Guest_Charges', 'charge_id');
                $q_charge = $this->conn->prepare("INSERT INTO Guest_Charges (charge_id, reservation_id, service_id, quantity, amount_charged) VALUES (?, ?, ?, 1, 0)");
                $q_charge->execute([$new_charge_id, $data['reservation_id'], $srv_id]);
            }
        }
    }

    public function hapus_reservasi($id)
    {   
        // Karena ON DELETE CASCADE di SQL, menghapus reservasi akan otomatis menghapus:
        // 1. Data di Guest_Charges
        // 2. Data di Guest_Bills
        $query = $this->conn->prepare("DELETE FROM Reservations WHERE reservation_id=?");
        $query->execute(array($id));   
    }

	// ============================================================
    // FITUR PEMBAYARAN (PAYMENT)
    // ============================================================
    public function ubah_status_pembayaran($reservation_id, $status_baru) {
        // Validasi input status agar sesuai ENUM database
        if ($status_baru !== 'Paid' && $status_baru !== 'Unpaid') {
            return false;
        }

        $query = $this->conn->prepare("UPDATE Guest_Bills SET payment_status = ? WHERE reservation_id = ?");
        $query->execute([$status_baru, $reservation_id]);
        return true;
    }

    // --- DATA PENDUKUNG (Dropdowns) ---

    public function get_reservasi_by_id($id)
    {   
        $query = $this->conn->prepare("SELECT * FROM Reservations WHERE reservation_id=?");
        $query->execute(array($id));   
        return $query->fetch(PDO::FETCH_ASSOC);       
    }

    public function get_selected_services($reservation_id)
    {
        $query = $this->conn->prepare("SELECT service_id FROM Guest_Charges WHERE reservation_id = ?");
        $query->execute([$reservation_id]);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public function get_rooms_list()
    {
        $query = $this->conn->prepare("SELECT room_id, room_type_name, base_price, status FROM Rooms ORDER BY room_id ASC");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_services_list()
    {
        $query = $this->conn->prepare("SELECT service_id, service_name, price FROM Services WHERE is_available = 1 ORDER BY service_name ASC");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // BARU: Mengambil data tamu untuk dropdown insert reservasi
    public function get_guests_list()
    {
        $query = $this->conn->prepare("SELECT guest_id, full_name, phone_number FROM Guests ORDER BY full_name ASC");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================================
    // CRUD TAMU (GUESTS)
    // ============================================================
    public function tampil_semua_tamu() {
        $query = $this->conn->prepare("SELECT * FROM Guests ORDER BY full_name ASC");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_tamu_by_id($id) {
        $query = $this->conn->prepare("SELECT * FROM Guests WHERE guest_id=?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function tambah_tamu($data) {
        $query = $this->conn->prepare("INSERT INTO Guests (guest_id, full_name, email, phone_number) VALUES (?,?,?,?)");
        $query->execute([$data['guest_id'], $data['full_name'], $data['email'], $data['phone_number']]);
    }

    public function ubah_tamu($data) {
        $query = $this->conn->prepare("UPDATE Guests SET full_name=?, email=?, phone_number=? WHERE guest_id=?");
        $query->execute([$data['full_name'], $data['email'], $data['phone_number'], $data['guest_id']]);
    }

    public function hapus_tamu($id) {
        $query = $this->conn->prepare("DELETE FROM Guests WHERE guest_id=?");
        $query->execute([$id]);
    }

    // ============================================================
    // CRUD KAMAR (ROOMS)
    // ============================================================
    public function tampil_semua_kamar() {
        $query = $this->conn->prepare("SELECT * FROM Rooms ORDER BY room_id ASC");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_kamar_by_id($id) {
        $query = $this->conn->prepare("SELECT * FROM Rooms WHERE room_id=?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function tambah_kamar($data) {
        $query = $this->conn->prepare("INSERT INTO Rooms (room_id, room_type_name, base_price, max_occupancy, status) VALUES (?,?,?,?,?)");
        $query->execute([$data['room_id'], $data['room_type_name'], $data['base_price'], $data['max_occupancy'], $data['status']]);
    }

    public function ubah_kamar($data) {
        $query = $this->conn->prepare("UPDATE Rooms SET room_type_name=?, base_price=?, max_occupancy=?, status=? WHERE room_id=?");
        $query->execute([$data['room_type_name'], $data['base_price'], $data['max_occupancy'], $data['status'], $data['room_id']]);
    }

    public function hapus_kamar($id) {
        $query = $this->conn->prepare("DELETE FROM Rooms WHERE room_id=?");
        $query->execute([$id]);
    }

    // ============================================================
    // CRUD LAYANAN (SERVICES)
    // ============================================================
    public function tampil_semua_layanan() {
        $query = $this->conn->prepare("SELECT * FROM Services ORDER BY service_name ASC");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_layanan_by_id($id) {
        $query = $this->conn->prepare("SELECT * FROM Services WHERE service_id=?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function tambah_layanan($data) {
        $query = $this->conn->prepare("INSERT INTO Services (service_id, service_name, description, price, category, is_available) VALUES (?,?,?,?,?,?)");
        $query->execute([$data['service_id'], $data['service_name'], $data['description'], $data['price'], $data['category'], $data['is_available']]);
    }

    public function ubah_layanan($data) {
        $query = $this->conn->prepare("UPDATE Services SET service_name=?, description=?, price=?, category=?, is_available=? WHERE service_id=?");
        $query->execute([$data['service_name'], $data['description'], $data['price'], $data['category'], $data['is_available'], $data['service_id']]);
    }

    public function hapus_layanan($id) {
        $query = $this->conn->prepare("DELETE FROM Services WHERE service_id=?");
        $query->execute([$id]);
    }

    // ... (Letakkan di dalam Class Database, sebelum penutup kurung kurawal '}') ...

    // ============================================================
    // MANAJEMEN USER (KHUSUS ADMIN)
    // ============================================================
    
    public function tampil_semua_user() {
        $query = $this->conn->prepare("SELECT user_id, username, role, created_at FROM Users ORDER BY user_id ASC");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tambah_user($data) {
        // Generate User ID Baru (Format: 001, 002...)
        $new_id = $this->generate_id('Users', 'user_id');

        // Simpan password dengan SHA1 (Sesuai format login yang ada)
        $query = $this->conn->prepare("INSERT INTO Users (user_id, username, password, role) VALUES (?, ?, SHA1(?), ?)");
        
        $query->execute(array(
            $new_id, 
            $data['username'], 
            $data['password'], 
            $data['role']
        ));
    }

    public function hapus_user($id) {
        // Mencegah penghapusan akun Admin Utama (001)
        if ($id == '001') {
            return false;
        }
        $query = $this->conn->prepare("DELETE FROM Users WHERE user_id=?");
        $query->execute([$id]);
        return true;
    }
}
?>