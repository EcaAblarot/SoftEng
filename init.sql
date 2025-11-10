-- Creates the enrollment_db database, users table and inserts a sample user

CREATE DATABASE IF NOT EXISTS enrollment_db;
USE enrollment_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  answer1 VARCHAR(255),
  answer2 VARCHAR(255),
  answer3 VARCHAR(255)
);

-- Sample user: username = testuser, password = TestPass123
INSERT INTO users (username, password, answer1, answer2, answer3)
VALUES ('testuser', '$2y$12$vrfzPozofrOtdoMT.6u0FO5/AsZdpRcoqC3MVGeZEwgGgkZJUiiRa', 'blue', 'fluffy', 'manila');

-- Optional students table for the Student Records module
CREATE TABLE IF NOT EXISTS students (
  id VARCHAR(32) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  grade VARCHAR(50),
  section VARCHAR(100),
  status VARCHAR(50) DEFAULT 'Active'
);

INSERT IGNORE INTO students (id,name,grade,section,status) VALUES
('2024-001','Ace Toralba','Grade 1','Faith','Active'),
('2024-002','Cliff Lozada','Grade 2','Hope','Active'),
('2024-003','Antonio Chong','Grade 3','Grace','Active');
