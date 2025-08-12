document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const ipoId = params.get('id');
    const detailsSection = document.getElementById('ipo-details');
    
    if (!ipoId) {
        detailsSection.innerHTML = '<div class="error">Invalid IPO ID. Please select an IPO from the main page.</div>';
        return;
    }
    
    // Show loading state
    detailsSection.innerHTML = '<div class="loading">Loading IPO details...</div>';
    
    // Updated API path
    fetch(`get_ipo_details.php?id=${encodeURIComponent(ipoId)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(ipo => {
            if (!ipo || !ipo.id) {
                detailsSection.innerHTML = '<div class="error">IPO not found. It may have been removed or the ID is incorrect.</div>';
                return;
            }
            
            // Create detailed IPO view
            detailsSection.innerHTML = `
                <div class="ipo-card ipo-details-card">
                    <div class="ipo-title">${escapeHtml(ipo.name)}</div>
                    <div class="ipo-meta">
                        <div class="ipo-dates">
                            <strong>Open:</strong> ${formatDate(ipo.open_date)} | 
                            <strong>Close:</strong> ${formatDate(ipo.close_date)}
                        </div>
                        <div class="ipo-price">
                            <strong>Price:</strong> ₹${parseFloat(ipo.price).toFixed(2)}
                        </div>
                        ${ipo.lot_size ? `<div class="ipo-lot"><strong>Lot Size:</strong> ${ipo.lot_size}</div>` : ''}
                        ${ipo.status ? `<div class="ipo-status">Status: <span class="status-badge status-${ipo.status}">${ipo.status}</span></div>` : ''}
                    </div>
                    <div class="ipo-details-content">
                        <h3>Details</h3>
                        <p>${escapeHtml(ipo.details).replace(/\n/g, '<br>')}</p>
                    </div>
                    <div class="ipo-actions">
                        <a href="index.html" class="btn-back">&larr; Back to IPO List</a>
                        ${ipo.status === 'live' ? '<button class="btn-apply" onclick="applyForIPO()">Apply for IPO</button>' : ''}
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error loading IPO details:', error);
            detailsSection.innerHTML = '<div class="error">Unable to load IPO details. Please check your connection and try again.</div>';
        });
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-IN', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    } catch {
        return dateString;
    }
}

function applyForIPO() {
    // Placeholder for IPO application functionality
    alert('IPO application functionality would be implemented here. This would typically redirect to a secure application form.');
}
