# StudentHub – Student Portal

## 1. Project Overview

StudentHub is a web-based student portal developed using HTML, CSS, JavaScript, PHP, and MySQL. It provides a centralized platform for students and administrators to access portal features through role-based login and authentication.

The project runs locally using XAMPP, Apache, and MySQL.

## 2. Objectives

- Develop a user-friendly student portal.
- Implement user authentication and session management.
- Provide separate access for students and administrators.
- Connect the website to a MySQL database.
- Organize student-related information and portal features in one place.
- Provide a responsive interface for different screen sizes.

## 3. Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Database access:** PDO
- **Local server:** Apache through XAMPP
- **Development environment:** Visual Studio Code
- **Version control:** Git and GitHub

## 4. Features

### Authentication and Authorization
- Login for existing student and administrator accounts.
- Password verification using PHP password hashing functions.
- Session-based authentication.
- Role-based redirection for students and administrators.
- Session timeout and logout functionality.
- Optional remember-me functionality.

### Student Portal
- Student access to the portal after successful login.
- Student dashboard and navigation to available portal features.

### Administrator Portal
- Separate administrator login and dashboard.
- Role-based access to administrator features.

### Database Integration
- MySQL database connectivity through PDO.
- Prepared statements for database queries.
- Storage and retrieval of user account information.

### User Interface
- HTML-based pages with CSS styling.
- JavaScript for client-side interactions.
- Responsive layout support, where implemented.

## 5. Project Structure

The project is organized into PHP backend files and frontend pages. The main files include:

```text
practical/
├── db.php
├── auth.php
├── login.php
├── STUDENT HUB/
│   ├── CSS/
│   │   └── styles.css
│   └── PAGES/
│       ├── login.html
│       └── register.html
└── README.md
```

Additional dashboard, event, notice, JavaScript, and other files may be present depending on the modules implemented in the project.

## 6. Requirements

- XAMPP with Apache and MySQL/MariaDB
- PHP supported by the installed XAMPP version
- Visual Studio Code or another code editor
- A web browser

## 7. Installation and Setup

### Step 1: Install and Start XAMPP

1. Install XAMPP.
2. Open the XAMPP Control Panel.
3. Start Apache.
4. Start MySQL.

### Step 2: Place the Project

Copy the project into the following directory:

```text
C:\xampp\htdocs\practical\
```

Ensure that `login.php`, `db.php`, and `auth.php` are located where the application expects them.

### Step 3: Create the Database

1. Open `http://localhost/phpmyadmin/`.
2. Create or select the database named `auth_demo`.
3. Import the project's SQL file if one is available.
4. Ensure that the required tables, including `users`, exist and contain the necessary columns.

The database schema must match the queries used by the PHP files.

### Step 4: Configure the Database Connection

Open `db.php` and verify the database settings:

```php
$host = "localhost";
$dbname = "auth_demo";
$username = "root";
$password = "";
```

These settings are suitable only if they match your local MySQL configuration. Change them if your environment uses different credentials.

### Step 5: Open the Website

Open the login page through Apache:

```text
http://localhost/practical/STUDENT%20HUB/PAGES/login.html
```

Do not use VS Code Live Server to run PHP files. PHP must be processed by Apache with PHP support enabled.

## 8. Authentication and Test Accounts

The application uses the `users` table to retrieve usernames, password hashes, and account roles.

The expected role values are:

- `admin` — administrator account
- `student` — student account

Use the existing test accounts configured in the database. Passwords should be stored as hashes generated using PHP's `password_hash()` function and checked using `password_verify()`.

Do not publish real passwords, database credentials, or private user information in this repository.

## 9. Testing

The following checks should be performed before submission:

- Student login and dashboard access.
- Administrator login and dashboard access.
- Correct redirection based on user role.
- Prevention of unauthorized access to protected pages.
- Logout and session expiration.
- Database connectivity and required database operations.
- Navigation links and form submissions.
- AJAX functionality, where implemented.
- Responsive layout on desktop and mobile-sized screens.

## 10. Security Considerations

- Passwords are verified using PHP's password verification functions.
- Prepared statements are used for database queries.
- Sessions are used to maintain authentication state.
- Role checks should be enforced on the server side.
- User-provided output should be escaped before being rendered in HTML.
- Database credentials and private information should not be committed to GitHub.

## 11. Limitations

The available features depend on the modules implemented and configured in the project. Registration, event management, notice management, AJAX operations, and other modules should be considered complete only after they have been implemented and tested.

## 12. Future Enhancements

- Complete and validate student registration.
- Add profile management.
- Expand event and notice management.
- Improve form validation and error handling.
- Add automated tests.
- Improve accessibility and responsive design.
- Deploy the portal to a production-ready hosting environment.

## 13. Author

**Project:** StudentHub – Student Portal  
**Purpose:** Academic project  
**Technologies:** HTML, CSS, JavaScript, PHP, and MySQL

---

*StudentHub is intended to demonstrate web development, database integration, authentication, and role-based access control in a local development environment.*
