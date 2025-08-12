document.addEventListener('DOMContentLoaded', function() {
    const ipoList = document.getElementById('ipo-list-admin');
    const form = document.getElementById('add-ipo-form');
    const msg = document.getElementById('add-ipo-msg');

    function loadIPOs() {
        ipoList.innerHTML = 'Loading...';
        fetch('../backend/admin_get_ipos.php')
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    ipoList.innerHTML = '<p style="color:red;">' + data.error + '</p>';
                    return;
                }
                if (!data.length) {
                    ipoList.innerHTML = '<p>No IPOs found.</p>';
                    return;
                }
                ipoList.innerHTML = '<table><tr><th>Name</th><th>Open</th><th>Close</th><th>Price</th><th>Status</th></tr>';
                data.forEach(ipo => {
                    ipoList.innerHTML += `<tr><td>${ipo.name}</td><td>${ipo.open_date}</td><td>${ipo.close_date}</td><td>₹${ipo.price}</td><td>${ipo.status}</td></tr>`;
                });
                ipoList.innerHTML += '</table>';
            })
            .catch(() => {
                ipoList.innerHTML = '<p style="color:red;">Error loading IPOs.</p>';
            });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        msg.textContent = 'Adding...';
        const formData = new FormData(form);
        const data = {};
        formData.forEach((v, k) => data[k] = v);
        fetch('../backend/admin_add_ipo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                msg.style.color = 'green';
                msg.textContent = 'IPO added!';
                form.reset();
                loadIPOs();
            } else {
                msg.style.color = 'red';
                msg.textContent = data.error || 'Failed to add IPO.';
            }
        })
        .catch(() => {
            msg.style.color = 'red';
            msg.textContent = 'Server error.';
        });
    });

    loadIPOs();
});
