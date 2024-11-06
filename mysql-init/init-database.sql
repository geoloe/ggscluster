-- Create the database (if it doesn't exist already)
CREATE DATABASE IF NOT EXISTS file_server;

-- Use the created database
USE file_server;

-- Create the roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- Create the users table with a foreign key to roles
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Insert sample roles (admin and viewer)
INSERT INTO roles (name) VALUES 
('admin'), 
('viewer');

-- Insert a sample admin user with hashed password
-- Replace 'password_hash('securePassword123', PASSWORD_DEFAULT)' with the actual hash for better security
INSERT INTO users (username, password, role_id) VALUES 
('admin', '<hash>', 1), -- securePassword123
('viewer', '<hash>', 2); -- secureViewerPassword123