# Google Drive Clone

A modern web-based file management application inspired by Google Drive, built with HTML, CSS, PHP, and MySQL.

## Features
- **User Authentication**: Secure signup and login.
- **File Management**: Upload documents, images, and videos.
- **Folder Organization**: Create and navigate nested folders.
- **File Operations**: Rename, Delete, and Download files and folders.
- **Modern UI**: Google Drive-inspired design with grid view and sidebar.
- **Storage Tracking**: Real-time storage usage indicator.

## Folder Structure
- `/actions`: PHP scripts for file/folder actions (delete, download, rename).
- `/assets/css`: Custom CSS for authentication and dashboard.
- `/assets/js`: Frontend logic for interactions and context menus.
- `/includes`: Core configuration, authentication, and helper functions.
- `/uploads`: User-specific storage directories (automatically created).
- `index.php`: Main dashboard.
- `login.php`/`register.php`: Authentication pages.
- `schema.sql`: Database structure.

## Setup Instructions (XAMPP / WAMP)

### 1. Database Setup
1. Open **phpMyAdmin**.
2. Create a new database named `google_drive_clone`.
3. Import the `schema.sql` file located in the project root.

### 2. Project Configuration
1. Move the `google-drive-clone` folder to your server's root (e.g., `C:\xampp\htdocs\`).
2. Open `includes/config.php` and update the database credentials if necessary:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'google_drive_clone');
   ```

### 3. Run the Application
1. Start **Apache** and **MySQL** in your XAMPP/WAMP control panel.
2. Open your browser and navigate to `http://localhost/google-drive-clone/register.php`.
3. Create an account and start managing your files!

## Note on File Sizes
By default, PHP might limit file uploads (often 2MB). To upload larger files, increase `upload_max_filesize` and `post_max_size` in your `php.ini` file.
