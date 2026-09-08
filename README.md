# 🏋️ FitZone Fitness Center

FitZone Fitness Center is a web-based gym and fitness center management system developed to provide an easy and convenient platform for customers, staff, and administrators. The system allows users to explore fitness services, trainers, membership plans, class schedules, promotions, and blog content while providing dedicated dashboards for managing day-to-day fitness center operations.

## 📌 Project Overview

The FitZone Fitness Center website is designed to improve the interaction between a fitness center and its members. Customers can create accounts, log in, subscribe to membership plans, book fitness classes, view trainers and services, manage their profiles, and submit inquiries.

The system also includes role-based functionality for **Admin**, **Staff**, and **Customer** users. Administrators can manage users, memberships, trainers, schedules, bookings, packages, customer queries, promotions, and blog posts, while staff members can access operational information through their own dashboard.

## ✨ Main Features

### 👤 Customer
- Customer registration and secure login
- Personal customer dashboard
- View and update profile information
- Browse fitness services
- View expert trainers and trainer packages
- View available membership plans
- Subscribe to membership plans
- Check current membership status
- View fitness class schedules
- Book fitness classes
- View offers and promotions
- Read fitness-related blog posts
- Submit inquiries/contact requests
- Forgot and reset password functionality

### 🧑‍💼 Admin
- Secure admin login and dashboard
- Manage registered users
- Manage membership plans and subscriptions
- Approve/manage memberships
- Manage trainers
- Manage trainer packages
- Manage class schedules
- Manage class bookings
- Manage customer queries
- Manage offers and promotions
- Manage blog posts

### 🧑‍🏫 Staff
- Staff login and dashboard
- View operational information
- Access class booking information
- Access customer queries
- Support day-to-day fitness center activities

## 🛠️ Technologies Used

| Technology | Purpose |
|---|---|
| HTML5 | Web page structure |
| CSS3 | Styling and responsive interface |
| JavaScript | Client-side interaction |
| PHP | Server-side application logic |
| MySQL | Database management |
| MySQLi | PHP database connectivity |
| XAMPP | Local Apache and MySQL development environment |
| Git & GitHub | Version control and source-code hosting |

## 🔐 Security Features

- Passwords are stored using PHP's secure `password_hash()` mechanism.
- Login passwords are validated using `password_verify()`.
- Prepared statements are used in important database operations to reduce SQL injection risk.
- PHP sessions are used for authenticated user access.
- Role-based redirection separates Admin, Staff, and Customer functionality.
- Customer registration validates duplicate email addresses and password confirmation.

## 📂 Project Structure

```text
FitZone-Fitness-Center/
│
├── Images/                     # Website images and media
├── index.php                   # Main landing page
├── Aboutus.php                 # About FitZone
├── Services.php                # Fitness services
├── trainers.php                # Trainer information
├── Membership.php              # Membership plans
├── Schedule.php                # Class schedule
├── Blog.php                    # Blog listing
├── Contact.php                 # Contact page
│
├── Login.php                   # Login interface
├── Registration.php            # Customer registration
├── login-handler.php           # Authentication processing
├── register-handler.php        # Registration processing
├── forgot-password.php         # Forgot-password functionality
├── reset-password.php          # Password reset
├── Logout.php                  # Logout
│
├── customer-dashboard.php      # Customer dashboard
├── customer-home.php           # Customer home
├── profile.php                 # Customer profile
├── subscribe.php               # Membership subscription
├── book-class.php              # Class booking
│
├── admin-dashboard.php         # Admin dashboard
├── manage-users.php            # User management
├── manage-membership.php       # Membership management
├── manage-trainers.php         # Trainer management
├── manage-packages.php         # Package management
├── manage-schedule.php         # Schedule management
├── manage-bookings.php         # Booking management
├── manage-queries.php          # Customer query management
├── blog-manager.php            # Blog management
├── update-offers.php           # Promotion/offer management
│
├── staff-dashboard.php         # Staff dashboard
├── staff-home.php              # Staff home
│
└── db.php                      # MySQL database connection
```

