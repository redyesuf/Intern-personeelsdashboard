CREATE DATABASE attendance_system;

USE attendance_system;

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    location ENUM('school', 'company') NOT NULL,
    status ENUM('present', 'absent', 'ziek') DEFAULT 'present',
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    isDeleted BOOLEAN
);


CREATE TABLE attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    date DATE,
    status ENUM('present', 'absent', 'ziek') DEFAULT 'present',
    isDeleted BOOLEAN
);


