// Function to focus on the table
function focusOnTable() {
    const tableContainer = document.querySelector('.table-container');
    if (tableContainer) {
        tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        tableContainer.classList.add('highlight');

        // Remove highlight after a delay
        setTimeout(() => {
            tableContainer.classList.remove('highlight');
        }, 1500);
    }
}

// Attach event listeners to search and pagination elements
document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.querySelector('form[method="GET"]');
    const paginationLinks = document.querySelectorAll('.pagination a');

    if (searchForm) {
        searchForm.addEventListener('submit', () => {
            focusOnTable();
        });
    }

    paginationLinks.forEach(link => {
        link.addEventListener('click', () => {
            focusOnTable();
        });
    });
});


document.addEventListener("DOMContentLoaded", function () {
    const logoutLink = document.getElementById("logoutLink");
    const logoutModal = document.getElementById("logoutModal");
    const confirmLogout = document.getElementById("confirmLogout");
    const cancelLogout = document.getElementById("cancelLogout");

    // Show modal on logout link click
    logoutLink.addEventListener("click", function(event) {
        event.preventDefault();
        logoutModal.style.display = "flex";
    });

    // Redirect to logout.php on confirmation
    confirmLogout.addEventListener("click", function() {
        logoutModal.style.display = "none"; // Hide modal before redirecting
        window.location.href = 'logout.php'; // This is where you trigger the PHP logout
    });

    // Hide modal on cancel button click
    cancelLogout.addEventListener("click", function() {
        logoutModal.style.display = "none";
    });

    // Hide modal when clicking outside
    window.addEventListener("click", function(event) {
        if (event.target === logoutModal) {
            logoutModal.style.display = "none";
        }
    });
});