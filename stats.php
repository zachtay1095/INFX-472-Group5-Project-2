<?php

require_once 'db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT contact_group, COUNT(*) AS count
        FROM contacts
        GROUP BY contact_group
    ");

    $rows = $stmt->fetchAll();

    $stats = [
        'total' => 0,
        'Family' => 0,
        'Friends' => 0,
        'Business' => 0
    ];

    foreach ($rows as $row) {
        $group = $row['contact_group'];
        $count = (int) $row['count'];

        $stats[$group] = $count;
        $stats['total'] += $count;
    }

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load contact statistics.'
    ]);
}