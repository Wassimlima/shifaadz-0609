<?php

function applyCorsHeaders(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if ($origin !== '' && isOriginAllowed($origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
}

function isOriginAllowed(string $origin): bool
{
    $parsed = parse_url($origin);

    if (!$parsed || empty($parsed['host'])) {
        return false;
    }

    $host = strtolower($parsed['host']);

    if (in_array($host, ['localhost', '127.0.0.1'], true)) {
        return true;
    }

    return $host === strtolower($_SERVER['HTTP_HOST'] ?? '');
}

/** @deprecated Use applyCorsHeaders() — kept for existing includes */
function setCorsHeaders(): void
{
    applyCorsHeaders();
}

applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}