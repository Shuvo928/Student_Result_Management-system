<?php
require_once __DIR__ . '/../config/config.php';

try {
    // Some schemas may not have a `created_at` column; order by `student_id` instead.
    $stmt = $conn->query('SELECT * FROM students ORDER BY student_id DESC LIMIT 100');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo '<h3>Error querying students table:</h3>'; 
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    exit;
}

?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Inspect Students</title>
<style>body{font-family:Arial,Helvetica,sans-serif;padding:18px}table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:6px}th{background:#f4f4f4}</style>
</head>
<body>
<h2>Students table inspector</h2>
<p>This page lists rows from the `students` table. Remove after use.</p>
<table>
<thead><tr>
<?php if (!empty($rows)): foreach (array_keys($rows[0]) as $col): ?>
<th><?php echo htmlspecialchars($col); ?></th>
<?php endforeach; else: ?>
<th>No rows</th>
<?php endif; ?>
</tr></thead>
<tbody>
<?php foreach($rows as $r): ?><tr><?php foreach($r as $c): ?><td><?php echo htmlspecialchars((string)$c); ?></td><?php endforeach; ?></tr><?php endforeach; ?>
</tbody>
</table>
</body>
</html>
