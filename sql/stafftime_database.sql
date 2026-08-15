-- =====================================================
-- StaffTime - Digital Staff Timebook System
-- Database Schema (MySQL / MariaDB compatible)
-- Created for multi-school use (African schools)
-- =====================================================

-- Create the database (optional - run this first if needed)
-- CREATE DATABASE IF NOT EXISTS stafftime CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE stafftime;

-- =====================================================
-- 1. Schools table
-- =====================================================
CREATE TABLE schools (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(255) NOT NULL,
    school_code VARCHAR(30) UNIQUE NOT NULL,
    address TEXT NULL,
    state VARCHAR(100) NULL,
    country VARCHAR(100) DEFAULT 'Nigeria',
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    logo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- 2. Users table (Admins + Staff)
-- =====================================================
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    staff_id_number VARCHAR(50) NULL,
    department VARCHAR(100) NULL,
    photo VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_role (school_id, role),
    INDEX idx_staff_id (staff_id_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- 3. Academic Sessions (e.g. 2025/2026)
-- =====================================================
CREATE TABLE sessions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    session_name VARCHAR(50) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_current TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_current (school_id, is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- 4. Terms (3 terms make one session)
-- =====================================================
CREATE TABLE terms (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    session_id BIGINT UNSIGNED NOT NULL,
    term_number TINYINT NOT NULL,
    term_name VARCHAR(50) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_term (session_id, term_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- 5. Attendance records (Daily Timebook)
-- =====================================================
CREATE TABLE attendances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    attendance_date DATE NOT NULL,
    check_in_time TIME NULL,
    check_out_time TIME NULL,
    status ENUM('present', 'late', 'absent', 'leave', 'holiday') DEFAULT 'present',
    photo_url VARCHAR(255) NULL,
    notes TEXT NULL,
    marked_by ENUM('system', 'admin') DEFAULT 'system',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_daily_attendance (user_id, attendance_date),
    INDEX idx_school_date (school_id, attendance_date),
    INDEX idx_user_date (user_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- 6. Leave requests
-- =====================================================
CREATE TABLE leaves (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_school_status (school_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- End of schema
-- =====================================================
