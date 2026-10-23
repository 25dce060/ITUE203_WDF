CREATE DATABASE IF NOT EXISTS studenthub
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE studenthub;

CREATE TABLE IF NOT EXISTS students (
    student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    mobile VARCHAR(10) NOT NULL,
    course VARCHAR(100) NOT NULL,
    study_year TINYINT UNSIGNED NOT NULL,
    gender VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    event_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_event (title, event_date)
);

CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL DEFAULT 'Registered',

    UNIQUE KEY unique_registration (student_id, event_id),

    CONSTRAINT fk_registration_student
        FOREIGN KEY (student_id)
        REFERENCES students(student_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_registration_event
        FOREIGN KEY (event_id)
        REFERENCES events(event_id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO events
(title, description, category, event_date, venue)
VALUES
(
    'Technical Fest',
    'Coding competitions, technical workshops and project exhibitions.',
    'Technical',
    '2026-11-15',
    'Innovation Hall'
),
(
    'Annual Sports Day',
    'Inter-department sports competitions and athletics.',
    'Sports',
    '2026-12-05',
    'Main Ground'
),
(
    'Cultural Festival',
    'Music, dance and cultural performances.',
    'Cultural',
    '2027-01-10',
    'Auditorium'
);