CREATE DATABASE IF NOT EXISTS rideon;

USE rideon;

-- ==========================================
-- 1. USERS TABLE
-- ==========================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(10) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ==========================================
-- 2. BOOKINGS TABLE
-- ==========================================

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pickup VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    ride_date DATE NOT NULL,
    ride_time TIME NOT NULL,
    vehicle VARCHAR(20),
    fare DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_booking_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT check_booking_fare
        CHECK (fare IS NULL OR fare > 0)
);


-- ==========================================
-- 3. VEHICLES TABLE
-- ==========================================

CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_type VARCHAR(20) NOT NULL,
    vehicle_name VARCHAR(100) NOT NULL,
    base_fare DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) DEFAULT 'Available',

    CONSTRAINT check_vehicle_fare
        CHECK (base_fare > 0)
);


-- ==========================================
-- 4. PAYMENTS TABLE
-- ==========================================

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    payment_method VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(30) DEFAULT 'Pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_payment_booking
        FOREIGN KEY (booking_id)
        REFERENCES bookings(id)
        ON DELETE CASCADE,

    CONSTRAINT check_payment_amount
        CHECK (amount > 0)
);


-- ==========================================
-- 5. FEEDBACK TABLE
-- ==========================================

CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    rating INT NOT NULL,
    comment VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_feedback_booking
        FOREIGN KEY (booking_id)
        REFERENCES bookings(id)
        ON DELETE CASCADE,

    CONSTRAINT check_rating
        CHECK (rating >= 1 AND rating <= 5)
);


-- ==========================================
-- 6. DEFAULT VEHICLES
-- ==========================================

INSERT INTO vehicles
(vehicle_type, vehicle_name, base_fare, status)
VALUES
('Bike', 'RideOn Bike', 50, 'Available'),
('Auto', 'RideOn Auto', 90, 'Available'),
('Car', 'RideOn Car', 150, 'Available');