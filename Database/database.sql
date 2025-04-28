-- Create database
CREATE DATABASE IF NOT EXISTS school_attendance;
USE school_attendance;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(50) NOT NULL,
    role ENUM('admin', 'teacher') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    class VARCHAR(20) NOT NULL,
    section VARCHAR(10) NOT NULL,
    qr_code VARCHAR(200) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'leave', 'half_day', 'holi_day') NOT NULL,
    notes TEXT,
    marked_by INT NOT NULL,
    is_auto_marked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (marked_by) REFERENCES users(id)
);

-- Create messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Insert default admin user
INSERT INTO users (username, password, role) VALUES 
('admin', 'admin123', 'admin');
-- Default password: password 

-- Insert sample students data
INSERT INTO students (roll_number, name, father_name, class, section, qr_code) VALUES
('2024001', 'Ali Ahmed', 'Ahmed Khan', '1', 'A', 'STD1A_2024001'),
('2024002', 'Fatima Khan', 'Khan Muhammad', '1', 'A', 'STD1A_2024002'),
('2024003', 'Usman Malik', 'Malik Aslam', '1', 'B', 'STD1B_2024003'),
('2024004', 'Ayesha Riaz', 'Riaz Ahmed', '1', 'B', 'STD1B_2024004'),
('2024005', 'Bilal Hassan', 'Hassan Ali', '2', 'A', 'STD2A_2024005'),
('2024006', 'Sana Javed', 'Javed Iqbal', '2', 'A', 'STD2A_2024006'),
('2024007', 'Hamza Ali', 'Ali Raza', '2', 'B', 'STD2B_2024007'),
('2024008', 'Zainab Shah', 'Shah Jahan', '2', 'B', 'STD2B_2024008'),
('2024009', 'Omar Farooq', 'Farooq Ahmed', '3', 'A', 'STD3A_2024009'),
('2024010', 'Hina Malik', 'Malik Asif', '3', 'A', 'STD3A_2024010'),
('2024011', 'Ahmed Raza', 'Raza Khan', '3', 'B', 'STD3B_2024011'),
('2024012', 'Sara Khan', 'Khan Waqas', '3', 'B', 'STD3B_2024012'),
('2024013', 'Faisal Iqbal', 'Iqbal Hussain', '4', 'A', 'STD4A_2024013'),
('2024014', 'Mehak Ali', 'Ali Hassan', '4', 'A', 'STD4A_2024014'),
('2024015', 'Waqas Ahmed', 'Ahmed Farooq', '4', 'B', 'STD4B_2024015'),
('2024016', 'Amina Hassan', 'Hassan Malik', '4', 'B', 'STD4B_2024016'),
('2024017', 'Zubair Khan', 'Khan Aslam', '5', 'A', 'STD5A_2024017'),
('2024018', 'Nida Malik', 'Malik Riaz', '5', 'A', 'STD5A_2024018'),
('2024019', 'Saad Ali', 'Ali Ahmed', '5', 'B', 'STD5B_2024019'),
('2024020', 'Hira Shah', 'Shah Raza', '5', 'B', 'STD5B_2024020'); 