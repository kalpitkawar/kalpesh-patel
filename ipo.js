document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const ipoId = params.get('id');
    const detailsSection = document.getElementById('ipo-details');
    if (!ipoId) {
        detailsSection.innerHTML = '<p>Invalid IPO ID.</p>';
        return;
    }
    fetch(`../backend/get_ipo_details.php?id=${ipoId}`)
        .then(response => response.json())
        .then(ipo => {
            if (!ipo || !ipo.id) {
                detailsSection.innerHTML = '<p>IPO not found.</p>';
                return;
            }
            detailsSection.innerHTML = `<div class="ipo-card">
                <div class="ipo-title">${ipo.name}</div>
                <div class="ipo-dates">Open: ${ipo.open_date} | Close: ${ipo.close_date}</div>
                <div class="ipo-price">Price: ₹${ipo.price}</div>
                <div class="ipo-details">${ipo.details}</div>
                <a href="index.html">&larr; Back to List</a>
            </div>`;
        })
        .catch(() => {
            detailsSection.innerHTML = '<p>Error loading IPO details.</p>';
        });
});
