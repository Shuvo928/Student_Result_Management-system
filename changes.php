<?php
// Returns JSON with simple counters and latest result timestamp to detect changes
require_once __DIR__ . '/../config/config.php';
if (!isAdminLoggedIn()) { http_response_code(403); echo json_encode(['error'=>'unauthorized']); exit; }

try {
    $s = $conn->query('SELECT COUNT(*) FROM students'); $students_count = (int)$s->fetchColumn();
    $t = $conn->query('SELECT COUNT(*) FROM teachers'); $teachers_count = (int)$t->fetchColumn();
    $r = $conn->query('SELECT COUNT(*) FROM results'); $results_count = (int)$r->fetchColumn();
    $m = $conn->query("SELECT MAX(created_at) FROM results"); $results_max = $m->fetchColumn();
} catch (Exception $e) {
    http_response_code(500); echo json_encode(['error'=>'query']); exit;
}

header('Content-Type: application/json');
echo json_encode([
    'students_count' => $students_count,
    'teachers_count' => $teachers_count,
    'results_count' => $results_count,
    'results_max' => $results_max,
]);
exit;
