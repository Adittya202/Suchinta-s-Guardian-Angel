# 👼 Suchinta's Guardian Angel ✨

> A bright, pure sanctuary of warm advice, heartfelt comfort, and gentle reminders crafted with love for Mehzabin Suchinta.

[![Live Website](https://img.shields.io/badge/Live%20Sanctuary-guardianangel.free.je-f59e0b?style=for-the-badge&logo=googlechrome&logoColor=white)](http://guardianangel.free.je/)
[![PHP Version](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/Copyright-2026%20Adittya%20Dey-ec4899?style=for-the-badge)](together.html)

---

## 🌐 Live Website

The sanctuary is publicly hosted and live at:
### 👉 **[[http://guardianangel.free.je/](http://guardianangel.free.je/](http://guardianangel.free.je/))**

---

## 📖 About The Project

**Suchinta's Guardian Angel** is an interactive, uplifting web sanctuary built to bring smiles, warmth, and peace of mind. Whenever Suchinta taps the celestial guardian angel, it delivers gentle reminders to drink water, unclench the jaw, take deep breaths, and celebrate everyday progress.

The sanctuary features a dual-advice architecture:
1. **Normal Advices (70% chance)**: Quick, refreshing bursts of positivity appearing directly inside the fluffy cloud.
2. **Pro Advices (30% chance)**: Substantial, deeply caring paragraphs of wisdom and support accessible via a dedicated celestial modal dialog.

The application also includes an **Owner's Control Dashboard** (`admin.php`), enabling Adittya to whisper new advices into the cloud in real time from any browser.

---

## ✨ Key Features

- **Hand-Crafted Celestial Angel**:
  - Fully bespoke SVG illustration featuring floating feathered wings, golden halo, royal celestial tunic, and a magic golden star wand.
  - Interactive bounce and sparkling particle burst on every tap.

- **Fluffy Cloud Advice Board**:
  - Organic multi-puff cloud interface with speech-bubble tail pointing straight to the angel's halo.
  - Dynamic text transition and non-consecutive randomization (never displays the same quote twice in a row).

- **Dual-Tier Advice System**:
  - **70% Normal Rotation**: Sweet, actionable reminders for hydration, self-care, and relaxation.
  - **30% Pro Advice Rotation**: Full-length heartfelt paragraphs representing the true motto of the sanctuary.

- **Native Web Audio API Chime Synthesizer**:
  - Generates harmonic celestial chimes programmatically in real time using the browser's native Web Audio API (zero audio files needed, zero latency).
  - Includes a mute/unmute toggle.

- **Dynamic Animated Wildlife**:
  - Flying birds floating gracefully across the sky.
  - Playful animated cats and dogs running across the bottom meadow.

- **Radiant Smiling Sun**:
  - Smiling sun with animated rotating sunbeams and glowing rings in the corner.

- **Owner's Control Dashboard (`admin.php`)**:
  - Password-protected management dashboard with 30-day persistent sessions.
  - Separate management tabs for Normal Advices and Pro Advices.
  - Full CRUD capabilities: create and delete advice records directly from MySQL.

- **Full Offline Resilience**:
  - Live asynchronous synchronization with the MySQL database via `api.php`.
  - Built-in curated fallback datasets guarantee the site functions flawlessly even without an active database connection.

- **One-Click Local Launcher (`run.bat`)**:
  - Automatically checks for running XAMPP Apache instances or spins up PHP's built-in development server on port 8000 and opens the browser.

- **Credits & Copyright Sanctuary (`together.html`)**:
  - Responsive, high-fidelity gallery displaying side-by-side portraits with interactive contain/cover zoom views.
  - Legal claim of authorship and modification rights.

---

## 🛠️ Architecture & Tech Stack

| Layer | Technology | Purpose |
| :--- | :--- | :--- |
| **Frontend Structure** | HTML5 | Semantic structure, accessibility (`aria-live`, roles) |
| **Styling & Effects** | Vanilla CSS3 | Custom design system, CSS variables, glassmorphism, keyframe animations |
| **Graphics** | SVG (Scalable Vector Graphics) | Hand-crafted celestial angel, radiant sun, and UI icons |
| **Audio** | Web Audio API | Real-time synthesis of pleasant celestial harmonic frequencies |
| **Client-Side Logic** | Vanilla JavaScript (ES6+) | Dynamic fetch, particle engine, modal logic, event handling |
| **Backend & API** | PHP 8.x | RESTful JSON API (`api.php`), secure session management (`admin.php`) |
| **Database** | MySQL / MariaDB | Persistent storage for normal & pro advice entries (`schema.sql`) |

---

## 📁 Project Directory Structure

```text
Suchinta's Guardian Angel/
├── resources/
│   ├── photo.jpg           # Portrait of Mehzabin Suchinta
│   └── photo2.jpg          # Portrait of Adittya Dey
├── .gitattributes          # Git attributes configuration
├── .gitignore              # Files to ignore in Git
├── admin.php               # Owner's Control Dashboard (Passcode Protected)
├── api.php                 # RESTful JSON API (GET, POST, DELETE)
├── db.php                  # PDO MySQL database connection handler
├── index.html              # Main frontend sanctuary
├── index.php               # PHP web entrypoint (serves index.html)
├── run.bat                 # One-click Windows development launcher
├── schema.sql              # MySQL database schema & initial seed data
├── script.js               # Frontend interactive logic & Web Audio synthesizer
├── style.css               # Main sanctuary stylesheet & animations
├── together.css            # Stylesheet for the Credits & Copyright page
├── together.html           # Credits & Copyright page
└── README.md               # Complete project documentation
```

---

## 🚀 How to Run Locally

### Option 1: One-Click Windows Launcher (Recommended)
If you have **PHP** or **XAMPP** installed:
1. Double-click [`run.bat`](run.bat).
2. The launcher will automatically detect your environment, start the server if needed, and launch the sanctuary in your browser!

---

### Option 2: Standard XAMPP / WAMP Setup

1. **Install XAMPP** (or any AMP stack with PHP and MySQL).
2. Move or clone this project folder into your web directory:
   ```text
   C:\xampp\htdocs\suchintas-guardian-angel\
   ```
3. Start **Apache** and **MySQL** from the XAMPP Control Panel.
4. Open **phpMyAdmin** (`http://localhost/phpmyadmin/`).
5. Click **Import** and select [`schema.sql`](schema.sql) to create the database and seed records.
6. Open your browser and navigate to:
   - **Sanctuary**: `http://localhost/suchintas-guardian-angel/`
   - **Admin Portal**: `http://localhost/suchintas-guardian-angel/admin.php`

---

### Option 3: PHP Built-in Server + MySQL

1. **Import Database**:
   ```bash
   mysql -u root -p < schema.sql
   ```
2. **Start PHP Development Server**:
   ```bash
   php -S localhost:8000
   ```
3. **Visit**:
   - `http://localhost:8000/index.html`

---

## 🌐 Hosting & Deployment Instructions

To deploy to shared hosting (such as **InfinityFree**, **free.je**, **Hostinger**, or **cPanel**):

1. **Upload Files**:
   - Upload all repository files to your public directory (usually `htdocs/` or `public_html/`).
2. **Set Up MySQL Database**:
   - Create a new MySQL Database and User in your hosting control panel.
   - Access **phpMyAdmin** on your host and import [`schema.sql`](schema.sql).
3. **Configure Database Connection**:
   - Open [`db.php`](db.php) and update the credentials to match your hosting details:
     ```php
     $host    = 'sqlXXX.yourhost.com'; // Your hosting DB host
     $dbname  = 'your_database_name';  // Your hosting DB name
     $user    = 'your_db_username';   // Your hosting DB username
     $pass    = 'your_db_password';   // Your hosting DB password
     ```
4. **Access the Site**:
   - Your site will be live immediately (e.g. `http://guardianangel.free.je/`).

---

## 🔒 Owner Dashboard & Security

- **Dashboard Path**: `/admin.php`
- **Default Passcode**: `angelpassword123`
- *To customize the passcode*: Open [`admin.php`](admin.php#L28) and update `$ADMIN_PASSWORD`:
  ```php
  $ADMIN_PASSWORD = 'your_new_secret_password';
  ```

---

## ⚖️ Copyright & Legal Notice

© 2026 **Adittya Dey**. All rights reserved.

> *Only Adittya Dey and Mehzabin Suchinta can modify, change, and are the authors to remove or alter this application.*
