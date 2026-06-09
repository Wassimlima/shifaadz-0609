-- =====================================================
-- SHIFAA DZ — SEED DATA
-- MySQL 8.4 Compatible | phpMyAdmin 5.2 Compatible
-- Run LAST — after schema.sql and schema_extension.sql
-- =====================================================
-- All INSERT statements verified against final schema.
-- No changes were required in this file; all errors
-- were caused by missing columns in schema.sql (now
-- fixed in the corrected schema.sql).
-- =====================================================

USE shifaa_dizad;

-- =====================================================
-- USERS
-- =====================================================

INSERT INTO users
    (full_name, email, phone, password_hash, role, is_verified)
VALUES
    ('Admin Shifaa',            'admin@shifaa.dz',    '0555000001', '$2y$10$3kmnvpHDSWMM2HA8mzuAm.KcaMYd5NwcsRfHjcBrYU2yTkz3De/B2', 'admin',      1),
    ('Ahmed Benali',            'ahmed@shifaa.dz',    '0555000002', '$2y$10$SreYL1XiHjkIIhzsiJ2swe6iHx/.mjeXD6zJAa5d7kpL6TqIiX76.', 'patient',    1),
    ('Pharmacie El Amal Owner', 'amal@pharmacy.dz',   '0555000003', '$2y$10$SreYL1XiHjkIIhzsiJ2swe6iHx/.mjeXD6zJAa5d7kpL6TqIiX76.', 'pharmacist', 1),
    ('Karim MedRep',            'karim@medrep.dz',    '0555000004', '$2y$10$SreYL1XiHjkIIhzsiJ2swe6iHx/.mjeXD6zJAa5d7kpL6TqIiX76.', 'med_rep',    1),
    ('Lab Central Alger',       'lab@central.dz',     '0555000005', '$2y$10$SreYL1XiHjkIIhzsiJ2swe6iHx/.mjeXD6zJAa5d7kpL6TqIiX76.', 'lab',        1);

-- =====================================================
-- PHARMACIES
-- =====================================================

INSERT INTO pharmacies
    (owner_user_id, name, address, wilaya, city, phone, email, description, rating, review_count, is_open, opening_hours, plan, is_verified)
VALUES
    (3, 'صيدلية الأمل',  '12 شارع العربي بن مهيدي', 'الجزائر',   'الجزائر العاصمة', '0550000001', 'amal@pharmacy.dz',  'صيدلية حديثة توفر الأدوية والأجهزة الطبية وخدمة الحجز', 4.8, 120, 1, '08:00 - 22:00', 'professional', 1),
    (3, 'صيدلية النور',  '5 طريق الاستقلال',        'الجزائر',   'باب الوادي',       '0550000002', 'nour@pharmacy.dz',  'صيدلية متخصصة في أدوية الأمراض المزمنة',               4.5, 85,  1, '24/7',          'free',         1),
    (3, 'صيدلية الشفاء', '23 شارع الثورة',           'وهران',     'وهران',            '0550000003', 'shifa@pharmacy.dz', 'صيدلية توفر خدمة التوصيل',                              4.7, 200, 1, '08:00 - 23:00', 'enterprise',   1),
    (3, 'صيدلية الرحمة', '8 حي المرادية',            'قسنطينة',  'قسنطينة',          '0550000004', 'rahma@pharmacy.dz', 'صيدلية متخصصة في مستلزمات الأطفال',                    4.3, 60,  0, '09:00 - 21:00', 'free',         1),
    (3, 'صيدلية الصحة',  '31 شارع ابن خلدون',        'عنابة',    'عنابة',            '0550000005', 'saha@pharmacy.dz',  'صيدلية متطورة بخدمة الحجز الإلكتروني',                 4.9, 310, 1, '24/7',          'enterprise',   1);

-- =====================================================
-- CATEGORIES
-- =====================================================

INSERT INTO categories
    (name_ar, name_en, icon, color, product_count, slug)