## 🗄️ Database

The application uses a MySQL database named:

```text
fitzone
```

The project uses database data for areas such as:

- Users and user roles
- Membership plans and user memberships
- Trainers and trainer packages
- Class schedules and bookings
- Customer queries
- Blog posts
- Offers and promotions
- Password-reset information

The default local database connection configured in `db.php` is:

```php
mysqli_connect("localhost", "root", "", "fitzone");
```

> **Note:** Update the database credentials in `db.php` if your MySQL username, password, host, or database name is different.

## 🚀 Installation and Setup

### Prerequisites

Install the following before running the project:

- XAMPP or another PHP/MySQL server environment
- PHP
- MySQL
- A modern web browser
- Git (optional, for cloning the repository)

### 1. Clone the repository

```bash
git clone https://github.com/Anju-Kaushi/Fitzone-Fitness-Center.git
```

Or download the repository as a ZIP file and extract it.

### 2. Move the project to XAMPP

Copy the project folder into:

```text
C:\xampp\htdocs\
```

For example:

```text
C:\xampp\htdocs\FitZone-Fitness-Center\
```

### 3. Start XAMPP

Open the XAMPP Control Panel and start:

- **Apache**
- **MySQL**

### 4. Create the database

Open phpMyAdmin in your browser and create a database named:

```text
fitzone
```

Import your FitZone database SQL backup if you have one. The required tables must exist before database-driven features can operate correctly.

### 5. Configure the database connection

Check `db.php` and make sure the database configuration matches your environment:

```php
$conn = mysqli_connect("localhost", "root", "", "fitzone");
```

### 6. Run the application

Open the project through your local Apache server, for example:

```text
http://localhost/FitZone-Fitness-Center/
```

## 👥 User Roles

| Role | Main Access |
|---|---|
| Customer | Memberships, classes, profile, trainers, services and bookings |
| Staff | Operational dashboard, bookings and customer queries |
| Admin | Full management dashboard and administrative functions |

## 🔄 Basic System Flow

```text
Visitor
   │
   ├── Browse Website
   │     ├── Services
   │     ├── Trainers
   │     ├── Memberships
   │     ├── Class Schedule
   │     └── Blog
   │
   ├── Register / Login
   │
   └── Role-Based Access
         ├── Customer → Customer Dashboard
         ├── Staff    → Staff Dashboard
         └── Admin    → Admin Dashboard
```

## 🎯 Project Objectives

- Provide an accessible online platform for FitZone Fitness Center.
- Simplify customer registration and membership subscription.
- Allow customers to discover services, trainers, packages, and class schedules.
- Provide convenient online class booking functionality.
- Centralize customer queries and fitness-center information.
- Provide role-based dashboards for customers, staff, and administrators.
- Reduce manual administrative work through digital management features.

## 📸 Screenshots

Screenshots can be added to this section to showcase the system.

Suggested screenshots:

1. Home Page
2. Login & Registration
3. Customer Dashboard
4. Membership Plans
5. Class Schedule
6. Trainer Page
7. Admin Dashboard
8. User Management
9. Membership Management
10. Booking Management

Example Markdown:

```markdown
![FitZone Home Page](screenshots/home.png)
```

## 🔮 Future Improvements

- Online membership payment gateway
- Email/SMS booking and membership notifications
- Attendance tracking
- Trainer appointment scheduling
- Fitness progress tracking
- Workout and nutrition plans
- Advanced admin reports and analytics
- Mobile-friendly Progressive Web App (PWA)

## 👩‍💻 Author

**Hewadulige Anjali Kaushalya**

GitHub: [Anju-Kaushi](https://github.com/Anju-Kaushi)

## 📄 License

This project was developed for educational purposes. If you plan to reuse or distribute the project, add an appropriate license to the repository.

---

⭐ If you find this project useful, consider giving the repository a star!
