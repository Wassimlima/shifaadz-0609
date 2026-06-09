-- =====================================================
-- SHIFAA DZ — DATABASE EXTENSION
-- MySQL 8.4 Compatible | phpMyAdmin 5.2 Compatible
-- Run AFTER schema.sql — BEFORE seed.sql
-- =====================================================
-- FIXES APPLIED:
--   FIX #3: Removed IF NOT EXISTS from all ADD COLUMN
--           statements. MySQL < 8.3.0 does not support
--           ADD COLUMN IF NOT EXISTS syntax. Since this
--           file is designed to run once on a clean
--           schema (after schema.sql), the guard is
--           unnecessary and caused syntax errors.
-- =====================================================

USE shifaa_dizad;

-- =====================================================
-- EXTEND: users role to include medical_services
-- =====================================================

ALTER TABLE users
  MODIFY COLUMN role ENUM(
    'admin',
    'patient',
    'pharmacist',
    'med_rep',
    'lab',
    'medical_services',
    'delivery'
  ) NOT NULL DEFAULT 'patient';

-- =====================================================
-- EXTEND: pharmacies plan to include basic
-- =====================================================

ALTER TABLE pharmacies
  MODIFY COLUMN plan ENUM(
    'free',
    'basic',
    'professional',
    'enterprise'
  ) DEFAULT 'free';

-- =====================================================
-- EXTEND: subscriptions role_type + fields
-- =====================================================

ALTER TABLE subscriptions
  MODIFY COLUMN role_type ENUM(
    'pharmacy',
    'med_rep',
    'lab',
    'medical_services'
  ) NOT NULL;

ALTER TABLE subscriptions
  ADD COLUMN renewal_status ENUM(
    'auto',
    'manual',
    'cancelled'
  ) DEFAULT 'manual';

ALTER TABLE subscriptions
  ADD COLUMN payment_method VARCHAR(100);

ALTER TABLE subscriptions
  ADD COLUMN notes TEXT;

-- =====================================================
-- EXTEND: notifications type
-- =====================================================

ALTER TABLE notifications
  MODIFY COLUMN type ENUM(
    'system',
    'reservation',
    'warning',
    'subscription',
    'promotion',
    'supply_alert',
    'partnership',
    'advertisement'
  ) DEFAULT 'system';

-- =====================================================
-- PROFESSIONAL PROFILES
-- =====================================================

