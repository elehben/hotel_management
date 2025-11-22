-- --------------------------------------------------------
-- Skema Database Sistem Reservasi Hotel
-- --------------------------------------------------------

-- Mengatur zona waktu dan menonaktifkan pemeriksaan foreign key sementara
SET time_zone = "+07:00";
SET FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS `hotel_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `hotel_management`;

-- --------------------------------------------------------
-- 1. Tabel Guests (Tamu)
-- Menyimpan data unik tamu
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Guests`;
CREATE TABLE `Guests` (
  `guest_id` CHAR(16) PRIMARY KEY, -- Misal: Nomor KTP
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone_number` VARCHAR(20) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Tabel Rooms (Kamar)
-- Menyimpan data inventaris kamar fisik hotel
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Rooms`;
CREATE TABLE `Rooms` (
  `room_id` CHAR(3) PRIMARY KEY,
  `room_type_name` VARCHAR(50) NOT NULL,
  `base_price` DECIMAL(10, 2) NOT NULL,
  `max_occupancy` INT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Available' COMMENT 'Contoh: Available, Cleaning, Maintenance'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Tabel Services (Layanan)
-- Katalog atau menu layanan tambahan yang ditawarkan
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Services`;
CREATE TABLE `Services` (
  `service_id` CHAR(3) PRIMARY KEY,
  `service_name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'General' COMMENT 'Contoh: F&B, Laundry, Spa',
  `is_available` BOOLEAN NOT NULL DEFAULT true
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Tabel Reservations (Reservasi)
-- Transaksi inti yang menghubungkan tamu dan kamar
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Reservations`;
CREATE TABLE `Reservations` (
  `reservation_id` CHAR(3) PRIMARY KEY,
  `guest_id` CHAR(16) NULL,
  `room_id` CHAR(3) NULL,
  `check_in_date` DATE NOT NULL,
  `check_out_date` DATE NOT NULL,
  `total_room_cost` DECIMAL(12, 2) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Pending' COMMENT 'Contoh: Pending, Confirmed, Checked-in, Cancelled',
  
  -- Definisi Foreign Key
  FOREIGN KEY (`guest_id`) REFERENCES `Guests`(`guest_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `Rooms`(`room_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Tabel Guest_Charges (Tagihan Layanan Tamu)
-- Jurnal pencatatan layanan yang digunakan per reservasi
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Guest_Charges`;
CREATE TABLE `Guest_Charges` (
  `charge_id` CHAR(3) PRIMARY KEY,
  `reservation_id` CHAR(3) NULL,
  `service_id` CHAR(3) NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `charge_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `amount_charged` DECIMAL(10, 2) NOT NULL COMMENT 'Hasil dari (Services.price * quantity)',
  
  -- Definisi Foreign Key
  -- Jika reservasi dihapus, tagihannya ikut terhapus (CASCADE)
  FOREIGN KEY (`reservation_id`) REFERENCES `Reservations`(`reservation_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  -- Jika layanan dihapus, tagihan tetap ada datanya (SET NULL)
  FOREIGN KEY (`service_id`) REFERENCES `Services`(`service_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Tabel Users (Pengguna Sistem)
-- Login ke sistem untuk admin dan staf resepsionis
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Users`;
CREATE TABLE `Users` (
  `user_id` CHAR(3) PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` char(40) NOT NULL, -- Disarankan menyimpan hash password (e.g., MD5/Bcrypt)
  `role` ENUM('Admin', 'Receptionist') DEFAULT 'Receptionist',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 7. Tabel Guest_Bills (Tagihan Tamu)
-- Data semua Tagihan Tamu
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Guest_Bills`;
CREATE TABLE `Guest_Bills` (
  `bill_id` INT AUTO_INCREMENT PRIMARY KEY,
  `reservation_id` CHAR(3) NOT NULL UNIQUE, -- Satu reservasi punya satu tagihan utama
  `room_id` CHAR(3) NOT NULL,
  `total_payment` DECIMAL(15, 2) DEFAULT 0.00 COMMENT 'Total Room Cost + Total Services',
  `payment_status` ENUM('Unpaid', 'Paid') DEFAULT 'Unpaid',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`reservation_id`) REFERENCES `Reservations`(`reservation_id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_id`) REFERENCES `Rooms`(`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Memasukkan Data Contoh (Sample Data)
-- --------------------------------------------------------

-- 1. Data Guests
INSERT INTO `Guests` (`guest_id`,`full_name`, `email`, `phone_number`) VALUES
('3201112223330001', 'Ahmad Subagja', 'ahmad@example.com', '081234567890'),
('3201128643200004', 'Anton Setiawan', 'anton@example.com', '081387481974'),
('3201445566770002', 'Bunga Citra', 'bunga@example.com', '081298765432'),
('3201778899000003', 'Charles Darmawan', 'charles@example.com', '081511223344');

-- 2. Data Rooms
INSERT INTO `Rooms` (`room_id`, `room_type_name`, `base_price`, `max_occupancy`, `status`) VALUES
('101', 'Standard', 500000.00, 2, 'Available'),
('102', 'Medium', 1000000.00, 2, 'Available'),
('201', 'Deluxe', 850000.00, 2, 'Cleaning'),
('301', 'Suite', 1500000.00, 4, 'Available');

-- 3. Data Services
INSERT INTO `Services` (`service_id`,`service_name`, `description`, `price`, `category`, `is_available`) VALUES
('001','Laundry Kemeja', 'Cuci setrika 1 kemeja', 25000.00, 'Laundry', true),
('002','Nasi Goreng Spesial', 'Room service nasi goreng dengan ayam dan telur', 75000.00, 'F&B', true),
('003','Spa Pijat Tradisional', 'Pijat 60 menit', 250000.00, 'Spa', true),
('004','Antar Jemput Bandara', 'Satu kali perjalanan ke/dari bandara', 150000.00, 'Transport', true);

-- 4. Data Reservations (Menggunakan ID dari Guests dan Rooms)
-- Asumsi: Ahmad (ID: 1), Bunga (ID: 2)
-- Asumsi: Room 101 (ID: 1), Room 301 (ID: 4)
INSERT INTO `Reservations` (`reservation_id`,`guest_id`, `room_id`, `check_in_date`, `check_out_date`, `total_room_cost`, `status`) VALUES
('001', '3201112223330001', '201', '2025-12-01', '2025-12-03', 1700000.00, 'Confirmed'),
('002', '3201445566770002', '301', '2025-12-05', '2025-12-07', 3000000.00, 'Pending'),
('003', '3201778899000003', '301', '2025-12-10', '2025-12-12', 3000000.00, 'Checked-in');

-- 5. Data Guest_Charges (Menggunakan ID dari Reservations dan Services)
-- Asumsi: Reservasi #1 (Ahmad) pesan 2 Nasi Goreng (ID: 2)
-- Asumsi: Reservasi #3 (Ahmad) pesan 3 Laundry (ID: 1)
INSERT INTO `Guest_Charges` (`charge_id`,`reservation_id`, `service_id`, `quantity`, `amount_charged`) VALUES
('001', '001', '002', 2, 150000.00),
('002', '001', '001', 3, 75000.00),
('003', '002', '003', 1, 250000.00),
('004', '003', '004', 1, 150000.00);

-- 6. Data Users
INSERT INTO `Users` (`user_id`, `username`, `password`, `role`) VALUES 
('001', 'admin', SHA('123'), 'Admin'),
('002', 'resepsionis1', SHA('111'), 'Receptionist');

-- 7. Data Guest_Bills (Menghitung total_payment dari Reservations dan Guest_Charges)
INSERT INTO Guest_Bills (reservation_id, room_id, total_payment, payment_status)
SELECT 
    r.reservation_id,
    r.room_id,
    -- Rumus: Harga Kamar + Total Charge (jika tidak ada charge, dianggap 0)
    (r.total_room_cost + COALESCE(SUM(gc.amount_charged), 0)) AS total_final,
    'Unpaid' -- Default status
FROM Reservations r
LEFT JOIN Guest_Charges gc ON r.reservation_id = gc.reservation_id
-- Mencegah error duplikat jika data sudah ada sebagian
WHERE r.reservation_id NOT IN (SELECT reservation_id FROM Guest_Bills)
GROUP BY r.reservation_id, r.room_id, r.total_room_cost;

-- Mengaktifkan kembali pemeriksaan foreign key
SET FOREIGN_KEY_CHECKS=1;

-- 1. Trigger untuk menghitung amount_charged di Guest_Charges Before Insert
-- Dumping structure for trigger Hotel_Management.trg_calc_amount_charged
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_calc_amount_charged
BEFORE INSERT ON Guest_Charges
FOR EACH ROW
BEGIN
    DECLARE harga_layanan DECIMAL(10,2);

    -- Ambil harga layanan
    SELECT price INTO harga_layanan
    FROM Services
    WHERE service_id = NEW.service_id;

    -- Hitung total charge
    SET NEW.amount_charged = harga_layanan * NEW.quantity;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- 2. Trigger untuk menghitung total_room_cost di Reservations Before Insert
-- Dumping structure for trigger Hotel_Management.trg_calc_total_room_cost
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_calc_total_room_cost
BEFORE INSERT ON Reservations
FOR EACH ROW
BEGIN
    DECLARE lama_hari INT;
    DECLARE harga_kamar DECIMAL(10,2);

    -- Hitung lama hari
    SET lama_hari = DATEDIFF(NEW.check_out_date, NEW.check_in_date);

    -- Ambil base price dari Rooms
    SELECT base_price INTO harga_kamar
    FROM Rooms
    WHERE room_id = NEW.room_id;

    -- Hitung biaya total kamar
    SET NEW.total_room_cost = harga_kamar * lama_hari;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- 3. Trigger untuk memperbarui amount_charged di Guest_Charges Before Update
-- Dumping structure for trigger Hotel_Management.trg_update_amount_charged
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_update_amount_charged
BEFORE UPDATE ON Guest_Charges
FOR EACH ROW
BEGIN
    DECLARE harga_layanan DECIMAL(10, 2);

    -- Ambil harga layanan terbaru
    SELECT price INTO harga_layanan
    FROM Services
    WHERE service_id = NEW.service_id;

    -- Hitung ulang total amount_charged
    SET NEW.amount_charged = harga_layanan * NEW.quantity;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- 4. Trigger untuk memperbarui total_room_cost di Reservations Before Update
-- Dumping structure for trigger Hotel_Management.trg_update_total_room_cost
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_update_total_room_cost
BEFORE UPDATE ON Reservations
FOR EACH ROW
BEGIN
    DECLARE lama_hari INT;
    DECLARE harga_kamar DECIMAL(10,2);

    -- Hitung ulang lama hari
    SET lama_hari = DATEDIFF(NEW.check_out_date, NEW.check_in_date);

    -- Ambil base price dari Rooms jika room_id berubah
    SELECT base_price INTO harga_kamar
    FROM Rooms
    WHERE room_id = NEW.room_id;

    -- Update total_room_cost
    SET NEW.total_room_cost = harga_kamar * lama_hari;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- 5. Trigger untuk membuat entri di Guest_Bills setelah insert di Reservations
DELIMITER //
CREATE TRIGGER trg_create_bill_after_reservation
AFTER INSERT ON Reservations
FOR EACH ROW
BEGIN
    INSERT INTO Guest_Bills (reservation_id, room_id, total_payment)
    VALUES (NEW.reservation_id, NEW.room_id, NEW.total_room_cost);
END//
DELIMITER ;

-- 6. Trigger untuk memperbarui total_payment di Guest_Bills setelah update di Reservations
DELIMITER //
CREATE TRIGGER trg_update_bill_on_res_change
AFTER UPDATE ON Reservations
FOR EACH ROW
BEGIN
    -- Update total hanya jika biaya kamar berubah
    IF OLD.total_room_cost <> NEW.total_room_cost THEN
        UPDATE Guest_Bills 
        SET total_payment = (total_payment - OLD.total_room_cost) + NEW.total_room_cost
        WHERE reservation_id = NEW.reservation_id;
    END IF;
END//
DELIMITER ;

-- 7. Trigger untuk menambahkan amount_charged ke total_payment di Guest_Bills setelah insert di Guest_Charges
DELIMITER //
CREATE TRIGGER trg_add_service_to_bill
AFTER INSERT ON Guest_Charges
FOR EACH ROW
BEGIN
    -- Tambahkan amount_charged baru ke total_payment di Guest_Bills
    UPDATE Guest_Bills 
    SET total_payment = total_payment + NEW.amount_charged
    WHERE reservation_id = NEW.reservation_id;
END//
DELIMITER ;

-- 8. Trigger untuk mengurangi amount_charged dari total_payment di Guest_Bills setelah delete di Guest_Charges
DELIMITER //
CREATE TRIGGER trg_edit_service_on_bill
AFTER UPDATE ON Guest_Charges
FOR EACH ROW
BEGIN
    -- Kurangi jumlah lama, tambah jumlah baru
    UPDATE Guest_Bills 
    SET total_payment = total_payment - OLD.amount_charged + NEW.amount_charged
    WHERE reservation_id = NEW.reservation_id;
END//
DELIMITER ;

-- --- Selesai ---