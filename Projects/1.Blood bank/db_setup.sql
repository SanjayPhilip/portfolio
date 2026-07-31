-- E Blood Connect Database Setup
-- This SQL script will create all necessary tables and initial data for the system

-- Drop existing tables if they exist (in reverse order to avoid foreign key constraints)
DROP TABLE IF EXISTS blood_bank_requests;
DROP TABLE IF EXISTS blood_donations;
DROP TABLE IF EXISTS blood_requests;
DROP TABLE IF EXISTS blood_bank;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    dob DATE NOT NULL,
    address TEXT NOT NULL,
    medical_conditions TEXT NULL,
    is_admin BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create blood bank table (inventory)
CREATE TABLE blood_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL UNIQUE,
    units INT NOT NULL DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create blood requests table
CREATE TABLE blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units INT NOT NULL,
    hospital VARCHAR(100) NOT NULL,
    hospital_address TEXT NOT NULL,
    urgency ENUM('critical', 'urgent', 'standard') NOT NULL,
    required_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'in_process', 'completed', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create blood donations table
CREATE TABLE blood_donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    request_id INT NULL, -- NULL if donation is to blood bank
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units INT NOT NULL,
    donation_date DATE NOT NULL,
    donation_type ENUM('direct', 'bloodbank') NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE SET NULL
);

-- Create blood bank requests table
CREATE TABLE blood_bank_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units INT NOT NULL,
    hospital VARCHAR(100) NOT NULL,
    required_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert initial admin
-- Default credentials: admin@ebloodconnect.com / admin123
INSERT INTO admins (full_name, email, password, phone) 
VALUES ('Admin User', 'admin@ebloodconnect.com', '$2y$10$jE6xN0xgHJgJj/vLIRgRouK4NgWpRKfU5jIE9vTEKbQaHr3qyLj1C', '+1234567890');

-- Insert initial blood bank inventory for all blood types
INSERT INTO blood_bank (blood_type, units) VALUES
('A+', 50),
('A-', 25),
('B+', 45),
('B-', 20),
('AB+', 15),
('AB-', 10),
('O+', 60),
('O-', 30);

-- Insert some sample users
INSERT INTO users (full_name, email, password, phone, blood_type, dob, address, medical_conditions) VALUES
('John Doe', 'john@example.com', '$2y$10$jE6xN0xgHJgJj/vLIRgRouK4NgWpRKfU5jIE9vTEKbQaHr3qyLj1C', '9876543210', 'A+', '1985-05-15', '123 Main St, Anytown', 'None'),
('Jane Smith', 'jane@example.com', '$2y$10$jE6xN0xgHJgJj/vLIRgRouK4NgWpRKfU5jIE9vTEKbQaHr3qyLj1C', '8765432109', 'B-', '1990-08-23', '456 Oak Ave, Somewhere', 'Asthma'),
('Michael Johnson', 'michael@example.com', '$2y$10$jE6xN0xgHJgJj/vLIRgRouK4NgWpRKfU5jIE9vTEKbQaHr3qyLj1C', '7654321098', 'O+', '1982-11-07', '789 Pine Rd, Nowhere', 'None'),
('Sarah Williams', 'sarah@example.com', '$2y$10$jE6xN0xgHJgJj/vLIRgRouK4NgWpRKfU5jIE9vTEKbQaHr3qyLj1C', '6543210987', 'AB+', '1995-03-12', '321 Elm St, Anywhere', 'None');

-- Insert sample blood requests
INSERT INTO blood_requests (user_id, patient_name, blood_type, units, hospital, hospital_address, urgency, required_date, reason, status) VALUES
(2, 'Robert Smith', 'A+', 2, 'City Hospital', '123 Hospital Ave, City', 'urgent', DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), 'Surgery scheduled', 'pending'),
(3, 'Emily Johnson', 'O-', 3, 'Community Medical Center', '456 Medical Blvd, Town', 'critical', DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), 'Accident victim needs immediate transfusion', 'pending'),
(4, 'David Wilson', 'B+', 1, 'Regional Hospital', '789 Health St, Region', 'standard', DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY), 'Scheduled procedure', 'pending');

-- Insert sample blood donations
INSERT INTO blood_donations (donor_id, request_id, blood_type, units, donation_date, donation_type, status) VALUES
(2, NULL, 'A+', 1, DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), 'bloodbank', 'pending'),
(3, 1, 'O+', 2, DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), 'direct', 'pending');

-- Insert sample blood bank requests
INSERT INTO blood_bank_requests (user_id, patient_name, blood_type, units, hospital, required_date, reason, status) VALUES
(4, 'Jason Brown', 'AB+', 2, 'City Medical Center', DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), 'Scheduled surgery', 'pending');

-- Create db_setup.php to initialize the database from browser
-- The following SQL string will be added to a CREATE PROCEDURE for later execution
DELIMITER //
CREATE PROCEDURE check_tables_exist()
BEGIN
    DECLARE table_count INT DEFAULT 0;
    
    -- Count how many of our expected tables exist
    SELECT COUNT(*) INTO table_count
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name IN ('users', 'blood_bank', 'blood_requests', 'blood_donations', 'blood_bank_requests');
    
    -- If we don't have all 5 tables, we'll return 0 (not setup)
    IF table_count < 5 THEN
        SELECT 0 AS is_setup;
    ELSE
        SELECT 1 AS is_setup;
    END IF;
END //
DELIMITER ; 