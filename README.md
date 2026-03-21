# 📋 Complaint Management System – by Papa

A modern, full-stack Online Complaint Management System built with **PHP**, **MySQL**, and a sleek dark-first frontend.

---

## ✨ Features

- 🌙 **Dark Mode by default** with smooth light mode toggle
- 🌌 **Animated glassmorphism UI** with orb background parallax
- 📝 **User registration & login** with bcrypt password hashing
- 🚀 **Submit complaints** with title, description, category, location, and 1–2 images
- ⚡ **Auto-assignment** of complaints to staff/admin based on category
- 📊 **Dashboard** with live search, filter by status, expandable complaint cards
- 🔒 **Session-based auth** — users can only see their own complaints
- 📱 **Fully responsive** — mobile + desktop

---

## 🛠️ Tech Stack

| Layer    | Technology                     |
|----------|-------------------------------|
| Backend  | PHP 8.x (XAMPP compatible)    |
| Database | MySQL 5.7+ / MariaDB 10.3+    |
| Frontend | HTML5, CSS3, Vanilla JS       |
| Fonts    | Syne + Outfit (Google Fonts)  |

---

## 📁 Folder Structure

```
complaint-system/
├── assets/
│   ├── css/style.css          ← All styles
│   └── js/
│       ├── theme.js           ← Dark/light toggle
│       ├── animation.js       ← Orbs, sidebar, card expand, search
│       └── validation.js      ← Forms, image preview, toast, spinner
├── components/
│   ├── head.php               ← HTML <head> reusable partial
│   ├── bg.php                 ← Animated background + theme toggle
│   └── sidebar.php            ← Dashboard sidebar
├── includes/
│   └── db.php                 ← PDO database connection
├── uploads/                   ← Complaint images (auto-created)
├── database/
│   └── complaint_system.sql   ← Full MySQL schema + seed data
├── index.php                  ← Landing page
├── login.php                  ← Login
├── register.php               ← Registration
├── dashboard.php              ← User dashboard
├── submit.php                 ← Submit new complaint
└── logout.php                 ← Session destroy
```

---

## 🚀 Setup Instructions (XAMPP)

### 1. Clone / Download
```bash
git clone https://github.com/parth-sahuji/Complaint-Management-System-.git
```
Place the folder inside `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux).

### 2. Import the Database
1. Start XAMPP — ensure **Apache** and **MySQL** are running
2. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
3. Click **Import** tab
4. Select `database/complaint_system.sql`
5. Click **Go**

### 3. Configure DB Connection
Open `includes/db.php` and confirm settings:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'complaint_system');
define('DB_USER', 'root');
define('DB_PASS', '');          // Leave empty for default XAMPP
```

### 4. Run
Visit: `http://localhost/Complaint-Management-System-/`

---

## 🗄️ Database Schema

| Table              | Purpose                            |
|--------------------|------------------------------------|
| `roles`            | Admin, Staff, User                 |
| `users`            | All accounts                       |
| `categories`       | 11 predefined complaint categories |
| `complaints`       | Core complaints table              |
| `complaint_images` | 1–2 image paths per complaint      |

---

## 🎨 UI Pages

| Page            | File            | Description                    |
|-----------------|-----------------|-------------------------------|
| Landing         | `index.php`     | Hero with animated background  |
| Login           | `login.php`     | Glassmorphism auth card        |
| Register        | `register.php`  | Registration form              |
| Dashboard       | `dashboard.php` | Complaint cards, search, stats |
| Submit          | `submit.php`    | Multi-field form + image upload|

---

## 🔐 Default Roles

| Role  | ID | Can Submit | Can Mark Complete |
|-------|----|------------|-------------------|
| Admin | 1  | ✅         | ✅                |
| Staff | 2  | ✅         | ✅                |
| User  | 3  | ✅         | ❌                |

---

## 📌 Notes

- Images are stored in the `uploads/` folder (max 5MB each, JPG/PNG/WEBP)
- No complaints can be deleted or edited after submission
- Status is either **Submitted** (yellow) or **Completed** (green)
- No chat, no priority system, single-organization

---

## 📄 License

MIT — Free to use for personal and commercial projects.
