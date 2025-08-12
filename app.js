document.addEventListener('DOMContentLoaded', function() {

    const searchInput = document.getElementById('search');
    const spinner = document.getElementById('loading-spinner');
    const ipoTableBody = document.getElementById('ipo-table-body');
    const ipoTabs = document.getElementById('ipo-tabs');
    let ipos = [];
    let apiIpos = { upcoming: [], closed: [] };
    let currentTab = 'all';

    function renderTabs() {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === currentTab);
        });
    }

    function renderTable(filter = '') {
        ipoTableBody.innerHTML = '';
        // Merge local and API IPOs for 'all' and 'open' tabs
        let allIpos = [...ipos];
        if (currentTab === 'upcoming' && apiIpos.upcoming.length) {
            allIpos = allIpos.concat(apiIpos.upcoming);
        } else if (currentTab === 'closed' && apiIpos.closed.length) {
            allIpos = allIpos.concat(apiIpos.closed);
        } else if (currentTab === 'all') {
            allIpos = allIpos.concat(apiIpos.upcoming, apiIpos.closed);
        }
        // Filter by tab
        let filtered = allIpos.filter(ipo => {
            let status = (ipo.status || ipo.ipoStatus || '').toLowerCase();
            if (currentTab === 'all') return true;
            if (currentTab === 'open') return status === 'live' || status === 'open';
            if (currentTab === 'upcoming') return status === 'upcoming';
            if (currentTab === 'closed') return status === 'closed';
            return true;
        });
        // Filter by search
        if (filter) {
            filtered = filtered.filter(ipo => (ipo.name || ipo.companyName || '').toLowerCase().includes(filter.toLowerCase()));
        }
        if (filtered.length === 0) {
            ipoTableBody.innerHTML = '<tr><td colspan="9" style="text-align:center;">No IPOs found.</td></tr>';
            return;
        }
        filtered.forEach(ipo => {
            const name = ipo.name || ipo.companyName || ipo.ipoName || 'N/A';
            const premium = ipo.premium || ipo.gmp || '-';
            const open = ipo.openingDate || ipo.open_date || ipo.openDate || ipo.open || '-';
            const close = ipo.closingDate || ipo.close_date || ipo.closeDate || ipo.close || '-';
            const price = ipo.priceBand || ipo.price || ipo.priceband || ipo.priceBandLower || ipo.priceLower || '-';
            const lot = ipo.lotSize || ipo.lot_size || ipo.lotsize || '-';
            const allot = ipo.allotmentDate || ipo.allotment_date || ipo.allotment || '-';
            const listing = ipo.listingDate || ipo.listing_date || ipo.listing || '-';
            const ipoId = ipo.id || ipo.ipoId || ipo.ipo_id || '';
            ipoTableBody.innerHTML += `<tr class="ipo-row" style="cursor:pointer;" data-id="${ipoId}">
                <td>${name}</td>
                <td>${premium}</td>
                <td>${open}</td>
                <td>${close}</td>
                <td>₹${price}</td>
                <td>${lot}</td>
                <td>${allot}</td>
                <td>${listing}</td>
                <td><span class="ipo-details-link">View</span></td>
            </tr>`;
    // Make each row clickable to open IPO details
    ipoTableBody.onclick = function(e) {
        let tr = e.target.closest('tr.ipo-row');
        if (tr && tr.dataset.id) {
            window.location.href = `ipo.html?id=${tr.dataset.id}`;
        }
    };
        });
    }

    function showSpinner(show) {
        if (spinner) spinner.style.display = show ? 'block' : 'none';
    }

    showSpinner(true);
    fetch('get_ipos.php')
        .then(response => response.json())
        .then(data => {
            ipos = data;
            Promise.all([
                fetch('fetch_ipo_api.php?type=upcoming').then(r => r.json()),
                fetch('fetch_ipo_api.php?type=closed').then(r => r.json())
            ]).then(([upcoming, closed]) => {
                apiIpos.upcoming = Array.isArray(upcoming) ? upcoming : (upcoming.data || []);
                apiIpos.closed = Array.isArray(closed) ? closed : (closed.data || []);
                renderTabs();
                renderTable(searchInput.value);
                showSpinner(false);
            }).catch(() => {
                renderTabs();
                renderTable(searchInput.value);
                showSpinner(false);
            });
        })
        .catch(() => {
            ipoTableBody.innerHTML = '<tr><td colspan="9" style="color:red;text-align:center;">Error loading IPO data. Please try again later.</td></tr>';
            showSpinner(false);
        });

    ipoTabs.addEventListener('click', function(e) {
        if (e.target.classList.contains('tab-btn')) {
            currentTab = e.target.dataset.tab;
            renderTabs();
            renderTable(searchInput.value);
        }
    });

    searchInput.addEventListener('input', function() {
        renderTable(this.value);
    });
});
