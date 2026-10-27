<?php
/**
 * index.php – The main page; displays the contact list and controls
 * 
 * Simple PHP Implementation:
 * - Standalone multi-page setup.
 * - "Add Contact" links directly to add_contact.php.
 * - "Edit" links directly to edit_contact.php?id=X.
 * - "Delete" links directly to delete_contact.php?id=X.
 */

require_once 'db.php';

$contact_groups = ['Family', 'Friends', 'Business'];

// Handle notification messages passed via query parameters
$flashMessage = $_GET['msg'] ?? '';
$flashStatus = $_GET['status'] ?? 'success';

// Search and filter parameters
$searchQuery = trim($_GET['search'] ?? '');
$selectedGroup = trim($_GET['group'] ?? '');

// Calculate group counts directly in PHP
try {
    $statsStmt = $pdo->query("SELECT contact_group, COUNT(*) as count FROM contacts GROUP BY contact_group");
    $groupCounts = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $statTotal = array_sum($groupCounts);
    $statFamily = $groupCounts['Family'] ?? 0;
    $statFriends = $groupCounts['Friends'] ?? 0;
    $statBusiness = $groupCounts['Business'] ?? 0;
} catch (PDOException $e) {
    $statTotal = $statFamily = $statFriends = $statBusiness = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Address Book & Contact Management</title>

    <!-- Font Awesome 6.5.1 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- External Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <!-- Notification Message Banner -->
        <?php if (!empty($flashMessage)): ?>
            <div class="alert <?= ($flashStatus === 'error') ? 'alert-error' : 'alert-success' ?>">
                <i class="fa-solid <?= ($flashStatus === 'error') ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>
                <div><?= htmlspecialchars($flashMessage) ?></div>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <header class="page-header">
            <div class="header-title-area">
                <h1><i class="fa-solid fa-address-book"></i> Contacts Directory</h1>
                <p>Manage your address book using simple PHP and MySQL.</p>
            </div>
            <div class="header-actions">
                <!-- Where JavaScript and AJAX would go to trigger the Add Contact popup -->
                <a href="add_contact.php" class="btn btn-primary" id="btnAddContact">
                    <i class="fa-solid fa-plus"></i> Add Contact
                </a>
            </div>
        </header>

        <!-- Controls Bar: Group Counts, Search & Filter -->
        <section class="controls-bar">
            <div class="stats-badges">
                <div class="stat-pill"><span>Total:</span> <strong id="statTotal"><?= $statTotal ?></strong></div>
                <div class="stat-pill"><span>Family:</span> <strong id="statFamily"><?= $statFamily ?></strong></div>
                <div class="stat-pill"><span>Friends:</span> <strong id="statFriends"><?= $statFriends ?></strong></div>
                <div class="stat-pill"><span>Business:</span> <strong id="statBusiness"><?= $statBusiness ?></strong>
                </div>
            </div>

            <!-- Where JavaScript and AJAX would go for live search and filtering -->
            <form method="GET" action="index.php" class="search-filter-group">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" id="searchInput" placeholder="Search contacts..."
                        value="<?= htmlspecialchars($searchQuery) ?>" aria-label="Search contacts">
                </div>
                <select name="group" id="groupFilter" class="filter-select" aria-label="Filter by group">
                    <option value="">All Groups</option>
                    <?php foreach ($contact_groups as $group): ?>
                        <option value="<?= htmlspecialchars($group) ?>" <?= ($selectedGroup === $group) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($group) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm" title="Apply Filter">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <?php if ($searchQuery !== '' || $selectedGroup !== ''): ?>
                    <a href="index.php" class="btn btn-secondary btn-sm" title="Clear Filter">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                <?php endif; ?>
            </form>
        </section>

        <!-- Contacts Table Card -->
        <main class="table-card">
            <div class="table-responsive">
                <table class="contacts-table" id="contactsTable">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th>Contact Name</th>
                            <th>Contact Info</th>
                            <th>Address</th>
                            <th>Group</th>
                            <th>Notes</th>
                            <th>Date Added</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>

                    <!-- Where AJAX would go to dynamically load the contacts table body -->
                    <tbody id="contactsTableBody">
                        <?php include 'contacts.php'; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Where JavaScript and AJAX would go for the popup webpages -->
</body>

</html>