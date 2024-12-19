<script>
let sidebar = document.querySelector(".sidebar");
let sidebarBtn = document.querySelector(".sidebarBtn");
sidebarBtn.onclick = function() {
  sidebar.classList.toggle("active");
  if(sidebar.classList.contains("active")){
  sidebarBtn.classList.replace("bx-menu" ,"bx-menu-alt-right");
}else
  sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
}

function setActiveClass(element) {
        var activeElements = document.querySelectorAll('.active');
        activeElements.forEach(function (el) {
            el.classList.remove('active');
        });
        element.classList.add('active');
    }
    document.getElementById('interactionStatus').addEventListener('change', function() {
            var resolvedFields = document.getElementById('resolved');
            var escalatedFields = document.getElementById('escalated');

            if (this.value === 'Resolved') {
                resolvedFields.classList.remove('hidden');
                escalatedFields.classList.add('hidden');
            } else if (this.value === 'Escalated') {
                escalatedFields.classList.remove('hidden');
                resolvedFields.classList.add('hidden');
            } else {
                resolvedFields.classList.add('hidden');
                escalatedFields.classList.add('hidden');
            }
        });
        document.getElementById('complaintCategory').addEventListener('change', function() {
            var kyc = document.getElementById('kyc-issues');
            var network = document.getElementById('network');
            var topup = document.getElementById('top-up');
            var others = document.getElementById('others');

            if (this.value === 'kyc-issues') {
                kyc.classList.remove('hidden');
                network.classList.add('hidden');
                topup.classList.add('hidden');
                others.classList.add('hidden');
            } else if (this.value === 'network-issues') {
                kyc.classList.add('hidden');
                network.classList.remove('hidden');
                topup.classList.add('hidden');
                others.classList.add('hidden');
            } else if (this.value === 'top-ups') {
                kyc.classList.add('hidden');
                network.classList.add('hidden');
                topup.classList.remove('hidden');
                others.classList.add('hidden');
            }
            else {
                kyc.classList.add('hidden');
                network.classList.add('hidden');
                topup.classList.add('hidden');
                others.classList.add('hidden');
            }
        });
        document.getElementById('search-focus').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#my-table tbody tr');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td, th');
                let rowText = '';

                cells.forEach(cell => {
                    rowText += cell.textContent.toLowerCase();
                });

                if (rowText.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        window.onload = function() {
    document.getElementById('search-focus').focus();
};

document.getElementById('search-focus').addEventListener('input', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('#my-table tbody tr');
    let visibleRowCount = 0;

    rows.forEach(row => {
        const cells = row.querySelectorAll('td, th');
        let rowText = '';

        cells.forEach(cell => {
            rowText += cell.textContent.toLowerCase();
        });

        if (rowText.includes(searchText)) {
            row.style.display = '';
            visibleRowCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Check if any rows are visible
    const noResultsRow = document.getElementById('no-results');
    if (visibleRowCount === 0) {
        if (!noResultsRow) {
            const tbody = document.querySelector('#my-table tbody');
            const noResults = document.createElement('tr');
            const noResultsCell = document.createElement('td');
            noResultsCell.colSpan = document.querySelectorAll('#my-table thead th').length;
            noResultsCell.textContent = 'Searched data not found';
            noResultsCell.style.textAlign = 'center';
            noResults.id = 'no-results';
            noResults.appendChild(noResultsCell);
            tbody.appendChild(noResults);
        } else {
            noResultsRow.style.display = '';
        }
    } else {
        if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }
});
</script>
