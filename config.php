<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'database.php';

$database = new Database();
$conn = $database->getConnection();

// Base URL configuration
define('BASE_URL', 'http://localhost/result_management/');

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Check if teacher is logged in
function isTeacherLoggedIn() {
    return isset($_SESSION['teacher_id']);
}

// Check if student is logged in
function isStudentLoggedIn() {
    return isset($_SESSION['student_id']);
}

// Redirect function: resolves relative paths against current script directory
function redirect($url) {
    // If the URL is absolute or starts with a slash, use it as-is.
    if (preg_match('#^https?://#i', $url) || strpos($url, '/') === 0) {
        header('Location: ' . $url);
        exit();
    }

    $parsed = parse_url(BASE_URL);
    $scheme = isset($parsed['scheme']) ? $parsed['scheme'] : 'http';
    $host = isset($parsed['host']) ? $parsed['host'] : $_SERVER['HTTP_HOST'];
    $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
    $origin = $scheme . '://' . $host . $port;

    // Use the directory of the current request as the base for relative URLs
    $currentDir = dirname($_SERVER['PHP_SELF']);
    $path = rtrim($currentDir, '/') . '/' . $url;

    // Normalize path segments (resolve . and ..)
    $segments = explode('/', $path);
    $resolved = [];
    foreach ($segments as $seg) {
        if ($seg === '' || $seg === '.') continue;
        if ($seg === '..') {
            array_pop($resolved);
        } else {
            $resolved[] = $seg;
        }
    }
    $normalizedPath = '/' . implode('/', $resolved);

    $u = $origin . $normalizedPath;

    header('Location: ' . $u);
    exit();
}

// Sanitize input
function sanitize($input) {
    global $conn;
    return htmlspecialchars(strip_tags($input));
}
?>