CREATE TABLE IF NOT EXISTS professional_profiles (
    id                      INT AUTO_INCREMENT PRIMARY KEY,

    user_id                 INT NOT NULL,

    profile_type ENUM(
        'pharmacy',
        'lab',
        'med_rep',
        'medical_services'
    ) NOT NULL,

    business_name           VARCHAR(255) NOT NULL,

    business_name_ar        VARCHAR(255),

    description             TEXT,

    wilaya                  VARCHAR(100),

    city                    VARCHAR(100),

    address                 TEXT,

    phone                   VARCHAR(30),

    email                   VARCHAR(255),

    website                 VARCHAR(255),

    logo_url                TEXT,

    cover_url               TEXT,

    is_verified             TINYINT(1) DEFAULT 0,

    is_active               TINYINT(1) DEFAULT 1,

    verification_notes      TEXT,

    verified_at             TIMESTAMP NULL,

    profile_completeness    TINYINT DEFAULT 0,

    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- ADVERTISEMENTS
-- =====================================================

CREATE TABLE IF NOT EXISTS advertisements (
    id                      INT AUTO_INCREMENT PRIMARY KEY,

    advertiser_user_id      INT NOT NULL,

    title                   VARCHAR(255) NOT NULL,

    title_ar                VARCHAR(255),

    description             TEXT,

    image_url               TEXT,

    target_url              VARCHAR(500),

    ad_type ENUM(
        'homepage_banner',
        'featured_product',
        'sponsored_search',
        'premium_visibility',
        'sidebar_banner'
    ) NOT NULL DEFAULT 'homepage_banner',

    target_audience ENUM(
        'all',
        'patients',
        'professionals'
    ) DEFAULT 'all',

    target_wilaya           VARCHAR(100),

    status ENUM(
        'pending',
        'approved',
        'rejected',
        'active',
        'paused',
        'expired'
    ) DEFAULT 'pending',

    admin_notes             TEXT,

    reviewed_by             INT,

    reviewed_at             TIMESTAMP NULL,

    starts_at               TIMESTAMP NULL,

    ends_at                 TIMESTAMP NULL,

    impressions             INT DEFAULT 0,

    clicks                  INT DEFAULT 0,

    budget                  DECIMAL(10,2),

    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- SUPPLY REQUESTS
-- =====================================================

CREATE TABLE IF NOT EXISTS supply_requests (
    id                  INT AUTO_INCREMENT PRIMARY KEY,

    pharmacy_id         INT NOT NULL,

    pharmacy_user_id    INT,

    product_name        VARCHAR(255) NOT NULL,

    product_type ENUM(
        'medicine',
        'device',
        'parapharmacy',
        'home_care',
        'special_needs',
        'other'
    ) DEFAULT 'medicine',

    requested_quantity  INT DEFAULT 1,

    urgency ENUM(
        'low',
        'medium',
        'high',
        'critical'
    ) DEFAULT 'medium',

    notes               TEXT,

    target_rep_id       INT,

    status ENUM(
        'open',
        'assigned',
        'accepted',
        'rejected',
        'forwarded',
        'fulfilled',
        'cancelled'
    ) DEFAULT 'open',

    assigned_rep_id     INT,

    assigned_at         TIMESTAMP NULL,

    fulfilled_at        TIMESTAMP NULL,

    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- INVENTORY SYNC LOGS
-- =====================================================

CREATE TABLE IF NOT EXISTS inventory_sync_logs (
    id                  INT AUTO_INCREMENT PRIMARY KEY,

    pharmacy_id         INT NOT NULL,

    sync_type ENUM(
        'csv_import',
        'excel_import',
        'api_sync',
        'manual_update',
        'realtime_sync'
    ) NOT NULL DEFAULT 'csv_import',

    sync_status ENUM(
        'pending',
        'processing',
        'success',
        'partial',
        'failed'
    ) DEFAULT 'pending',

    file_name           VARCHAR(255),

    file_url            TEXT,

    total_records       INT DEFAULT 0,

    imported_records    INT DEFAULT 0,

    updated_records     INT DEFAULT 0,

    failed_records      INT DEFAULT 0,

    error_log           LONGTEXT,

    last_sync           TIMESTAMP NULL,

    duration_ms         INT,

    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- EXTEND: pharmacies with sync fields
-- =====================================================

ALTER TABLE pharmacies
  ADD COLUMN sync_status ENUM(
    'not_synced',
    'syncing',
    'synced',
    'error'
  ) DEFAULT 'not_synced';

ALTER TABLE pharmacies
  ADD COLUMN last_sync TIMESTAMP NULL;

ALTER TABLE pharmacies
  ADD COLUMN sync_method ENUM(
    'manual',
    'csv',
    'excel',
    'api'
  ) DEFAULT 'manual';

-- =====================================================
-- EXTEND: analytics_events with new fields
-- =====================================================

ALTER TABLE analytics_events
  ADD COLUMN event_data JSON;

ALTER TABLE analytics_events
  ADD COLUMN session_id VARCHAR(100);

ALTER TABLE analytics_events
  ADD COLUMN wilaya VARCHAR(100);

ALTER TABLE analytics_events
  ADD COLUMN search_query VARCHAR(500);

ALTER TABLE analytics_events
  ADD COLUMN product_type VARCHAR(100);

ALTER TABLE analytics_events
  ADD COLUMN referrer TEXT;

-- =====================================================
-- PAYMENT HISTORY
-- =====================================================

CREATE TABLE IF NOT EXISTS payment_history (
    id                  INT AUTO_INCREMENT PRIMARY KEY,

    subscription_id     INT,

    user_id             INT NOT NULL,

    amount              DECIMAL(10,2) NOT NULL,

    currency            VARCHAR(10) DEFAULT 'DZD',

    payment_method      VARCHAR(100),

    transaction_id      VARCHAR(255),

    status ENUM(
        'pending',
        'completed',
        'failed',
        'refunded'
    ) DEFAULT 'pending',

    notes               TEXT,

    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- =====================================================
-- PLATFORM SETTINGS
-- =====================================================

-- FIX #4: setting_key reduced from VARCHAR(255) to VARCHAR(191).
-- With utf8mb4 (4 bytes/char), VARCHAR(255) = 1020 bytes which
-- exceeds the 1000-byte index limit on MyISAM. VARCHAR(191) = 764
-- bytes, safely under the limit on any engine. ENGINE=InnoDB and
-- ROW_FORMAT=DYNAMIC are also set explicitly to guarantee the
-- 3072-byte index limit regardless of server default engine.

CREATE TABLE IF NOT EXISTS platform_settings (
    id              INT AUTO_INCREMENT PRIMARY KEY,

    setting_key     VARCHAR(191) UNIQUE NOT NULL,        -- FIX #4

    setting_value   TEXT,

    setting_type ENUM(
        'string',
        'integer',
        'boolean',
        'json'
    ) DEFAULT 'string',

    description     TEXT,

    updated_by      INT,

    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB                                          -- FIX #4
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  ROW_FORMAT=DYNAMIC;

-- Default platform settings
INSERT IGNORE INTO platform_settings
    (setting_key, setting_value, setting_type, description)
VALUES
    ('platform_name',           'شفاء ديزاد',              'string',  'اسم المنصة'),
    ('max_free_products',       '50',                       'integer', 'الحد الأقصى للمنتجات في الباقة المجانية'),
    ('max_basic_products',      '500',                      'integer', 'الحد الأقصى للمنتجات في الباقة الأساسية'),
    ('enable_advertisements',   '1',                        'boolean', 'تفعيل نظام الإعلانات'),
    ('enable_supply_requests',  '1',                        'boolean', 'تفعيل نظام طلبات التوريد'),
    ('maintenance_mode',        '0',                        'boolean', 'وضع الصيانة'),
    ('contact_email',           'contact@shifaa-dz.com',   'string',  'البريد الإلكتروني للتواصل'),
    ('supported_wilayas',       '69',                       'integer', 'عدد الولايات المدعومة');
