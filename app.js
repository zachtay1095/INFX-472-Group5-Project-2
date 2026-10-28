// ==============================
// LOAD CONTACTS WITH AJAX
// ==============================

async function loadContacts() {
    const tableBody = document.getElementById("contactsTableBody");

    tableBody.innerHTML = `
        <tr>
            <td colspan="8">Loading contacts...</td>
        </tr>
    `;

    try {
        const response = await fetch("contacts.php");
        const html = await response.text();

        tableBody.innerHTML = html;

    } catch (error) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8">Unable to load contacts.</td>
            </tr>
        `;

        console.error("Unable to load contacts.", error);
    }
}


// ==============================
// LOAD CONTACT STATISTICS
// ==============================

async function loadStats() {
    try {
        const response = await fetch("stats.php");
        const result = await response.json();

        if (!result.success) {
            return;
        }

        document.getElementById("statTotal").textContent =
            result.stats.total;

        document.getElementById("statFamily").textContent =
            result.stats.Family;

        document.getElementById("statFriends").textContent =
            result.stats.Friends;

        document.getElementById("statBusiness").textContent =
            result.stats.Business;

    } catch (error) {
        console.error("Unable to load statistics.", error);
    }
}


// ==============================
// DELETE CONTACT WITH AJAX
// ==============================

document.addEventListener("click", async function (event) {
    const deleteButton = event.target.closest(".btn-delete");

    if (!deleteButton) {
        return;
    }

    const contactId = deleteButton.dataset.id;

    const confirmed = confirm(
        "Are you sure you want to delete this contact?"
    );

    if (!confirmed) {
        return;
    }

    const formData = new FormData();

    formData.append("id", contactId);

    try {
        const response = await fetch("delete_contact.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);

            loadContacts();
            loadStats();

        } else {
            alert(result.message);
        }

    } catch (error) {
        console.error(error);

        alert("Unable to delete contact.");
    }
});


// ==============================
// OPEN EDIT CONTACT POPUP
// ==============================

document.addEventListener("click", async function (event) {
    const editButton = event.target.closest(".btn-edit");

    if (!editButton) {
        return;
    }

    // Prevent browser from opening edit_contact.php normally
    event.preventDefault();

    try {
        const response = await fetch(editButton.href);
        const html = await response.text();

        const page = new DOMParser().parseFromString(
            html,
            "text/html"
        );

        const editForm = page.querySelector("form");

        if (!editForm) {
            alert("Unable to load the edit form.");
            return;
        }

        let dialog = document.getElementById("editDialog");

        if (!dialog) {
            dialog = document.createElement("dialog");

            dialog.id = "editDialog";

            dialog.innerHTML = `
                <div>
                    <button
                        type="button"
                        id="closeEditDialog"
                    >
                        Close
                    </button>

                    <h2>Edit Contact</h2>

                    <div id="editFormContainer"></div>
                </div>
            `;

            document.body.appendChild(dialog);
        }

        const container =
            document.getElementById("editFormContainer");

        container.innerHTML = "";

        container.appendChild(editForm);

        dialog.showModal();

    } catch (error) {
        console.error(error);

        alert("Unable to load contact information.");
    }
});


// ==============================
// CLOSE EDIT POPUP
// ==============================

document.addEventListener("click", function (event) {
    if (event.target.id === "closeEditDialog") {
        const dialog =
            document.getElementById("editDialog");

        dialog.close();
    }
});


// ==============================
// SUBMIT EDIT FORM WITH AJAX
// ==============================

document.addEventListener("submit", async function (event) {
    const editForm =
        event.target.closest("#editDialog form");

    if (!editForm) {
        return;
    }

    event.preventDefault();

    const formData = new FormData(editForm);

    try {
        const response = await fetch(
            "edit_contact.php",
            {
                method: "POST",
                body: formData
            }
        );

        /*
         * edit_contact.php redirects to index.php
         * when the update succeeds.
         */

        if (response.redirected) {
            alert("Contact updated successfully!");

            document
                .getElementById("editDialog")
                .close();

            loadContacts();
            loadStats();

        } else {
            alert(
                "Unable to update contact. Please check the form."
            );
        }

    } catch (error) {
        console.error(error);

        alert("Unable to update contact.");
    }
});


// ==============================
// OPEN ADD CONTACT POPUP
// ==============================

document.addEventListener("click", async function (event) {
    const addButton = event.target.closest(
        "#btnAddContact, #btnEmptyAddContact"
    );

    if (!addButton) {
        return;
    }

    event.preventDefault();

    try {
        const response = await fetch("add_contact.php");
        const html = await response.text();

        const page = new DOMParser().parseFromString(
            html,
            "text/html"
        );

        const addForm = page.querySelector("form");

        if (!addForm) {
            alert("Unable to load the Add Contact form.");
            return;
        }

        let dialog = document.getElementById("addDialog");

        if (!dialog) {
            dialog = document.createElement("dialog");

            dialog.id = "addDialog";

            dialog.innerHTML = `
                <div>
                    <button
                        type="button"
                        id="closeAddDialog"
                    >
                        Close
                    </button>

                    <h2>Add Contact</h2>

                    <div id="addFormContainer"></div>
                </div>
            `;

            document.body.appendChild(dialog);
        }

        const container =
            document.getElementById("addFormContainer");

        container.innerHTML = "";

        container.appendChild(addForm);

        dialog.showModal();

    } catch (error) {
        console.error(error);

        alert("Unable to load the Add Contact form.");
    }
});


// ==============================
// CLOSE ADD POPUP
// ==============================

document.addEventListener("click", function (event) {
    if (event.target.id === "closeAddDialog") {
        const dialog =
            document.getElementById("addDialog");

        dialog.close();
    }
});


// ==============================
// SUBMIT ADD FORM WITH AJAX
// ==============================

document.addEventListener("submit", async function (event) {
    const addForm =
        event.target.closest("#addDialog form");

    if (!addForm) {
        return;
    }

    event.preventDefault();

    const formData = new FormData(addForm);

    try {
        const response = await fetch(
            "add_contact.php",
            {
                method: "POST",
                body: formData
            }
        );

        /*
         * add_contact.php redirects to index.php
         * when the insert succeeds.
         */

        if (response.redirected) {
            alert("Contact added successfully!");

            document
                .getElementById("addDialog")
                .close();

            loadContacts();
            loadStats();

        } else {
            alert(
                "Unable to add contact. Please check the form."
            );
        }

    } catch (error) {
        console.error(error);

        alert("Unable to add contact.");
    }
});


// ==============================
// RUN WHEN THE PAGE FIRST LOADS
// ==============================

document.addEventListener(
    "DOMContentLoaded",
    function () {
        loadContacts();
        loadStats();
    }
);