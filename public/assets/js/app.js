/**
 * SPK PIP — Global JavaScript
 * Hamburger menu toggle, alert auto-hide, sidebar interactions
 */
document.addEventListener('DOMContentLoaded', function () {
    // --- SIDEBAR TOGGLE ---
    const toggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // Close sidebar on pressing escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
        }
    });

    // --- AUTO HIDE ALERTS ---
    const alerts = document.querySelectorAll('.alert-dismissible, .alert:not(.alert-permanent)');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function () {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // --- TOOLTIPS INITIALIZATION ---
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});

/**
 * Reusable inline table search helper
 * @param {string} inputId - ID of search input field
 * @param {string} tableId - ID of table to search
 * @param {number} searchColIndex - index of column to search (0-indexed) or -1 for all
 */
function initTableSearch(inputId, tableId, searchColIndex = -1) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('keyup', function () {
        const filter = input.value.toLowerCase();
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) { // Skip header row
            let cells = rows[i].getElementsByTagName('td');
            if (cells.length === 0) continue;
            let match = false;

            if (searchColIndex >= 0 && searchColIndex < cells.length) {
                let cellValue = cells[searchColIndex].textContent || cells[searchColIndex].innerText;
                if (cellValue.toLowerCase().indexOf(filter) > -1) {
                    match = true;
                }
            } else {
                // Search all cells
                for (let j = 0; j < cells.length; j++) {
                    let cellValue = cells[j].textContent || cells[j].innerText;
                    if (cellValue.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }
            }

            rows[i].style.display = match ? '' : 'none';
        }
    });
}
