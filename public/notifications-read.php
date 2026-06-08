<?php
require_once __DIR__ . '/../includes/functions.php';
require_recruiter();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    mark_notifications_read($pdo, current_user_id());
    header('Content-Type: application/json');
    echo json_encode(array('ok' => true));
    exit;
}
http_response_code(405);
