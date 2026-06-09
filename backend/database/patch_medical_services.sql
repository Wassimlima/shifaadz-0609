-- Run once on existing shifaa_dizad DB to fix medical_services dashboard (BUG-06)
USE shifaa_dizad;

SET @ms_id = (SELECT id FROM users WHERE email = 'medservices@shifaa.dz' LIMIT 1);

INSERT INTO pharmacies
    (owner_user_id, name, address, wilaya, city, phone, email, description, rating, review_count, is_open, opening_hours, plan, is_verified)
SELECT @ms_id, 'ميدي ستور', '15 شارع ديدوش مراد', 'الجزائر', 'الجزائر العاصمة', '0555000013', 'medservices@shifaa.dz',
       'متجر خدمات طبية — أجهزة ومستلزمات', 4.8, 95, 1, '08:00 - 20:00', 'enterprise', 1
FROM DUAL
WHERE @ms_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM pharmacies WHERE owner_user_id = @ms_id LIMIT 1);

SET @ph_id = (SELECT id FROM pharmacies WHERE owner_user_id = @ms_id ORDER BY id ASC LIMIT 1);

INSERT INTO inventory (pharmacy_id, product_name, category, quantity, minimum_stock, status, price)
SELECT @ph_id, 'جهاز قياس الضغط', 'device', 25, 5, 'available', 7500
FROM DUAL
WHERE @ph_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM inventory WHERE pharmacy_id = @ph_id LIMIT 1);

INSERT INTO subscriptions (user_id, plan_name, role_type, billing_cycle, price, status)
SELECT @ms_id, 'Enterprise', 'medical_services', 'yearly', 59000, 'active'
FROM DUAL
WHERE @ms_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM subscriptions WHERE user_id = @ms_id AND status = 'active' LIMIT 1);