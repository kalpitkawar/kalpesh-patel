// Dark mode toggle
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('dark-mode-toggle');
    if (btn) {
        btn.onclick = function() {
            document.documentElement.classList.toggle('dark-mode');
            btn.textContent = document.documentElement.classList.contains('dark-mode') ? '☀️ Light Mode' : '🌙 Dark Mode';
        };
    }
});
// Export data logic
document.addEventListener('DOMContentLoaded', function() {
    const exportDematBtn = document.getElementById('export-demat-btn');
    const exportIpoAppBtn = document.getElementById('export-ipo-app-btn');
    function downloadCSV(filename, rows) {
        const process = v => '"' + (String(v).replace(/"/g, '""')) + '"';
        const csv = rows.map(r => r.map(process).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    if (exportDematBtn) {
        exportDematBtn.onclick = function() {
            fetch('../backend/user_demat.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.accounts.length) {
                        const rows = [
                            ['Account Number', 'Depository', 'Holder Name']
                        ];
                        data.accounts.forEach(acc => {
                            rows.push([acc.account_number, acc.depository, acc.holder_name || '']);
                        });
                        downloadCSV('demat_accounts.csv', rows);
                    }
                });
        };
    }
    if (exportIpoAppBtn) {
        exportIpoAppBtn.onclick = function() {
            fetch('../backend/user_ipo_applications.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.applications.length) {
                        const rows = [
                            ['IPO Name', 'Account Number', 'Lots', 'Date', 'Status', 'Listing Gain']
                        ];
                        data.applications.forEach(app => {
                            rows.push([app.ipo_name, app.account_number, app.applied_lots, app.application_date, app.status, app.listing_gain || '']);
                        });
                        downloadCSV('ipo_applications.csv', rows);
                    }
                });
        };
    }
});
// Notifications management
document.addEventListener('DOMContentLoaded', function() {
    const notesList = document.getElementById('notifications-list');
    const notesMsg = document.getElementById('notifications-msg');
    function loadNotifications() {
        fetch('../backend/user_notifications.php')
            .then(res => res.json())
            .then(data => {
                notesList.innerHTML = '';
                if (data.success && data.notifications.length) {
                    data.notifications.forEach(note => {
                        const li = document.createElement('li');
                        li.innerHTML = `<span style='${note.is_read ? '' : 'font-weight:bold;'}'>${note.message}</span> <small style='color:#888;'>${note.created_at}</small> ${!note.is_read ? '<button data-id="'+note.id+'" class="mark-read-btn">Mark as read</button>' : ''}`;
                        notesList.appendChild(li);
                    });
                } else {
                    notesList.innerHTML = '<li>No notifications.</li>';
                }
            });
    }
    if (notesList) {
        loadNotifications();
        notesList.onclick = function(e) {
            if (e.target.classList.contains('mark-read-btn')) {
                const id = e.target.getAttribute('data-id');
                fetch('../backend/user_notifications.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) loadNotifications();
                });
            }
        };
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profile-form');
    const msg = document.getElementById('profile-msg');
    // Load current profile
    fetch('../backend/user_profile.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                msg.textContent = data.error || 'Not logged in.';
                msg.style.color = 'red';
                form.style.display = 'none';
                return;
            }
            document.getElementById('email').value = data.email;
            document.getElementById('mobile').value = data.mobile;
            document.getElementById('email_alerts').checked = !!data.email_alerts;
        });
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        msg.textContent = 'Updating...';
        const email = document.getElementById('email').value;
        const mobile = document.getElementById('mobile').value;
        const password = document.getElementById('password').value;
        const email_alerts = document.getElementById('email_alerts').checked ? 1 : 0;
        fetch('../backend/user_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, mobile, password, email_alerts })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                msg.style.color = 'green';
                msg.textContent = 'Profile updated!';
                form.reset();
            } else {
                msg.style.color = 'red';
                msg.textContent = data.error || 'Update failed.';
            }
        })
        .catch(() => {
            msg.style.color = 'red';
            msg.textContent = 'Server error.';
        });
    });
});
function logout() {
    fetch('../backend/user_logout.php').then(() => window.location.href = 'login.html');
}

