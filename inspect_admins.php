<?php
require_once __DIR__ . '/../config/config.php';

// Simple, read-only inspector for the `admins` table.
// WARNING: Do not leave this file accessible on production systems.

try {
    $stmt = $conn->query('SELECT id, username, password FROM admins');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo '<h3>Error querying admins table:</h3>'; 
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    exit;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inspect Admins</title>
    <style>body{font-family:Arial,Helvetica,sans-serif;padding:18px;}table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:8px}th{background:#f4f4f4;text-align:left}</style>
</head>
<body>
    <h2>Admins table inspector</h2>
    <p>This page shows `admins` rows and a simple analysis of the stored password field.</p>
    <table>
        <thead><tr><th>ID</th><th>Username</th><th>Password Type</th><th>Matches `admin123`?</th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="4">No admin rows found.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $r): 
                $pw = $r['password'];
                $isHash = is_string($pw) && preg_match('/^\$2[ayb]\$|^\$argon2/', $pw);
                $isPlain = !$isHash;
                $matchesAdmin123 = false;
                if ($isHash) {
                    $matchesAdmin123 = password_verify('admin123', $pw);
                } else {
                    $matchesAdmin123 = ($pw === 'admin123');
                }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($r['id']); ?></td>
                <td><?php echo htmlspecialchars($r['username']); ?></td>
                <td><?php echo $isHash ? 'hashed' : 'plaintext'; ?></td>
                <td><?php echo $matchesAdmin123 ? 'YES' : 'no'; ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <p>After checking, delete this file for safety: <strong>admin/inspect_admins.php</strong></p>
</body>
</html>
