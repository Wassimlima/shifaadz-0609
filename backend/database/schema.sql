-- =====================================================
-- SHIFAA DZ — MAIN SCHEMA
-- MySQL 8.4 Compatible | phpMyAdmin 5.2 Compatible
-- Run this file FIRST
-- =====================================================

CREATE DATABASE IF NOT EXISTS shifaa_dizad
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE shifaa_dizad;

-- =====================================================
-- USERS
-- =====================================================
-- FIX #1: Added full_name column (required by seed.sql)
-- FIX #2: Added password_hash column (required by seed.sql)

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,

    full_name     VARCHAR(255),                          -- FIX #1: was missing

    email         VARCHAR(191) NOT NULL,
    UNIQUE KEY uk_users_email (email),

    phone         VARCHAR(30),
    UNIQUE KEY uk_users_phone (phone),

    password_hash VARCHAR(255),                          -- FIX #2: was missing

    role ENUM(
        'admin',
        'patient',
        'pharmacist',
        'med_rep',
        'lab',
        'delivery'
    ) NOT NULL DEFAULT 'patient',

    avatar        TEXT,

    is_verified   TINYINT(1) DEFAULT 0,

    is_active     TINYINT(1) DEFAULT 1,

    last_login    TIMESTAMP NULL,

    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- PHARMACIES
-- =====================================================

