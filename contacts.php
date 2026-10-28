<?php
/**
 * contacts.php – Queries the database and returns the list of contacts
 * 
 * Simple PHP Implementation:
 * Renders HTML table rows for index.php.
 * 
 * Note: Where JavaScript and AJAX would go to dynamically fetch or refresh contacts.
 */

require_once 'db.php';

if (!function_exists('getInitials')) {
    function getInitials($first, $last): string
    {
        $f = !empty($first) ? substr(trim($first), 0, 1) : '';
        $l = !empty($last) ? substr(trim($last), 0, 1) : '';

        return strtoupper($f . $l) ?: '??';
    }
}

// Support search and group filtering
$search = trim($_GET['search'] ?? '');
$groupFilter = trim($_GET['group'] ?? '');

$sql = "SELECT * FROM contacts WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (first_name LIKE :s OR last_name LIKE :s OR email LIKE :s OR phone LIKE :s OR city LIKE :s OR notes LIKE :s)";
    $params[':s'] = "%$search%";
}

if ($groupFilter !== '' && in_array($groupFilter, ['Family', 'Friends', 'Business'], true)) {
    $sql .= " AND contact_group = :grp";
    $params[':grp'] = $groupFilter;
}

$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<tr><td colspan="8" class="empty-state-cell">';
    echo '<div class="empty-state">';
    echo '<div class="empty-icon empty-icon-error"><i class="fa-solid fa-triangle-exclamation"></i></div>';
    echo '<h3>Database Query Error</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div></td></tr>';
    exit;
}

// Return HTML table rows directly for server-side rendering
if (!empty($contacts)):
    foreach ($contacts as $contact):
        $id = (int) $contact['id'];
        $firstName = htmlspecialchars($contact['first_name'] ?? '');
        $lastName = htmlspecialchars($contact['last_name'] ?? '');
        $fullName = trim($firstName . ' ' . $lastName);
        $email = htmlspecialchars($contact['email'] ?? '');
        $phone = htmlspecialchars($contact['phone'] ?? '');
        $group = htmlspecialchars($contact['contact_group'] ?? 'Friends');
        $groupClass = 'badge-' . strtolower($contact['contact_group'] ?? 'friends');
        $notes = htmlspecialchars($contact['notes'] ?? '');
        $dateAdded = htmlspecialchars($contact['date_added'] ?? '');

        // Construct full address: Address 1, Address 2, City, State, Zip Code
        $addressLines = [];
        if (!empty($contact['address_1']))
            $addressLines[] = htmlspecialchars($contact['address_1']);
        if (!empty($contact['address_2']))
            $addressLines[] = htmlspecialchars($contact['address_2']);

        $cityStateZip = [];
        if (!empty($contact['city']))
            $cityStateZip[] = htmlspecialchars($contact['city']);
        if (!empty($contact['state']))
            $cityStateZip[] = htmlspecialchars($contact['state']);
        if (!empty($contact['zip_code']))
            $cityStateZip[] = htmlspecialchars($contact['zip_code']);
        if (!empty($cityStateZip))
            $addressLines[] = implode(', ', $cityStateZip);

        $fullAddress = !empty($addressLines) ? implode('<br>', $addressLines) : '<span class="text-subtle">No address provided</span>';
        $initials = getInitials($contact['first_name'] ?? '', $contact['last_name'] ?? '');
        ?>
        <tr id="contact-row-<?= $id ?>">
            <td><?= $id ?></td>
            <td>
                <div class="contact-name-cell">
                    <div class="avatar-badge"><?= $initials ?></div>
                    <div><strong><?= $fullName ?></strong></div>
                </div>
            </td>
            <td>
                <div class="contact-meta">
                    <span><i class="fa-solid fa-envelope"></i> <a href="mailto:<?= $email ?>"><?= $email ?></a></span>
                    <span><i class="fa-solid fa-phone"></i> <a href="tel:<?= $phone ?>"><?= $phone ?></a></span>
                </div>
            </td>
            <td>
                <div class="address-text"><?= $fullAddress ?></div>
            </td>
            <td>
                <span class="badge-group <?= $groupClass ?>"><?= $group ?></span>
            </td>
            <td>
                <div class="notes-preview" title="<?= $notes ?>"><?= $notes ?: '<span class="text-subtle">-</span>' ?></div>
            </td>
            <td>
                <span class="contact-date"><?= $dateAdded ?></span>
            </td>
            <td>
                <div class="table-actions">
                    <a href="edit_contact.php?id=<?= $id ?>" class="btn btn-sm btn-edit" title="Edit Contact">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>

                    <button type="button" class="btn btn-sm btn-delete" data-id="<?= $id ?>" title="Delete Contact">
                        <i class="fa-solid fa-trash-can"></i> Delete
                    </button>
                </div>
            </td>
        </tr>
        <?php
    endforeach;
else:
    ?>
    <tr id="emptyStateRow">
        <td colspan="8" class="empty-state-cell">
            <div class="empty-state">
                <div class="empty-icon"><i class="fa-solid fa-users-slash"></i></div>
                <h3>No contacts found in database</h3>
                <p>
                    <?= ($search !== '' || $groupFilter !== '') ? 'No contacts match your filter criteria. Try clearing your search.' : 'Click "Add Contact" above to insert your first record.' ?>
                </p>
                <!-- Where JavaScript and AJAX would go to trigger the Add Contact popup -->
                <a href="add_contact.php" class="btn btn-primary" id="btnEmptyAddContact">
                    <i class="fa-solid fa-plus"></i> Add New Contact
                </a>
            </div>
        </td>
    </tr>
    <?php
endif;
?>