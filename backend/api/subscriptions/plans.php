<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

$plans = [
    ['id' => 'pharmacy-free', 'role_type' => 'pharmacy', 'label' => 'صيدلاني — مجاني', 'price' => 0, 'billing_cycle' => 'monthly'],
    ['id' => 'pharmacy-pro', 'role_type' => 'pharmacy', 'label' => 'صيدلاني — Pro', 'price' => 3900, 'billing_cycle' => 'monthly'],
    ['id' => 'pharmacy-enterprise', 'role_type' => 'pharmacy', 'label' => 'صيدلاني — Enterprise', 'price' => 7000, 'billing_cycle' => 'monthly'],
    ['id' => 'medrep-free', 'role_type' => 'med_rep', 'label' => 'مندوب — مجاني', 'price' => 0, 'billing_cycle' => 'monthly'],
    ['id' => 'medrep-monthly', 'role_type' => 'med_rep', 'label' => 'مندوب — شهري', 'price' => 2500, 'billing_cycle' => 'monthly'],
    ['id' => 'medrep-yearly', 'role_type' => 'med_rep', 'label' => 'مندوب — سنوي', 'price' => 25000, 'billing_cycle' => 'yearly'],
    ['id' => 'lab-free', 'role_type' => 'lab', 'label' => 'مخبر — مجاني', 'price' => 0, 'billing_cycle' => 'monthly'],
    ['id' => 'lab-premium', 'role_type' => 'lab', 'label' => 'مخبر — Premium', 'price' => 4500, 'billing_cycle' => 'monthly'],
    ['id' => 'lab-enterprise', 'role_type' => 'lab', 'label' => 'مخبر — Enterprise', 'price' => 8500, 'billing_cycle' => 'monthly'],
    ['id' => 'medservices-free', 'role_type' => 'medical_services', 'label' => 'خدمات طبية — مجاني', 'price' => 0, 'billing_cycle' => 'monthly'],
    ['id' => 'medservices-pro', 'role_type' => 'medical_services', 'label' => 'خدمات طبية — Pro', 'price' => 4200, 'billing_cycle' => 'monthly'],
    ['id' => 'medservices-enterprise', 'role_type' => 'medical_services', 'label' => 'خدمات طبية — Enterprise', 'price' => 10500, 'billing_cycle' => 'monthly'],
];

sendSuccess(['plans' => $plans]);