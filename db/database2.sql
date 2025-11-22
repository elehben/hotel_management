-- --------------------------------------------------------
-- Host:                         192.168.56.10
-- Server version:               10.1.38-MariaDB - Source distribution
-- Server OS:                    Linux
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for hotel_management
CREATE DATABASE IF NOT EXISTS `hotel_management` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `hotel_management`;

-- Dumping structure for table hotel_management.Guests
CREATE TABLE IF NOT EXISTS `Guests` (
  `guest_id` char(16) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`guest_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone_number` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table hotel_management.Guests: ~5 rows (approximately)
INSERT INTO `Guests` (`guest_id`, `full_name`, `email`, `phone_number`, `created_at`) VALUES
	('3201019380189009', 'Budi Darmawan', 'budi@example.com', '085384983801', '2025-11-22 05:07:33'),
	('3201112223330001', 'Ahmad Subagja', 'ahmad@example.com', '081234567890', '2025-11-22 03:03:57'),
	('3201128643200004', 'Anton Setiawan', 'anton@example.com', '081387481974', '2025-11-22 03:03:57'),
	('3201445566770002', 'Bunga Citra', 'bunga@example.com', '081298765432', '2025-11-22 03:03:57'),
	('3201778899000003', 'Charles Darmawan', 'charles@example.com', '081511223344', '2025-11-22 03:03:57');

-- Dumping structure for table hotel_management.Guest_Bills
CREATE TABLE IF NOT EXISTS `Guest_Bills` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` char(3) NOT NULL,
  `room_id` char(3) NOT NULL,
  `total_payment` decimal(15,2) DEFAULT '0.00' COMMENT 'Total Room Cost + Total Services',
  `payment_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`bill_id`),
  UNIQUE KEY `reservation_id` (`reservation_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `Guest_Bills_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `Reservations` (`reservation_id`) ON DELETE CASCADE,
  CONSTRAINT `Guest_Bills_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `Rooms` (`room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table hotel_management.Guest_Bills: ~4 rows (approximately)
INSERT INTO `Guest_Bills` (`bill_id`, `reservation_id`, `room_id`, `total_payment`, `payment_status`, `updated_at`) VALUES
	(1, '001', '201', 3075000.00, 'Unpaid', '2025-11-22 03:40:50'),
	(2, '002', '301', 3250000.00, 'Unpaid', '2025-11-22 03:03:57'),
	(3, '003', '301', 3150000.00, 'Paid', '2025-11-22 05:35:23'),
	(5, '004', '101', 1800000.00, 'Paid', '2025-11-22 05:36:56');

-- Dumping structure for table hotel_management.Guest_Charges
CREATE TABLE IF NOT EXISTS `Guest_Charges` (
  `charge_id` char(3) NOT NULL,
  `reservation_id` char(3) DEFAULT NULL,
  `service_id` char(3) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `charge_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `amount_charged` decimal(10,2) NOT NULL COMMENT 'Hasil dari (Services.price * quantity)',
  PRIMARY KEY (`charge_id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `Guest_Charges_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `Reservations` (`reservation_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Guest_Charges_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `Services` (`service_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table hotel_management.Guest_Charges: ~6 rows (approximately)
INSERT INTO `Guest_Charges` (`charge_id`, `reservation_id`, `service_id`, `quantity`, `charge_timestamp`, `amount_charged`) VALUES
	('003', '002', '003', 1, '2025-11-22 03:03:57', 250000.00),
	('004', '003', '004', 1, '2025-11-22 03:03:57', 150000.00),
	('005', '001', '001', 1, '2025-11-22 03:40:50', 25000.00),
	('006', '001', '002', 1, '2025-11-22 03:40:50', 75000.00),
	('007', '004', '004', 1, '2025-11-22 05:06:01', 150000.00),
	('008', '004', '003', 1, '2025-11-22 05:06:01', 250000.00);

-- Dumping structure for table hotel_management.Reservations
CREATE TABLE IF NOT EXISTS `Reservations` (
  `reservation_id` char(3) NOT NULL,
  `guest_id` char(16) DEFAULT NULL,
  `room_id` char(3) DEFAULT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `total_room_cost` decimal(12,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending' COMMENT 'Contoh: Pending, Confirmed, Checked-in, Cancelled',
  PRIMARY KEY (`reservation_id`),
  KEY `guest_id` (`guest_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `Reservations_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `Guests` (`guest_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Reservations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `Rooms` (`room_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table hotel_management.Reservations: ~4 rows (approximately)
INSERT INTO `Reservations` (`reservation_id`, `guest_id`, `room_id`, `check_in_date`, `check_out_date`, `total_room_cost`, `status`) VALUES
	('001', '3201112223330001', '301', '2025-12-01', '2025-12-02', 1500000.00, 'Cancelled'),
	('002', '3201445566770002', '301', '2025-12-05', '2025-12-07', 3000000.00, 'Pending'),
	('003', '3201778899000003', '301', '2025-12-10', '2025-12-12', 3000000.00, 'Checked-in'),
	('004', '3201128643200004', '101', '2025-11-22', '2025-11-24', 1000000.00, 'Confirmed');

-- Dumping structure for table hotel_management.Rooms
CREATE TABLE IF NOT EXISTS `Rooms` (
  `room_id` char(3) NOT NULL,
  `room_type_name` varchar(50) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `max_occupancy` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Available' COMMENT 'Contoh: Available, Cleaning, Maintenance',
  PRIMARY KEY (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table hotel_management.Rooms: ~5 rows (approximately)
INSERT INTO `Rooms` (`room_id`, `room_type_name`, `base_price`, `max_occupancy`, `status`) VALUES
	('101', 'Standard', 500000.00, 2, 'Available'),
	('102', 'Medium', 1000000.00, 2, 'Available'),
	('201', 'Deluxe', 850000.00, 2, 'Available'),
	('202', 'Premium', 120000.00, 2, 'Available'),
	('301', 'Suite', 1500000.00, 4, 'Available');

-- Dumping structure for table hotel_management.Services
CREATE TABLE IF NOT EXISTS `Services` (
  `service_id` char(3) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'General' COMMENT 'Contoh: F&B, Laundry, Spa',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table hotel_management.Services: ~4 rows (approximately)
INSERT INTO `Services` (`service_id`, `service_name`, `description`, `price`, `category`, `is_available`) VALUES
	('001', 'Laundry Kemeja', 'Cuci setrika 1 kemeja', 25000.00, 'Laundry', 1),
	('002', 'Nasi Goreng Spesial', 'Room service nasi goreng dengan ayam dan telur', 50000.00, 'F&B', 1),
	('003', 'Spa Pijat Tradisional', 'Pijat 60 menit', 250000.00, 'Spa', 1),
	('004', 'Antar Jemput Bandara', 'Satu kali perjalanan ke/dari bandara', 150000.00, 'Transport', 1);

-- Dumping structure for table hotel_management.Users
CREATE TABLE IF NOT EXISTS `Users` (
  `user_id` char(3) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` char(40) NOT NULL,
  `role` enum('Admin','Receptionist') DEFAULT 'Receptionist',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table hotel_management.Users: ~2 rows (approximately)
INSERT INTO `Users` (`user_id`, `username`, `password`, `role`, `created_at`) VALUES
	('001', 'admin', SHA('123'), 'Admin', '2025-11-22 03:03:57'),
	('002', 'resepsionis1', SHA('111'), 'Receptionist', '2025-11-22 03:03:57');

-- Dumping structure for trigger hotel_management.trg_add_service_to_bill
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
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
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger hotel_management.trg_calc_amount_charged
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

-- Dumping structure for trigger hotel_management.trg_calc_total_room_cost
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

-- Dumping structure for trigger hotel_management.trg_create_bill_after_reservation
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_create_bill_after_reservation
AFTER INSERT ON Reservations
FOR EACH ROW
BEGIN
    INSERT INTO Guest_Bills (reservation_id, room_id, total_payment)
    VALUES (NEW.reservation_id, NEW.room_id, NEW.total_room_cost);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger hotel_management.trg_edit_service_on_bill
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
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
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger hotel_management.trg_update_amount_charged
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

-- Dumping structure for trigger hotel_management.trg_update_bill_on_res_change
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
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
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger hotel_management.trg_update_total_room_cost
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

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
