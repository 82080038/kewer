# Kewer Development Environment Setup Guide

Complete setup requirements for developing Kewer application on Windsurf IDE.

## Prerequisites

- Ubuntu Linux (or similar)
- Sudo access
- Internet connection

## Required Software

### 1. XAMPP
Download and install XAMPP from https://www.apachefriends.org/
- Apache web server
- MySQL/MariaDB database
- PHP 8.0+

### 2. Development Tools

#### Node.js and npm
```bash
sudo apt update
sudo apt install nodejs npm
```

Verify installation:
```bash
node --version  # Should be v18+
npm --version
```

#### PHP Composer
```bash
sudo apt install composer
```

Verify installation:
```bash
composer --version
```

#### MySQL Client
```bash
sudo apt install mysql-client-core-8.0
```

### 3. PHP Extensions

Required PHP extensions for the application:
```bash
sudo apt install php8.3-mysql php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-zip
```

Verify extensions:
```bash
php -m | grep -E "mysqli|gd|mbstring|curl|xml|zip"
```

## Project Setup

### 1. Clone Repository
```bash
git clone https://github.com/82080038/kewer.git
cd kewer
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Install Playwright (E2E Testing)
```bash
cd tests/e2e
npm install
```

## Database Setup

### 1. Start XAMPP
```bash
sudo /opt/lampp/lampp start
```

### 2. Create Database
```bash
# Using phpMyAdmin or MySQL command line
mysql -u root -p
CREATE DATABASE kewer;
EXIT;
```

### 3. Import Database Schema
```bash
mysql -u root -p kewer < kewer_database_export.sql
```

### 4. Configure Database Connection
Edit `.env` file:
```env
DB_HOST=localhost
DB_NAME=kewer
DB_USER=root
DB_PASS=your_mysql_password
```

## Verify Installation

### Check Database Connection
```bash
mysql -u root -p kewer -e "SHOW TABLES;"
```

### Check PHP Extensions
```bash
php -m
```

### Test Application
1. Open browser: `http://localhost/kewer/login.php`
2. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`

## Development Tools

### Windsurf IDE
- Install Windsurf from https://windsurf.ai/
- Open project folder in Windsurf

### Git
```bash
sudo apt install git
```

### Browser Testing
- Chrome/Chromium (for Playwright)
- Firefox (for cross-browser testing)

## Troubleshooting

### MySQL Connection Issues
If MySQL client can't connect:
```bash
# Use XAMPP's MySQL
/opt/lampp/bin/mysql -u root -p
```

### PHP Extension Issues
If Composer complains about missing extensions:
```bash
# Install specific extension
sudo apt install php8.3-[extension-name]
```

### Playwright Issues
If Playwright browsers not found:
```bash
cd tests/e2e
npx playwright install
```

## Default Credentials

| Role | Username | Password |
|------|----------|----------|
| Superadmin | admin | admin123 |
| Owner | owner | password |
| Manager | manager1 | password |
| Petugas | petugas1 | password |
| Karyawan | karyawan1 | password |

## Development Workflow

1. Start XAMPP: `sudo /opt/lampp/lampp start`
2. Open Windsurf IDE
3. Make code changes
4. Test in browser: `http://localhost/kewer`
5. Run tests: `cd tests/e2e && npm test`
6. Commit changes: `git commit -am "description"`

## Additional Resources

- README.md: Project overview and features
- docs/role_definitions.json: Role and permission structure
- tests/: Testing suite
- api/: API documentation

## System Requirements

- RAM: Minimum 4GB (8GB recommended)
- Disk Space: Minimum 2GB free
- CPU: Dual-core or better
- OS: Ubuntu 20.04+ or similar

## Support

For issues:
1. Check XAMPP status: `sudo /opt/lampp/lampp status`
2. Check MySQL logs: `/opt/lampp/var/mysql/*.err`
3. Check Apache logs: `/opt/lampp/logs/error_log`
