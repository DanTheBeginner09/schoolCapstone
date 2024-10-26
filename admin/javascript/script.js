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
