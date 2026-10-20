<?php
require_once 'db.php';

try {
    // Fetch all contacts from the database
    $stmt = $pdo->query("SELECT * FROM contacts");
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Address Book Contacts</title>
</head>
<body>
    <h1>Contacts Database</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address 1</th>
                <th>Address 2</th>
                <th>City</th>
                <th>State</th>
                <th>Zip Code</th>
                <th>Contact Group</th>
                <th>Notes</th>
                <th>Date Added</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($contacts)): ?>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?= htmlspecialchars($contact['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['first_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['last_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['phone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['address_1'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['address_2'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['city'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['state'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['zip_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['contact_group'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['notes'] ?? '') ?></td>
                        <td><?= htmlspecialchars($contact['date_added'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="13">No contacts found in the database.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