VALUES
    ('الأدوية العامة',    'General Medicines', '💊', '#10b981', 245, 'general'),
    ('الأجهزة الطبية',   'Medical Devices',   '🩺', '#0ea5e9', 89,  'devices'),
    ('مواد التجميل',      'Cosmetics',         '✨', '#ec4899', 120, 'cosmetics'),
    ('شبه صيدلاني',       'Parapharmacy',      '🧴', '#f59e0b', 156, 'parapharmacy'),
    ('الاحتياجات الخاصة', 'Special Needs',     '♿', '#8b5cf6', 67,  'special-needs'),
    ('المكملات الغذائية', 'Supplements',       '💪', '#14b8a6', 112, 'supplements');

-- =====================================================
-- MEDICINES
-- =====================================================

INSERT INTO medicines
    (pharmacy_id, category_id, name, name_ar, active_ingredient, manufacturer, dosage, form_type, requires_prescription, type, availability, quantity, price, rating)
VALUES
    (1, 1, 'Doliprane 1000mg',       'دوليبران 1000 ملغ', 'Paracetamol',  'Sanofi',          '1000mg', 'Tablet',    0, 'medicine',      'available', 150, 180,   4.8),
    (1, 1, 'Ventoline',              'فانتولين',          'Salbutamol',   'GSK',             '100mcg', 'Inhaler',   1, 'medicine',      'limited',   12,  320,   4.7),
    (2, 1, 'Augmentin 1g',           'أوغمنتين',          'Amoxicillin',  'GSK',             '1g',     'Tablet',    1, 'medicine',      'available', 80,  690,   4.6),
    (2, 2, 'Blood Pressure Monitor', 'جهاز قياس الضغط',  NULL,           'Omron',           NULL,     'Device',    0, 'device',        'available', 15,  7500,  4.9),
    (3, 3, 'La Roche Posay Serum',   'سيروم لاروش',       NULL,           'La Roche Posay',  NULL,     'Cosmetic',  0, 'cosmetic',      'available', 20,  4500,  4.8),
    (4, 4, 'Baby Shampoo',           'شامبو أطفال',       NULL,           'Mustela',         NULL,     'Parapharmacy', 0, 'parapharmacy', 'available', 40, 1200, 4.5),
    (5, 1, 'Insuline Glargine',      'أنسولين',           'Insulin',      'Novo Nordisk',    '100UI',  'Injection', 1, 'medicine',      'limited',   5,   2500,  4.9),
    (5, 5, 'Wheelchair',             'كرسي متحرك',        NULL,           'Medical DZ',      NULL,     'Equipment', 0, 'special_needs', 'available', 3,   32000, 4.7);

-- =====================================================
-- INVENTORY
-- =====================================================

INSERT INTO inventory
    (pharmacy_id, medicine_id, product_name, category, quantity, minimum_stock, expiry_date, batch_number, supplier_name, status, price)
VALUES
    (1, 1, 'Doliprane 1000mg',  'medicine', 45, 10, '2027-12-01', 'DP2025', 'Sanofi DZ',     'available', 180),
    (1, 2, 'Ventoline',         'medicine', 8,  10, '2026-09-01', 'VT2025', 'GSK Algeria',   'limited',   320),
    (2, 3, 'Augmentin 1g',      'medicine', 60, 15, '2027-01-01', 'AG2025', 'GSK Algeria',   'available', 690),
    (5, 7, 'Insuline Glargine', 'medicine', 3,  5,  '2026-05-01', 'IN2025', 'Novo Nordisk',  'limited',   2500);

-- =====================================================
-- DONATIONS
-- =====================================================

INSERT INTO donations
    (user_id, item_name, item_name_ar, description, category, `condition`, wilaya, city, donor_name, donor_phone)
VALUES
    (2, 'Wheelchair', 'كرسي متحرك', 'كرسي متحرك بحالة ممتازة', 'special_needs', 'good', 'الجزائر', 'الجزائر العاصمة', 'محمد بن علي',  '0557000001'),
    (2, 'Crutches',   'عكازات',     'عكازات جديدة',             'special_needs', 'new',  'وهران',   'وهران',            'فاطمة زهراء', '0557000002'),
    (2, 'Nebulizer',  'جهاز بخار',  'جهاز للأطفال',             'device',        'good', 'عنابة',   'عنابة',            'سارة حداد',   '0557000003');

-- =====================================================
-- LABS
-- =====================================================

