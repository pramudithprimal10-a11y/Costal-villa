CREATE DATABASE IF NOT EXISTS coastal_villa
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE coastal_villa;

CREATE TABLE IF NOT EXISTS users (
  user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  contact_number VARCHAR(25) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('guest', 'admin') NOT NULL DEFAULT 'guest',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rooms (
  room_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  room_name VARCHAR(100) NOT NULL,
  room_type ENUM('suite', 'family', 'garden') NOT NULL,
  short_description VARCHAR(220) NOT NULL,
  description TEXT NOT NULL,
  price_per_night DECIMAL(10,2) NOT NULL,
  max_guests TINYINT UNSIGNED NOT NULL DEFAULT 2,
  bed_summary VARCHAR(120) NOT NULL DEFAULT 'Queen bed',
  room_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (room_id),
  UNIQUE KEY uq_rooms_name (room_name),
  KEY idx_rooms_status_capacity (room_status, max_guests)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
  booking_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  room_id INT UNSIGNED NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  guest_count TINYINT UNSIGNED NOT NULL DEFAULT 1,
  total_amount DECIMAL(10,2) NOT NULL,
  booking_status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
  booking_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (booking_id),
  KEY idx_bookings_availability (room_id, booking_status, check_in, check_out),
  KEY idx_bookings_guest_date (user_id, booking_date),
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE RESTRICT,
  CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms (room_id) ON DELETE RESTRICT,
  CONSTRAINT chk_booking_dates CHECK (check_out > check_in),
  CONSTRAINT chk_booking_guests CHECK (guest_count > 0),
  CONSTRAINT chk_booking_amount CHECK (total_amount >= 0)
) ENGINE=InnoDB;

INSERT INTO rooms
  (room_name, room_type, short_description, description, price_per_night, max_guests, bed_summary, room_status, featured)
VALUES
  ('The Ocean Suite', 'suite', 'A breezy king room with a quiet ocean-side balcony.', 'Wake to the sound of the coast in a generous suite with soft natural textures, a private balcony, a king bed, and an ensuite made for slow mornings.', 24500.00, 2, 'King bed', 'active', 1),
  ('Palm Garden Room', 'garden', 'A calm garden hideaway framed by palms and green.', 'Open the doors to a leafy private patio, settle into a queen bed, and let the garden shade set the pace. A gentle base for couples and solo travellers.', 18500.00, 2, 'Queen bed', 'active', 0),
  ('The Family Cabana', 'family', 'Room to gather, with a second bed and leafy outlook.', 'A relaxed split sleeping space with a king bed and two singles, built for families or friends who want room to unwind after a day along the south coast.', 32000.00, 4, 'King bed + two singles', 'active', 1)
ON DUPLICATE KEY UPDATE room_name = VALUES(room_name);
