# DIF Attendance System

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Status](https://img.shields.io/badge/status-active-brightgreen.svg)

A modern, web-based school attendance management system for schools and institutions. Built with PHP and MySQL, this system provides robust features for admins, teachers, and students, including attendance tracking, reporting, user management, and a secure maintenance mode.

---

## 📸 Screenshots

> **Add your screenshots in `assets/images/screenshots/` and link them here.**

| Login Page | Dashboard | Maintenance Popup |
|------------|-----------|------------------|
| ![Login](assets/images/screenshots/login.png) | ![Dashboard](assets/images/screenshots/dashboard.png) | ![Maintenance](assets/images/screenshots/maintenance.png) |

---

## 🚀 Features

- **User Roles:**
  - **Admin:** Full control (manage users, students, attendance, reports, maintenance mode)
  - **Teacher:** Mark attendance, view reports
  - **Student:** (Optional) View own attendance (if enabled)

- **Attendance Management:**
  - Mark attendance (manual, by roll number, or quick scan)
  - Bulk attendance update
  - Auto attendance (if supported)
  - Attendance status: Present, Absent, Leave, Half Day, Holiday

- **Student Management:**
  - Add, edit, delete students
  - Generate and print student ID cards
  - Search students

- **User Management:**
  - Add, edit, delete users (admin/teacher)
  - Prevent deletion of last admin

- **Reports & Export:**
  - Monthly, class-wise, and section-wise attendance reports
  - Export attendance data as CSV

- **Messaging:**
  - Admin can send messages to users
  - Message history and unread message notifications

- **Maintenance Mode:**
  - Enable/disable maintenance mode from admin dashboard
  - When ON: Only admin can login, others see a maintenance popup
  - Customizable popup UI matching the login theme

- **Security:**
  - Session-based authentication
  - Verification math problem on login

- **Modern UI:**
  - Responsive, clean design
  - Custom branding (logo, colors)

---

## 🏷️ Versioning & Tags

- **Current Version:** `1.0.0`
- **Release Tags:**  
  - `v1.0.0` - Initial public release  
  - *(Add more tags as you release new versions)*

---

## 📝 Changelog

- **v1.0.0**
  - Initial release with all core features (attendance, users, students, reports, maintenance mode, messaging, etc.)

---

## 🛠️ Installation & Setup

1. **Clone or Download the Project**

2. **Database Setup:**
   - Import `Database/database.sql` or `Database/difs_students_database.sql` into your MySQL server.
   - Update `config/database.php` with your DB credentials.

3. **Configure Assets:**
   - Place your logo in `assets/images/dif_logo.png` (or update the path in `index.php`)
   - CSS is in `assets/css/admin.css`

4. **Set Permissions:**
   - Ensure the web server can write to `config/maintenance_mode.php` for maintenance toggle.

5. **Run the App:**
   - Open `index.php` in your browser.
   - Default admin credentials are set in the database (change after first login).

---

## 📋 Usage Guide

- **Login:** Go to `index.php` and login as admin or teacher.
- **Dashboard:** View stats, quick actions, and recent activity.
- **Attendance:** Mark attendance via different methods.
- **Students:** Manage student records and ID cards.
- **Users:** Manage admin/teacher accounts.
- **Reports:** Generate and export attendance reports.
- **Maintenance Mode:**
  - Admin can enable/disable from dashboard.
  - When ON, only admin can login; others see a themed popup.

---

## 🗂️ Project Structure

```
├── index.php                # Login page
├── dashboard.php            # Main dashboard
├── students.php             # Student management
├── users.php                # User management
├── attendance.php           # Attendance marking
├── reports.php              # Attendance reports
├── config/
│   ├── database.php         # DB connection
│   └── maintenance_mode.php # Maintenance mode toggle
├── Database/
│   └── *.sql                # Database schema
├── assets/
│   ├── images/              # Logos, images, screenshots
│   └── css/admin.css        # Main stylesheet
├── auth/
│   ├── login.php            # Login logic
│   └── logout.php           # Logout logic
├── components/
│   └── navbar.php           # Reusable navbar
└── ...                      # Other modules (messaging, export, etc.)
```

---

## 💻 Technologies Used

- **Backend:** PHP 7+
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, Bootstrap 5, FontAwesome
- **Other:** JavaScript, jQuery

---

## ❓ FAQ

**Q: Default admin credentials kya hain?**  
A: Database mein set hain, pehli login ke baad change karen.

**Q: Maintenance mode kaise enable/disable hota hai?**  
A: Admin dashboard se toggle button se.

**Q: Attendance data export kaise karen?**  
A: Reports section mein export option hai (CSV).

---

## 🛠️ Troubleshooting

- PHP version 7+ required
- File permissions (especially `config/maintenance_mode.php`)
- Database connection issues: check `config/database.php`
- Browser cache clear karen agar UI update na ho

---

## 🤝 Contribution

Pull requests welcome!  
- Fork this repo
- Create a feature branch
- Commit your changes
- Open a pull request

---

## 📄 License

This project is licensed under the MIT License.

---

## 👨‍💻 Credits & Support

- **Developed by:** AR Developers
- **Branding:** DIF Sec School
- **Contact:** [Your contact info or support email]

---

Enjoy using the **DIF Attendance System**! 🎉
