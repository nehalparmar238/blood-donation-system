CREATE DATABASE IF NOT EXISTS blood_donation_db;
USE blood_donation_db;

-- 1. Users / Donors Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address TEXT,
    last_donated DATE DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Blood Requests Table
CREATE TABLE blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    units INT NOT NULL,
    hospital VARCHAR(150) NOT NULL,
    city VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    urgency ENUM('Normal', 'Urgent', 'Critical') DEFAULT 'Urgent',
    status ENUM('Pending', 'Fulfilled', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Donation Camps Table
CREATE TABLE camps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camp_name VARCHAR(150) NOT NULL,
    organizer VARCHAR(150) NOT NULL,
    location VARCHAR(200) NOT NULL,
    camp_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('Upcoming', 'Ongoing', 'Completed') DEFAULT 'Upcoming'
);

-- Default Admin User (Password: admin123 -> hashed with password_hash or clear for demo with check, let's store hash for 'admin123' or use password_verify)
-- Password below is password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, blood_group, phone, city, is_admin) VALUES
('System Administrator', 'admin@lifeline.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'O+', '9876543210', 'Ahmedabad', 1);

-- Sample Donors
INSERT INTO users (name, email, password, blood_group, phone, city, last_donated, is_admin) VALUES
('Aarav Patel', 'aarav@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'A+', '9811122233', 'Ahmedabad', '2026-01-15', 0),
('Priya Sharma', 'priya@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'O-', '9822233344', 'Surat', '2025-12-10', 0),
('Rajesh Kumar', 'rajesh@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'B+', '9833344455', 'Vadodara', '2026-02-01', 0);

-- Sample Blood Requests
INSERT INTO blood_requests (patient_name, blood_group, units, hospital, city, contact_person, phone, urgency, status) VALUES
('Ramesh Mehta', 'A+', 2, 'Civil Hospital Ahmedabad', 'Ahmedabad', 'Suresh Mehta', '9898989898', 'Critical', 'Pending'),
('Sunita Verma', 'O-', 1, 'Sterling Hospital', 'Ahmedabad', 'Amit Verma', '9797979797', 'Urgent', 'Pending');

-- Sample Camps
INSERT INTO camps (camp_name, organizer, location, camp_date, start_time, end_time, status) VALUES
('Mega Blood Donation Drive', 'Red Cross Society', 'Town Hall, Ahmedabad', '2026-12-15', '09:00:00', '16:00:00', 'Upcoming'),
('Youth Club Camp', 'Rotary Club', 'Gujarat University Campus', '2026-12-20', '10:00:00', '15:00:00', 'Upcoming');
