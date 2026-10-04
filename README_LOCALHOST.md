# How to Host SEVAM Locally

This project is built with **PHP (8.0+)** and **MySQL / MariaDB**. You can run it on your local machine using any of the 3 simple methods below.

---

## 🚀 Option 1: Docker (Recommended — 1 Single Command)

If you have [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed on Windows, Mac, or Linux:

1. Open a terminal / command prompt in this project folder.
2. Run:
   ```bash
   docker compose up -d
   ```
3. Open your browser and navigate to:
   👉 **`http://localhost:8000`**

*The database (`sevam`) is automatically created and populated with demo data on the first run.*

To stop the containers:
```bash
docker compose down
```

---

## 🐘 Option 2: XAMPP / WampServer (Windows & Mac)

If you use **XAMPP**:

1. **Move Files**:
   - Copy or extract the entire project folder into your XAMPP web root:
     - **Windows**: `C:\xampp\htdocs\sevam`
     - **macOS**: `/Applications/XAMPP/xamppfiles/htdocs/sevam`
2. **Start Services**:
   - Open the **XAMPP Control Panel**.
   - Start **Apache** and **MySQL**.
3. **Import Database**:
   - Open your browser to `http://localhost/phpmyadmin`.
   - Click **New** on the left menu and create a database named **`sevam`** with collation `utf8mb4_general_ci`.
   - Click the newly created `sevam` database, go to the **Import** tab, choose the file **`database/sevam.sql`**, and click **Import** (or **Go**).
4. **Access the Website**:
   👉 **`http://localhost/sevam`**

*(The included `.htaccess` file ensures clean URLs and extensionless routing work automatically on Apache).*

---

## 💻 Option 3: Quick Launch Scripts or Native PHP CLI

### On Windows:
Double-click **`start-local.bat`**. It will automatically detect Docker or launch the PHP built-in server on `http://localhost:8000`.

### On macOS / Linux:
Run:
```bash
chmod +x start-local.sh
./start-local.sh
```

### Manual Command Line:
1. Ensure MySQL is running and import the database:
   ```bash
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS sevam;"
   mysql -u root -p sevam < database/sevam.sql
   ```
2. Start the built-in server:
   ```bash
   php -S 0.0.0.0:8000 router.php
   ```
3. Visit **`http://localhost:8000`**.

---

## 🔑 Default Accounts & Credentials

| Role | Username / Identity | Password | Description |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` | Full access to moderation, users, categories, analytics |
| **Food Provider** | `annapurna_kitchen` | `provider123` | Post surplus food listings, manage requests |
| **NGO Group** | `hope_foundation` | `group123` | Request food donations, track status |

---

## ⚙️ Environment Variables (Optional)

If your local MySQL uses custom credentials, you can pass them via environment variables:

| Variable | Default Value | Description |
| :--- | :--- | :--- |
| `DB_HOST` | `127.0.0.1` | MySQL server host |
| `DB_PORT` | `3306` | MySQL port |
| `DB_NAME` | `sevam` | Database name |
| `DB_USER` | `root` | Database username |
| `DB_PASS` | ` ` (empty) | Database password |
