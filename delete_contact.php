<?php

require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid contact ID.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "DELETE FROM contacts WHERE id = :id"
    );

    $stmt->execute([
        ':id' => $id
    ]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Contact not found.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Contact deleted successfully!'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error.'
    ]);
}