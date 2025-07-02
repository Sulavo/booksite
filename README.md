# 📚 Free Online Book Reading Website

A simple, fully functional online book reading platform where users can browse, read, and bookmark books chapter by chapter. Admins can manage books, chapters, and users through a basic PHP-powered dashboard.

---

## 🚀 Features

### 👥 User Side:

- Browse books by genre, type, or status
- View book descriptions and chapters
- Read books one chapter at a time
- Bookmark favorite books (requires login)
- Search books by title
- See popular books (daily and all-time)
- Get book recommendation based on your reading history

### 🔐 Admin Side:

- Admin login system
- Add / Edit / Delete books
- Upload chapters (text only)
- Manage chapters
- View or take actions on user accounts

---

## 🧰 Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** Core PHP (no framework)
- **Database:** MySQL
- **Auth:** Custom PHP session-based login

---

## ⚙️ Installation Instructions

### Requirements

- PHP ≥ 8.0
- MySQL
- Web Server (e.g., Apache — included in XAMPP)

### Setup Steps

```bash
1. Download or clone this repo
2. Place it inside your web server's root (e.g., htdocs or www)

3. Import the SQL file:
   - Open phpMyAdmin
   - Create a database (e.g., `book_db`)
   - Import `book_db.sql` from the project

4. Login:

```
