<?php
/**
 * add_contact.php – Add Contact Page
 * 
 * Simple PHP Implementation:
 * Standalone webpage containing the Add Contact form.
 * 
 * Note: Where JavaScript and AJAX would go for the Add Contact popup modal and asynchronous form submission.
 */

require_once 'db.php';

$us_states = [
    'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
    'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
    'DC' => 'District of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii',
    'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
    'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine',
    'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
    'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska',
    'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico',
    'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
    'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island',
    'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas',
    'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington',
    'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming'
];

$contact_groups = ['Family', 'Friends', 'Business'];

$errors = [];
$firstName = '';
$lastName  = '';
$email     = '';
$phone     = '';
$group     = 'Friends';
$address1  = '';
$address2  = '';
$city      = '';
$state     = '';
$zip       = '';
$notes     = '';

// Handle POST Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $group     = trim($_POST['contact_group'] ?? '');
    $address1  = trim($_POST['address_1'] ?? '') ?: null;
    $address2  = trim($_POST['address_2'] ?? '') ?: null;
    $city      = trim($_POST['city'] ?? '') ?: null;
    $state     = trim($_POST['state'] ?? '') ?: null;
    $zip       = trim($_POST['zip_code'] ?? '') ?: null;
    $notes     = trim($_POST['notes'] ?? '') ?: null;

    // Validation
    if ($firstName === '') {
        $errors[] = 'First name is required.';
    } elseif (mb_strlen($firstName) > 50) {
        $errors[] = 'First name cannot exceed 50 characters.';
    }

    if ($lastName === '') {
        $errors[] = 'Last name is required.';
    } elseif (mb_strlen($lastName) > 50) {
        $errors[] = 'Last name cannot exceed 50 characters.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    } elseif (mb_strlen($email) > 100) {
        $errors[] = 'Email cannot exceed 100 characters.';
    }

    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    } elseif (mb_strlen($phone) > 20) {
        $errors[] = 'Phone cannot exceed 20 characters.';
    }

    if ($group === '' || !in_array($group, $contact_groups, true)) {
        $errors[] = 'Please select a valid contact group.';
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO contacts (
                        first_name, last_name, email, phone,
                        address_1, address_2, city, state, zip_code,
                        contact_group, notes
                    ) VALUES (
                        :first_name, :last_name, :email, :phone,
                        :address_1, :address_2, :city, :state, :zip_code,
                        :contact_group, :notes
                    )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':first_name'    => $firstName,
                ':last_name'     => $lastName,
                ':email'         => $email,
                ':phone'         => $phone,
                ':address_1'     => $address1,
                ':address_2'     => $address2,
                ':city'          => $city,
                ':state'         => $state,
                ':zip_code'      => $zip,
                ':contact_group' => $group,
                ':notes'         => $notes
            ]);

            // Redirect back to main page with success message
            header('Location: index.php?msg=' . urlencode('Contact added successfully!'));
            exit;

        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Contact - Address Book</title>

    <!-- Font Awesome 6.5.1 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- External Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container" style="max-width: 800px;">
        <!-- Page Header -->
        <header class="page-header">
            <div class="header-title-area">
                <h1><i class="fa-solid fa-user-plus"></i> Add New Contact</h1>
                <p>Fill out the form below to add a new contact to your address book.</p>
            </div>
            <div class="header-actions">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Contacts
                </a>
            </div>
        </header>

        <!-- Form Card -->
        <main class="table-card" style="padding: 2rem;">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <strong>Please correct the following errors:</strong>
                        <ul style="margin-top: 0.5rem; margin-left: 1.25rem;">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Where JavaScript and AJAX would go to handle the popup form submission -->
            <form method="POST" action="add_contact.php">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required maxlength="50"
                            placeholder="e.g. Jane" value="<?= htmlspecialchars($firstName) ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required maxlength="50"
                            placeholder="e.g. Doe" value="<?= htmlspecialchars($lastName) ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required maxlength="100"
                            placeholder="e.g. jane.doe@example.com" value="<?= htmlspecialchars($email) ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" required maxlength="20"
                            placeholder="e.g. (337) 555-0199" value="<?= htmlspecialchars($phone) ?>">
                    </div>

                    <div class="form-group">
                        <label for="contact_group">Contact Group <span class="required">*</span></label>
                        <select class="form-control" id="contact_group" name="contact_group" required>
                            <option value="">-- Select Group --</option>
                            <?php foreach ($contact_groups as $g): ?>
                                <option value="<?= htmlspecialchars($g) ?>" <?= ($group === $g) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="state">State</label>
                        <select class="form-control" id="state" name="state">
                            <option value="">-- Select State --</option>
                            <?php foreach ($us_states as $abbr => $stateName): ?>
                                <option value="<?= htmlspecialchars($abbr) ?>" <?= ($state === $abbr) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($abbr) ?> - <?= htmlspecialchars($stateName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-full">
                        <label for="address_1">Address Line 1</label>
                        <input type="text" class="form-control" id="address_1" name="address_1" maxlength="100"
                            placeholder="e.g. 104 University Circle" value="<?= htmlspecialchars($address1 ?? '') ?>">
                    </div>

                    <div class="form-group col-full">
                        <label for="address_2">Address Line 2</label>
                        <input type="text" class="form-control" id="address_2" name="address_2" maxlength="100"
                            placeholder="e.g. Apt 4B, Suite 200" value="<?= htmlspecialchars($address2 ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" class="form-control" id="city" name="city" maxlength="50"
                            placeholder="e.g. Lafayette" value="<?= htmlspecialchars($city ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="zip_code">Zip Code</label>
                        <input type="text" class="form-control" id="zip_code" name="zip_code" maxlength="20"
                            placeholder="e.g. 70504" value="<?= htmlspecialchars($zip ?? '') ?>">
                    </div>

                    <div class="form-group col-full">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                            placeholder="Additional details or preferences..."><?= htmlspecialchars($notes ?? '') ?></textarea>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Contact
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>

</html>
