<?php

function sendSuccess($data, $status = 200)
{
    sendJSON($data, $status);
}

function sendJSON($data, $status = 200)
{
    http_response_code($status);

    header(
        'Content-Type: application/json; charset=utf-8'
    );

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}

function sendError(
    $message,
    $status = 400
) {

    sendJSON([
        'error' => $message
    ], $status);
}

function getBody()
{
    $raw = file_get_contents(
        'php://input'
    );

    return json_decode(
        $raw,
        true
    ) ?? [];
}