INSERT INTO labs
    (owner_user_id, name, name_ar, address, wilaya, city, phone, email, maps_link, opening_hours, is_open, rating, review_count)
VALUES
    (5, 'Laboratoire Central Alger', 'مخبر الجزائر المركزي', '5 شارع ديدوش مراد',  'الجزائر', 'الجزائر العاصمة', '0550100001', 'central@lab.dz', 'https://maps.google.com', '07:00 - 18:00', 1, 4.8, 320),
    (5, 'BioLab Annaba',             'مخبر بيولاب عنابة',    '3 شارع ابن خلدون',   'عنابة',   'عنابة',            '0550100002', 'biolab@lab.dz',  'https://maps.google.com', '07:00 - 19:00', 1, 4.7, 210);

-- =====================================================
-- LAB ANALYSES
-- =====================================================

INSERT INTO lab_analyses
    (lab_id, name, name_ar, category, price, preparation_time, description)
VALUES
    (1, 'NFS',              'تعداد الدم الكامل',      'hematologie',  600,  '1 hour',    'تحليل شامل لخلايا الدم'),
    (1, 'TSH',              'هرمون الغدة الدرقية',    'hormonologie', 1500, '24 hours',  'فحص الغدة الدرقية'),
    (1, 'Glycémie',         'سكر الدم',               'biochimie',    300,  '30 minutes','قياس مستوى السكر'),
    (2, 'CRP',              'بروتين سي التفاعلي',     'immunologie',  700,  '1 hour',    'تحليل الالتهابات'),
    (2, 'Bilan lipidique',  'بيلان الدهون',           'biochimie',    1200, '24 hours',  'تحليل الدهون والكوليسترول');

-- =====================================================
-- PRESCRIPTIONS
-- =====================================================

INSERT INTO prescriptions
    (user_id, image_url, patient_name, notes, status)
VALUES
    (2, 'uploads/prescriptions/rx1.jpg', 'Ahmed Benali', 'Prescription diabète', 'verified'),
    (2, 'uploads/prescriptions/rx2.jpg', 'Sara Haddad',  'Prescription ORL',     'processing');

-- =====================================================
-- PRESCRIPTION AI LOGS
-- =====================================================

INSERT INTO prescription_ai_logs
    (prescription_id, extracted_text, detected_medicines, suggested_alternatives, confidence_score)
VALUES
    (1, 'Doliprane 1000mg + Ventoline', 'Doliprane,Ventoline', 'Paracetamol Generic', 0.94),
    (2, 'Augmentin 1g',                 'Augmentin',           'Amoxicilline',        0.89);

-- =====================================================
-- MED REPS
-- =====================================================

INSERT INTO med_reps
    (user_id, company_name, region, email, phone)
VALUES
    (4, 'Pharma DZ', 'الجزائر - البليدة - تيبازة', 'karim@pharmadz.dz', '0770123456');

-- =====================================================
-- REP PRODUCTS
-- =====================================================

INSERT INTO rep_products
    (rep_id, name, total_stock, low_stock_pharmacies, status)
VALUES
    (1, 'Doliprane 1000mg', 450, 2, 'warning'),
    (1, 'Ventoline',        120, 5, 'critical'),
    (1, 'Insuline Glargine', 45, 1, 'good');

-- =====================================================
-- ALERTS
-- =====================================================

INSERT INTO rep_alerts
    (rep_id, pharmacy_id, pharmacy_name, pharmacy_phone, product_name, remaining_stock, severity)
VALUES
    (1, 1, 'صيدلية الأمل',  '0550000001', 'Ventoline',       2, 'high'),
    (1, 5, 'صيدلية الصحة',  '0550000005', 'Insuline Glargine', 1, 'high');

-- =====================================================
-- PARTNERSHIP REQUESTS
-- =====================================================

INSERT INTO partnership_requests
    (rep_id, pharmacy_id, status, message)
VALUES
    (1, 1, 'accepted', 'طلب شراكة لتتبع المخزون'),
    (1, 5, 'pending',  'تعاون استراتيجي للأدوية المزمنة');

-- =====================================================
-- RESUPPLY REQUESTS
-- =====================================================

INSERT INTO resupply_requests
    (rep_id, pharmacy_id, product_name, requested_quantity, message, status)
