<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'admin/admin_login.php');
}

$username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Please provide username and password.';
    redirect(BASE_URL . 'admin/admin_login.php');
}

try {
    // Try to find admin in database
    $stmt = $conn->prepare('SELECT * FROM admins WHERE username = :u LIMIT 1');
    $stmt->bindParam(':u', $username);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $admin = false;
}

// Accept both modern hashed passwords and legacy plaintext passwords.
// If a plaintext password is detected and matches, upgrade it to a secure hash.
if ($admin) {
    $stored = $admin['password'];
    $isPasswordOk = false;
    $needsRehash = false;

    if (!empty($stored) && password_verify($password, $stored)) {
        $isPasswordOk = true;
        $needsRehash = password_needs_rehash($stored, PASSWORD_DEFAULT);
    } elseif ($stored === $password) {
        // Legacy fallback: stored passwords were plaintext. Accept and rehash.
        $isPasswordOk = true;
        $needsRehash = true;
    }

    if ($isPasswordOk) {
        if ($needsRehash) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $u = $conn->prepare('UPDATE admins SET password = :ph WHERE id = :id');
                $u->execute([':ph' => $newHash, ':id' => $admin['id']]);
            } catch (Exception $e) {
                // ignore rehash errors
            }
        }

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        redirect(BASE_URL . 'admin/dashboard.php');
    }
}

$_SESSION['error'] = 'Invalid credentials.';
    redirect(BASE_URL . 'admin/admin_login.php');

?>
