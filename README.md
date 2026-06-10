# ISO_Manager

ISO Certificate Manager built with Core PHP (OOP), PDO, MySQL, and Bootstrap 5.

## Step-by-Step Setup Guide

Follow these instructions to set up the project on your local machine.

### Prerequisites
- PHP 8.0+
- MySQL or MariaDB
- Composer (for dependency management)
- A web server (Apache/Nginx/XAMPP/WAMP)

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone <repository_url>
   cd iso-manager
   ```

2. **Install PHP Dependencies**
   Since PDF and QR Code generation rely on external libraries, you must run Composer to install them:
   ```bash
   composer install
   ```

3. **Database Setup**
   - Create a new MySQL database named `iso_manager` (or your preferred name).
   - Import the provided database structure and sample data:
     ```bash
     mysql -u root -p iso_manager < database.sql
     ```
   - *Note: The default admin credentials in the database are Username: `admin`, Password: `password`.*

4. **Configure Database Connection**
   - Open `config/database.php`.
   - Update the database credentials (`host`, `db_name`, `username`, `password`) if they differ from the defaults:
     ```php
     private $host = "localhost";
     private $db_name = "iso_manager";
     private $username = "root";
     private $password = "";
     ```

5. **Folder Permissions**
   Ensure the following directories have write permissions (CHMOD 755 or 777 depending on your environment) so the application can save generated PDFs and QR codes:
   - `uploads/`
   - `uploads/certificates/`
   - `uploads/qrcodes/`

6. **Run the Application**
   - If you are using a local server like XAMPP/WAMP, place the folder in your `htdocs` or `www` directory and access it via `http://localhost/iso-manager/`.
   - Alternatively, you can use the built-in PHP server for testing:
     ```bash
     php -S localhost:8000
     ```
   - Access the admin panel at `http://localhost:8000/admin/login.php`.

## Troubleshooting

- **Certificate Generation Error (Class not found)**: Make sure you have run `composer install` in the root directory. The `vendor` directory must exist for PDF (Dompdf) and QR Code (chillerlan/php-qrcode) generation to work.