CREATE TABLE pharmacies (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    owner_user_id   INT,

    name            VARCHAR(255) NOT NULL,

    address         TEXT NOT NULL,

    wilaya          VARCHAR(100) NOT NULL,

    city            VARCHAR(100) NOT NULL,

    phone           VARCHAR(30) NOT NULL,

    email           VARCHAR(255),

    description     TEXT,

    logo_url        TEXT,

    cover_image     TEXT,

    rating          FLOAT DEFAULT 4.0,

    review_count    INT DEFAULT 0,

    is_open         TINYINT(1) DEFAULT 1,

    opening_hours   VARCHAR(100),

    latitude        FLOAT,

    longitude       FLOAT,

    is_verified     TINYINT(1) DEFAULT 0,

    plan ENUM(
        'free',
        'professional',
        'enterprise'
    ) DEFAULT 'free',

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- CATEGORIES
-- =====================================================

CREATE TABLE categories (
    id            INT AUTO_INCREMENT PRIMARY KEY,

    name_ar       VARCHAR(255) NOT NULL,

    name_en       VARCHAR(255) NOT NULL,

    icon          VARCHAR(100),

    color         VARCHAR(100),

    slug          VARCHAR(100) UNIQUE,

    product_count INT DEFAULT 0

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- MEDICINES
-- =====================================================

CREATE TABLE medicines (
    id                      INT AUTO_INCREMENT PRIMARY KEY,

    pharmacy_id             INT NOT NULL,

    category_id             INT,

    name                    VARCHAR(255) NOT NULL,

    name_ar                 VARCHAR(255),

    active_ingredient       TEXT,

    barcode                 VARCHAR(255),

    manufacturer            VARCHAR(255),

    dosage                  VARCHAR(100),

    form_type               VARCHAR(100),

    requires_prescription   TINYINT(1) DEFAULT 0,

    type ENUM(
        'medicine',
        'device',
        'parapharmacy',
        'emergency',
        'special_needs',
        'home_care',
        'cosmetic'
    ) DEFAULT 'medicine',

    availability ENUM(
        'available',
        'limited',
        'unavailable'
    ) DEFAULT 'available',

    quantity    INT DEFAULT 0,

    price       DECIMAL(10,2),

    rating      FLOAT DEFAULT 4.0,

    distance    FLOAT,

    image_url   TEXT,

    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- INVENTORY
-- =====================================================

CREATE TABLE inventory (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    pharmacy_id     INT NOT NULL,

    medicine_id     INT,

    product_name    VARCHAR(255),

    category        VARCHAR(100),

    quantity        INT DEFAULT 0,

    minimum_stock   INT DEFAULT 5,

    expiry_date     DATE,

    batch_number    VARCHAR(255),

    supplier_name   VARCHAR(255),

    status ENUM(
        'available',
        'limited',
        'unavailable'
    ) DEFAULT 'available',

    price           DECIMAL(10,2),

    last_updated    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- RESERVATIONS
-- =====================================================

CREATE TABLE reservations (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    user_id         INT,

    medicine_id     INT NOT NULL,

    pharmacy_id     INT NOT NULL,

    quantity        INT DEFAULT 1,

    patient_name    VARCHAR(255),

    patient_phone   VARCHAR(30),

    notes           TEXT,

    status ENUM(
        'pending',
        'confirmed',
        'ready',
        'completed',
        'cancelled'
    ) DEFAULT 'pending',

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- DONATIONS
-- =====================================================

CREATE TABLE donations (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    user_id         INT,

    item_name       VARCHAR(255) NOT NULL,

    item_name_ar    VARCHAR(255),

    description     TEXT,

    category        VARCHAR(100),

    `condition` ENUM(
        'new',
        'good',
        'fair'
    ) DEFAULT 'good',

    wilaya          VARCHAR(100),

    city            VARCHAR(100),

    donor_name      VARCHAR(255),

    donor_phone     VARCHAR(30),

    image_url       TEXT,

    is_available    TINYINT(1) DEFAULT 1,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- LABS
-- =====================================================

CREATE TABLE labs (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    owner_user_id   INT,

    name            VARCHAR(255) NOT NULL,

    name_ar         VARCHAR(255),

    address         TEXT,

    wilaya          VARCHAR(100),

    city            VARCHAR(100),

    phone           VARCHAR(30),

    email           VARCHAR(255),

    maps_link       TEXT,

    opening_hours   VARCHAR(100),

    is_open         TINYINT(1) DEFAULT 1,

    rating          FLOAT DEFAULT 4.0,

    review_count    INT DEFAULT 0,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- LAB ANALYSES
-- =====================================================

CREATE TABLE lab_analyses (
    id                  INT AUTO_INCREMENT PRIMARY KEY,

    lab_id              INT NOT NULL,

    name                VARCHAR(255),

    name_ar             VARCHAR(255),

    category            VARCHAR(100),

    price               DECIMAL(10,2),

    preparation_time    VARCHAR(255),

    description         TEXT,

    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- PRESCRIPTIONS
-- =====================================================

CREATE TABLE prescriptions (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    user_id         INT,

    image_url       TEXT NOT NULL,

    patient_name    VARCHAR(255),

    notes           TEXT,

    status ENUM(
        'pending',
        'processing',
        'verified',
        'rejected'
    ) DEFAULT 'pending',

    uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- PRESCRIPTION AI LOGS
-- =====================================================

CREATE TABLE prescription_ai_logs (
    id                      INT AUTO_INCREMENT PRIMARY KEY,

    prescription_id         INT NOT NULL,

    extracted_text          LONGTEXT,

    detected_medicines      LONGTEXT,

    suggested_alternatives  LONGTEXT,

    confidence_score        FLOAT,

    processed_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- MEDICAL REPRESENTATIVES
-- =====================================================

CREATE TABLE med_reps (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    user_id         INT,

    company_name    VARCHAR(255),

    region          VARCHAR(255),

    email           VARCHAR(255),

    phone           VARCHAR(30),

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- REP PRODUCTS
-- =====================================================

CREATE TABLE rep_products (
    id                      INT AUTO_INCREMENT PRIMARY KEY,

    rep_id                  INT NOT NULL,

    name                    VARCHAR(255),

    total_stock             INT DEFAULT 0,

    low_stock_pharmacies    INT DEFAULT 0,

    status ENUM(
        'good',
        'warning',
        'critical'
    ) DEFAULT 'good'

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- ALERTS
-- =====================================================

CREATE TABLE rep_alerts (
    id                  INT AUTO_INCREMENT PRIMARY KEY,

    rep_id              INT,

    pharmacy_id         INT,

    pharmacy_name       VARCHAR(255),

    pharmacy_phone      VARCHAR(30),

    product_name        VARCHAR(255),

    remaining_stock     INT,

    severity ENUM(
        'low',
        'medium',
        'high'
    ) DEFAULT 'medium',

    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- PARTNERSHIP REQUESTS
-- =====================================================

CREATE TABLE partnership_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    rep_id          INT,

    pharmacy_id     INT,

    status ENUM(
        'pending',
        'accepted',
        'rejected',
        'revoked'
    ) DEFAULT 'pending',

    message         TEXT,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- RESUPPLY REQUESTS
-- =====================================================

CREATE TABLE resupply_requests (
    id                  INT AUTO_INCREMENT PRIMARY KEY,

    rep_id              INT,

    pharmacy_id         INT,

    product_name        VARCHAR(255),

    requested_quantity  INT DEFAULT 1,

    message             TEXT,

    status ENUM(
        'pending',
        'approved',
        'rejected',
        'completed'
    ) DEFAULT 'pending',

    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- SUBSCRIPTIONS
-- =====================================================

CREATE TABLE subscriptions (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    user_id         INT NOT NULL,

    plan_name       VARCHAR(100),

    role_type ENUM(
        'pharmacy',
        'med_rep',
        'lab'
    ) NOT NULL,

    billing_cycle ENUM(
        'monthly',
        'yearly'
    ) NOT NULL,

    price           DECIMAL(10,2),

    status ENUM(
        'active',
        'expired',
        'cancelled',
        'trial'
    ) DEFAULT 'active',

    starts_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    expires_at      TIMESTAMP NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- REVIEWS
-- =====================================================

CREATE TABLE reviews (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    user_id         INT,

    target_type ENUM(
        'pharmacy',
        'lab',
        'medicine'
    ),

    target_id       INT,

    rating          INT,

    comment         TEXT,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- FAVORITES
-- =====================================================

CREATE TABLE favorites (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    user_id         INT,

    medicine_id     INT,

    pharmacy_id     INT,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- NOTIFICATIONS
-- =====================================================

CREATE TABLE notifications (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    user_id         INT,

    title           VARCHAR(255),

    message         TEXT,

    type ENUM(
        'system',
        'reservation',
        'warning',
        'subscription',
        'promotion'
    ) DEFAULT 'system',

    is_read         TINYINT(1) DEFAULT 0,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- CONTACT MESSAGES
-- =====================================================

CREATE TABLE contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,

    name        VARCHAR(255),

    email       VARCHAR(255),

    subject     VARCHAR(255),

    message     TEXT,

    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- SUPPLIERS
-- =====================================================

CREATE TABLE suppliers (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    company_name    VARCHAR(255),

    phone           VARCHAR(30),

    email           VARCHAR(255),

    address         TEXT,

    wilaya          VARCHAR(100),

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- ORDERS
-- =====================================================

CREATE TABLE orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    pharmacy_id     INT,

    supplier_id     INT,

    total_amount    DECIMAL(10,2),

    status ENUM(
        'pending',
        'confirmed',
        'shipped',
        'delivered',
        'cancelled'
    ) DEFAULT 'pending',

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- ORDER ITEMS
-- =====================================================

CREATE TABLE order_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    order_id        INT,

    medicine_id     INT,

    quantity        INT,

    unit_price      DECIMAL(10,2)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- ANALYTICS EVENTS
-- =====================================================

CREATE TABLE analytics_events (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,

    user_id         INT,

    event_type      VARCHAR(100),

    page_url        TEXT,

    device_type     VARCHAR(50),

    ip_address      VARCHAR(100),

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- PHARMACY PERMANENCE
-- =====================================================

CREATE TABLE pharmacy_permanence (
    id                  INT AUTO_INCREMENT PRIMARY KEY,

    pharmacy_id         INT,

    permanence_date     DATE,

    start_time          TIME,

    end_time            TIME,

    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- CHAT MESSAGES
-- =====================================================

CREATE TABLE chat_messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    sender_id       INT,

    receiver_id     INT,

    message         TEXT,

    is_read         TINYINT(1) DEFAULT 0,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- API TOKENS
-- =====================================================

CREATE TABLE api_tokens (
    id          INT AUTO_INCREMENT PRIMARY KEY,

    user_id     INT,

    token       TEXT,

    expires_at  TIMESTAMP NULL,

    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
