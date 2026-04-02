-- Create Database
CREATE DATABASE IF NOT EXISTS google_drive_clone;
USE google_drive_clone;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    storage_limit BIGINT DEFAULT 16106127360, -- 15GB in bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -- Folders Table
-- CREATE TABLE IF NOT EXISTS folders (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     user_id INT NOT NULL,
--     name VARCHAR(255) NOT NULL,
--     parent_id INT DEFAULT NULL,
--     is_trash TINYINT(1) DEFAULT 0,
--     is_starred TINYINT(1) DEFAULT 0,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES users(id),
--     FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE
-- );

-- -- Files Table
-- CREATE TABLE IF NOT EXISTS files (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     user_id INT NOT NULL,
--     folder_id INT DEFAULT NULL,
--     name VARCHAR(255) NOT NULL,
--     original_name VARCHAR(255) NOT NULL,
--     file_path VARCHAR(255) NOT NULL,
--     file_type VARCHAR(100),
--     file_size BIGINT NOT NULL,
--     is_trash TINYINT(1) DEFAULT 0,
--     is_starred TINYINT(1) DEFAULT 0,
--     uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
--     FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE
-- );

-- Folders Table
CREATE TABLE IF NOT EXISTS folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    parent_id INT NULL,
    is_trash TINYINT(1) DEFAULT 0,
    is_starred TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX (user_id),
    INDEX (parent_id),

    CONSTRAINT fk_folders_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_folders_parent
        FOREIGN KEY (parent_id)
        REFERENCES folders(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Files Table
CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    folder_id INT NULL,
    name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(100),
    file_size BIGINT NOT NULL,
    is_trash TINYINT(1) DEFAULT 0,
    is_starred TINYINT(1) DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX (user_id),
    INDEX (folder_id),

    CONSTRAINT fk_files_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_files_folder
        FOREIGN KEY (folder_id)
        REFERENCES folders(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
