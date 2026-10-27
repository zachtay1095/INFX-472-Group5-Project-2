<?php
/**
 * delete_contact.php – Deletes a contact
 * 
 * Simple PHP Implementation:
 * Receives the contact ID via GET or POST, deletes the record from MySQL,
 * and redirects back to index.php with a notification message.
 * 
 * Note: Where JavaScript and AJAX would go for the Delete Contact confirmation popup.
 */

require_once 'db.php';

// Retrieve ID from GET (direct link) or POST (form submission)
$id = filter_var($_GET['id'] ?? ($_POST['id'] ?? null), FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: index.php?msg=' . urlencode('Invalid contact ID provided.') . '&status=error');
    exit;
}

try {
    // Check if record exists
    $checkStmt = $pdo->prepare("SELECT id FROM contacts WHERE id = :id");
    $checkStmt->execute([':id' => $id]);
    if (!$checkStmt->fetch()) {
        header('Location: index.php?msg=' . urlencode('Contact not found or already deleted.') . '&status=error');
        exit;
    }

    // Execute deletion
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: index.php?msg=' . urlencode('Contact deleted successfully!'));
    exit;

} catch (PDOException $e) {
    header('Location: index.php?msg=' . urlencode('Database error: ' . $e->getMessage()) . '&status=error');
    exit;
}