// IPO Application management
document.addEventListener('DOMContentLoaded', function() {
    const ipoAppForm = document.getElementById('ipo-app-form');
    const ipoAppList = document.getElementById('ipo-app-list');
    const ipoAppMsg = document.getElementById('ipo-app-msg');
    const ipoSelect = document.getElementById('ipo_app_ipo_id');
    const dematSelect = document.getElementById('ipo_app_demat_id');
    const lotsInput = document.getElementById('ipo_app_lots');
    const dateInput = document.getElementById('ipo_app_date');
    function loadIposAndDemats() {
        // Load IPOs
        fetch('../backend/get_ipos.php')
            .then(res => res.json())
            .then(data => {
                ipoSelect.innerHTML = '';
                data.forEach(ipo => {
                    const opt = document.createElement('option');
                    opt.value = ipo.id;
                    opt.textContent = `${ipo.name} (${ipo.status})`;
                    ipoSelect.appendChild(opt);
                });
            });
        // Load demat accounts
        fetch('../backend/user_demat.php')
            .then(res => res.json())
            .then(data => {
                dematSelect.innerHTML = '';
                if (data.success && data.accounts.length) {
                    data.accounts.forEach(acc => {
                        const opt = document.createElement('option');
                        opt.value = acc.id;
                        opt.textContent = `${acc.account_number} (${acc.depository})`;
                        dematSelect.appendChild(opt);
                    });
                }
            });
    }
    function loadIpoApplications() {
        fetch('../backend/user_ipo_applications.php')
            .then(res => res.json())
            .then(data => {
                ipoAppList.innerHTML = '';
                if (data.success && data.applications.length) {
                    data.applications.forEach(app => {
                        const li = document.createElement('li');
                        li.innerHTML = `<b>${app.ipo_name}</b> (${app.account_number}) - Lots: ${app.applied_lots}, Date: ${app.application_date}, Status: <b>${app.status}</b> ${app.listing_gain ? ', Gain: ₹' + app.listing_gain : ''} <button data-id='${app.id}' class='delete-ipo-app-btn' style='margin-left:10px;'>Delete</button>`;
                        ipoAppList.appendChild(li);
                    });
                } else {
                    ipoAppList.innerHTML = '<li>No IPO applications added.</li>';
                }
                // Render analytics
                if (window.renderIpoAnalytics) {
                    window.renderIpoAnalytics(data.applications || []);
                }
            });
// Load analytics script
const analyticsScript = document.createElement('script');
analyticsScript.src = 'ipo_analytics.js';
document.head.appendChild(analyticsScript);
    }
    if (ipoAppForm) {
        loadIposAndDemats();
        loadIpoApplications();
        ipoAppForm.onsubmit = function(e) {
            e.preventDefault();
            ipoAppMsg.textContent = 'Adding...';
            fetch('../backend/user_ipo_applications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ipo_id: ipoSelect.value,
                    demat_account_id: dematSelect.value,
                    applied_lots: lotsInput.value,
                    application_date: dateInput.value
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    ipoAppMsg.style.color = 'green';
                    ipoAppMsg.textContent = 'IPO application added!';
                    ipoAppForm.reset();
                    loadIpoApplications();
                } else {
                    ipoAppMsg.style.color = 'red';
                    ipoAppMsg.textContent = data.error || 'Failed to add.';
                }
            })
            .catch(() => {
                ipoAppMsg.style.color = 'red';
                ipoAppMsg.textContent = 'Server error.';
            });
        };
        ipoAppList.onclick = function(e) {
            if (e.target.classList.contains('delete-ipo-app-btn')) {
                const id = e.target.getAttribute('data-id');
                if (confirm('Delete this IPO application?')) {
                    fetch('../backend/user_ipo_applications.php', {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            loadIpoApplications();
                        } else {
                            alert(data.error || 'Delete failed.');
                        }
                    });
                }
            }
        };
    }
});

// Allotment check logic
document.addEventListener('DOMContentLoaded', function() {
    const checkBtn = document.getElementById('check-allotment-btn');
    const resultsDiv = document.getElementById('allotment-results');
    if (checkBtn) {
        checkBtn.onclick = function() {
            resultsDiv.textContent = 'Checking...';
            fetch('../backend/check_allotment.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.allotments.length) {
                        let html = '<table style="width:100%;border-collapse:collapse;">';
                        html += '<tr><th>Account</th><th>Depository</th><th>Holder</th><th>Status</th></tr>';
                        data.allotments.forEach(a => {
                            html += `<tr><td>${a.account_number}</td><td>${a.depository}</td><td>${a.holder_name || ''}</td><td><b>${a.status}</b></td></tr>`;
                        });
                        html += '</table>';
                        resultsDiv.innerHTML = html;
                    } else {
                        resultsDiv.textContent = 'No demat accounts or no results.';
                    }
                })
                .catch(() => {
                    resultsDiv.textContent = 'Server error.';
                });
        };
    }
});

// Demat account management
document.addEventListener('DOMContentLoaded', function() {
    const dematForm = document.getElementById('demat-form');
    const dematList = document.getElementById('demat-list');
    const dematMsg = document.getElementById('demat-msg');
    if (dematForm) {
        function loadDematAccounts() {
            fetch('../backend/user_demat.php')
                .then(res => res.json())
                .then(data => {
                    dematList.innerHTML = '';
                    if (data.success && data.accounts.length) {
                        data.accounts.forEach(acc => {
                            const li = document.createElement('li');
                            li.innerHTML = `<b>${acc.account_number}</b> (${acc.depository}) ${acc.holder_name ? '- ' + acc.holder_name : ''} <button data-id='${acc.id}' class='delete-demat-btn' style='margin-left:10px;'>Delete</button>`;
                            dematList.appendChild(li);
                        });
                    } else {
                        dematList.innerHTML = '<li>No demat accounts added.</li>';
                    }
                });
        }
        loadDematAccounts();
        dematForm.onsubmit = function(e) {
            e.preventDefault();
            dematMsg.textContent = 'Adding...';
            fetch('../backend/user_demat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    account_number: document.getElementById('demat_account_number').value,
                    depository: document.getElementById('demat_depository').value,
                    holder_name: document.getElementById('demat_holder_name').value
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    dematMsg.style.color = 'green';
                    dematMsg.textContent = 'Demat account added!';
                    dematForm.reset();
                    loadDematAccounts();
                } else {
                    dematMsg.style.color = 'red';
                    dematMsg.textContent = data.error || 'Failed to add.';
                }
            })
            .catch(() => {
                dematMsg.style.color = 'red';
                dematMsg.textContent = 'Server error.';
            });
        };
        dematList.onclick = function(e) {
            if (e.target.classList.contains('delete-demat-btn')) {
                const id = e.target.getAttribute('data-id');
                if (confirm('Delete this demat account?')) {
                    fetch('../backend/user_demat.php', {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            loadDematAccounts();
                        } else {
                            alert(data.error || 'Delete failed.');
                        }
                    });
                }
            }
        };
    }
});