VALUES
    (1, 1, 'Doliprane 1000mg', 50, 'إعادة تموين عاجلة',       'pending'),
    (1, 5, 'Insuline Glargine', 20, 'منتج حساس يحتاج تبريد', 'approved');

-- =====================================================
-- SUBSCRIPTIONS
-- =====================================================

INSERT INTO subscriptions
    (user_id, plan_name, role_type, billing_cycle, price, status)
VALUES
    (3, 'Professional Pharmacy', 'pharmacy', 'monthly', 4900,  'active'),
    (4, 'MedRep Premium',        'med_rep',  'yearly',  25000, 'active'),
    (5, 'Lab Premium',           'lab',      'monthly', 6900,  'trial');

-- =====================================================
-- REVIEWS
-- =====================================================

INSERT INTO reviews
    (user_id, target_type, target_id, rating, comment)
VALUES
    (2, 'pharmacy', 1, 5, 'خدمة ممتازة وسريعة'),
    (2, 'lab',      1, 4, 'نتائج دقيقة وسريعة');

-- =====================================================
-- FAVORITES
-- =====================================================

INSERT INTO favorites
    (user_id, medicine_id, pharmacy_id)
VALUES
    (2, 1,    NULL),
    (2, NULL, 1);

-- =====================================================
-- NOTIFICATIONS
-- =====================================================

INSERT INTO notifications
    (user_id, title, message, type, is_read)
VALUES
    (2, 'تم تأكيد الحجز',  'تم تأكيد حجز دوائك',    'reservation',  0),
    (3, 'تجديد الاشتراك',  'اشتراكك سينتهي قريباً', 'subscription', 0);

-- =====================================================
-- CONTACT MESSAGES
-- =====================================================

INSERT INTO contact_messages
    (name, email, subject, message)
VALUES
    ('Ahmed', 'ahmed@gmail.com', 'اقتراح', 'واجهة الموقع رائعة'),
    ('Sara',  'sara@gmail.com',  'مشكلة',  'البحث لا يعمل أحياناً');

-- =====================================================
-- SUPPLIERS
-- =====================================================

INSERT INTO suppliers
    (company_name, phone, email, address, wilaya)
VALUES
    ('Sanofi Algeria',  '021000001', 'contact@sanofi.dz', 'الجزائر العاصمة', 'الجزائر'),
    ('Novo Nordisk DZ', '021000002', 'info@novo.dz',       'وهران',            'وهران');

-- =====================================================
-- ORDERS
-- =====================================================

INSERT INTO orders
    (pharmacy_id, supplier_id, total_amount, status)
VALUES
    (1, 1, 25000, 'confirmed'),
    (5, 2, 54000, 'shipped');

-- =====================================================
-- ORDER ITEMS
-- =====================================================

INSERT INTO order_items
    (order_id, medicine_id, quantity, unit_price)
VALUES
    (1, 1, 100, 180),
    (2, 7, 20,  2500);

-- =====================================================
-- ANALYTICS EVENTS
-- =====================================================

INSERT INTO analytics_events
    (user_id, event_type, page_url, device_type, ip_address)
VALUES
    (2, 'search',      '/pages/search.html',   'mobile',  '127.0.0.1'),
    (2, 'reservation', '/pages/medicine.html', 'desktop', '127.0.0.1');

-- =====================================================
-- PHARMACY PERMANENCE
-- =====================================================

INSERT INTO pharmacy_permanence
    (pharmacy_id, permanence_date, start_time, end_time)
VALUES
    (1, '2026-05-15', '20:00:00', '08:00:00'),
    (5, '2026-05-15', '20:00:00', '08:00:00');

-- =====================================================
-- CHAT MESSAGES
-- =====================================================

INSERT INTO chat_messages
    (sender_id, receiver_id, message, is_read)
VALUES
    (2, 3, 'هل الدواء متوفر؟', 1),
    (3, 2, 'نعم متوفر حالياً',  0);

-- =====================================================
-- API TOKENS
-- =====================================================

INSERT INTO api_tokens
    (user_id, token)
VALUES
    (2, 'token_demo_123456'),
    (3, 'token_demo_789456');
