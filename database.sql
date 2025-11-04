-- --------------------------------------------------------
-- Skema Database Sistem Reservasi Hotel
-- --------------------------------------------------------

-- Mengatur zona waktu dan menonaktifkan pemeriksaan foreign key sementara
SET time_zone = "+07:00";
SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- 1. Tabel Guests (Tamu)
-- Menyimpan data unik tamu
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Guests`;
CREATE TABLE `Guests` (
  `guest_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone_number` VARCHAR(20) NOT NULL UNIQUE,
  `identity_number` VARCHAR(50) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Tabel Rooms (Kamar)
-- Menyimpan data inventaris kamar fisik hotel
-- --------------------------------------------------------

DROP TABLE IF EXISTS `Rooms`;
CREATE TABLE `Rooms` (
  `room_id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_number` VARCHAR(10) NOT NULL UNIQUE,
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
  `service_id` INT AUTO_INCREMENT PRIMARY KEY,
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
  `reservation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `guest_id` INT NULL,
  `room_id` INT NULL,
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
  `charge_id` INT AUTO_INCREMENT PRIMARY KEY,
  `reservation_id` INT NOT NULL,
  `service_id` INT NULL,
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
-- Memasukkan Data Contoh (Sample Data)
-- --------------------------------------------------------

-- 1. Data Guests
INSERT INTO `Guests` (`full_name`, `email`, `phone_number`, `identity_number`) VALUES
('Ahmad Subagja', 'ahmad.s@example.com', '081234567890', '3201112223330001'),
('Bunga Citra', 'bunga.citra@example.com', '081298765432', '3201445566770002'),
('Charles Darmawan', 'charles.d@example.com', '081511223344', '3201778899000003');

-- 2. Data Rooms
INSERT INTO `Rooms` (`room_number`, `room_type_name`, `base_price`, `max_occupancy`, `status`) VALUES
('101', 'Standard', 500000.00, 2, 'Available'),
('102', 'Standard', 500000.00, 2, 'Available'),
('201', 'Deluxe', 850000.00, 2, 'Cleaning'),
('301', 'Suite', 1500000.00, 4, 'Available');

-- 3. Data Services
INSERT INTO `Services` (`service_name`, `description`, `price`, `category`, `is_available`) VALUES
('Laundry Kemeja', 'Cuci setrika 1 kemeja', 25000.00, 'Laundry', true),
('Nasi Goreng Spesial', 'Room service nasi goreng dengan ayam dan telur', 75000.00, 'F&B', true),
('Spa Pijat Tradisional', 'Pijat 60 menit', 250000.00, 'Spa', true),
('Antar Jemput Bandara', 'Satu kali perjalanan ke/dari bandara', 150000.00, 'Transport', true);

-- 4. Data Reservations (Menggunakan ID dari Guests dan Rooms)
-- Asumsi: Ahmad (ID: 1), Bunga (ID: 2)
-- Asumsi: Room 101 (ID: 1), Room 301 (ID: 4)
INSERT INTO `Reservations` (`guest_id`, `room_id`, `check_in_date`, `check_out_date`, `total_room_cost`, `status`) VALUES
(1, 1, '2025-12-01', '2025-12-03', 1000000.00, 'Confirmed'),
(2, 4, '2025-12-05', '2025-12-06', 1500000.00, 'Pending'),
(1, 3, '2025-12-10', '2025-12-11', 850000.00, 'Checked-in');

-- 5. Data Guest_Charges (Menggunakan ID dari Reservations dan Services)
-- Asumsi: Reservasi #1 (Ahmad) pesan 2 Nasi Goreng (ID: 2)
-- Asumsi: Reservasi #3 (Ahmad) pesan 3 Laundry (ID: 1)
INSERT INTO `Guest_Charges` (`reservation_id`, `service_id`, `quantity`, `amount_charged`) VALUES
(1, 2, 2, 150000.00),
(3, 1, 3, 75000.00);

-- Mengaktifkan kembali pemeriksaan foreign key
SET FOREIGN_KEY_CHECKS=1;

-- --- Selesai ---