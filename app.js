// ==============================
// LOAD CONTACTS WITH AJAX
// ==============================

async function loadContacts(isUserInitiatedSearch = false) {
    const tableBody = document.getElementById("contactsTableBody");
    if (!tableBody) return;

    const searchInput = document.getElementById("searchInput");
    const groupFilter = document.getElementById("groupFilter");

    const searchVal = searchInput ? searchInput.value.trim() : "";
    const groupVal = groupFilter ? groupFilter.value.trim() : "";

    const params = new URLSearchParams();
    if (searchVal) params.append("search", searchVal);
    if (groupVal) params.append("group", groupVal);

    const queryStr = params.toString() ? `?${params.toString()}` : "";

    tableBody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align: center; padding: 2.5rem;">
                <i class="fa-solid fa-spinner fa-spin" style="margin-right: 0.5rem; color: var(--cyan);"></i>
                Loading contacts...
            </td>
        </tr>
    `;

    try {
        const response = await fetch(`contacts.php${queryStr}`);
        const html = await response.text();

        tableBody.innerHTML = html;

        // If user searched/filtered and no contacts match, throw error notification
        if (isUserInitiatedSearch && (searchVal || groupVal) && tableBody.querySelector("#emptyStateRow")) {
            alert("Contact not found.");
            showCopyToast("Contact not found.");
        }

    } catch (error) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="empty-state-cell">
                    <div class="empty-state">
                        <div class="empty-icon empty-icon-error"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <h3>Unable to load contacts</h3>
                        <p>There was an error communicating with the server.</p>
                    </div>
                </td>
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

        const totalEl = document.getElementById("statTotal");
        const familyEl = document.getElementById("statFamily");
        const friendsEl = document.getElementById("statFriends");
        const businessEl = document.getElementById("statBusiness");

        if (totalEl) totalEl.textContent = result.stats.total;
        if (familyEl) familyEl.textContent = result.stats.Family;
        if (friendsEl) friendsEl.textContent = result.stats.Friends;
        if (businessEl) businessEl.textContent = result.stats.Business;

    } catch (error) {
        console.error("Unable to load statistics.", error);
    }
}


// ==============================
// SEARCH & FILTER HANDLERS (ON-DEMAND)
// ==============================

function setupSearchAndFilters() {
    const searchInput = document.getElementById("searchInput");
    const groupFilter = document.getElementById("groupFilter");
    const searchForm = document.getElementById("searchFilterForm");

    // Submit search form (e.g. clicking 'Filter' button or pressing Enter)
    if (searchForm) {
        searchForm.addEventListener("submit", function (event) {
            event.preventDefault();
            loadContacts(true);
        });
    }

    // Changing the group filter dropdown triggers filter
    if (groupFilter) {
        groupFilter.addEventListener("change", function () {
            loadContacts(true);
        });
    }

    // Reset button handler clears inputs and reloads full list
    document.addEventListener("click", function (event) {
        if (event.target.closest("#btnResetFilter")) {
            event.preventDefault();
            if (searchInput) searchInput.value = "";
            if (groupFilter) groupFilter.value = "";
            loadContacts(false);
        }
    });
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
            showCopyToast(result.message);
            loadContacts();
            loadStats();
        } else {
            alert(result.message || "Failed to delete contact.");
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

                    <h2><i class="fa-solid fa-user-pen"></i> Edit Contact</h2>

                    <div id="editFormContainer"></div>
                </div>
            `;

            document.body.appendChild(dialog);
        }

        const container = document.getElementById("editFormContainer");
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
        const dialog = document.getElementById("editDialog");
        if (dialog) dialog.close();
    }
});


// ==============================
// SUBMIT EDIT FORM WITH AJAX
// ==============================

document.addEventListener("submit", async function (event) {
    const editForm = event.target.closest("#editDialog form");

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
            showCopyToast("Contact updated successfully!");

            const dialog = document.getElementById("editDialog");
            if (dialog) dialog.close();

            loadContacts();
            loadStats();

        } else {
            // Validation failed: Parse returned HTML and display the errors + form inside dialog
            const text = await response.text();
            const page = new DOMParser().parseFromString(text, "text/html");
            const newForm = page.querySelector("form");
            const errorAlert = page.querySelector(".alert-error");

            const container = document.getElementById("editFormContainer");
            if (container && newForm) {
                container.innerHTML = "";
                if (errorAlert) {
                    container.appendChild(errorAlert);
                }
                container.appendChild(newForm);
                showCopyToast("Please correct the form errors.");
            } else {
                alert("Unable to update contact. Please check the form.");
            }
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

                    <h2><i class="fa-solid fa-user-plus"></i> Add Contact</h2>

                    <div id="addFormContainer"></div>
                </div>
            `;

            document.body.appendChild(dialog);
        }

        const container = document.getElementById("addFormContainer");
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
        const dialog = document.getElementById("addDialog");
        if (dialog) dialog.close();
    }
});


// ==============================
// CLOSE DIALOGS ON CANCEL LINK CLICK
// ==============================

document.addEventListener("click", function (event) {
    const cancelLink = event.target.closest("#addDialog a[href*='index.php'], #editDialog a[href*='index.php']");
    if (cancelLink) {
        event.preventDefault();
        const dialog = cancelLink.closest("dialog");
        if (dialog) dialog.close();
    }
});


// ==============================
// SUBMIT ADD FORM WITH AJAX
// ==============================

document.addEventListener("submit", async function (event) {
    const addForm = event.target.closest("#addDialog form");

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
            showCopyToast("Contact added successfully!");

            const dialog = document.getElementById("addDialog");
            if (dialog) dialog.close();

            loadContacts();
            loadStats();

        } else {
            // Validation failed: Parse returned HTML and display the errors + form inside dialog
            const text = await response.text();
            const page = new DOMParser().parseFromString(text, "text/html");
            const newForm = page.querySelector("form");
            const errorAlert = page.querySelector(".alert-error");

            const container = document.getElementById("addFormContainer");
            if (container && newForm) {
                container.innerHTML = "";
                if (errorAlert) {
                    container.appendChild(errorAlert);
                }
                container.appendChild(newForm);
                showCopyToast("Please correct the form errors.");
            } else {
                alert("Unable to add contact. Please check the form.");
            }
        }

    } catch (error) {
        console.error(error);
        alert("Unable to add contact.");
    }
});


// ==============================
// CLICK TO COPY CONTACT INFO
// ==============================

document.addEventListener("click", async function (event) {
    const copyItem = event.target.closest(".copyable");

    if (!copyItem) {
        return;
    }

    const value = copyItem.dataset.copy;

    try {
        await navigator.clipboard.writeText(value);
        showCopyToast("Copied: " + value);

    } catch (error) {
        console.error("Unable to copy contact information.", error);
        showCopyToast("Unable to copy");
    }
});


// ==============================
// NOTIFICATION / TOAST POPUP
// ==============================

function showCopyToast(message) {
    let toast = document.getElementById("copyToast");

    if (!toast) {
        toast = document.createElement("div");
        toast.id = "copyToast";
        toast.className = "copy-toast";

        document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.classList.add("show");

    clearTimeout(window.copyToastTimer);

    window.copyToastTimer = setTimeout(function () {
        toast.classList.remove("show");
    }, 2400);
}


// ==============================
// RUN WHEN THE PAGE FIRST LOADS
// ==============================

document.addEventListener(
    "DOMContentLoaded",
    function () {
        setupSearchAndFilters();
        loadContacts();
        loadStats();
    }
);
