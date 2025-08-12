document.addEventListener('DOMContentLoaded', function() {
    const ipoList = document.getElementById('ipo-list-admin');
    const form = document.getElementById('add-ipo-form');
    const msg = document.getElementById('add-ipo-msg');

    function validateForm(formData) {
        const errors = [];
        
        if (!formData.name || formData.name.trim().length < 2) {
            errors.push('IPO name must be at least 2 characters.');
        }
        
        if (!formData.open_date) {
            errors.push('Open date is required.');
        }
        
        if (!formData.close_date) {
            errors.push('Close date is required.');
        }
        
        if (formData.open_date && formData.close_date && new Date(formData.open_date) >= new Date(formData.close_date)) {
            errors.push('Close date must be after open date.');
        }
        
        if (!formData.price || parseFloat(formData.price) <= 0) {
            errors.push('Price must be a positive number.');
        }
        
        if (!formData.details || formData.details.trim().length < 10) {
            errors.push('Details must be at least 10 characters.');
        }
        
        return errors;
    }

    function showMessage(element, message, type) {
        element.textContent = message;
        element.style.color = type === 'error' ? '#f44336' : 
                             type === 'success' ? '#4caf50' : '#666';
    }

    function loadIPOs() {
        ipoList.innerHTML = '<div class="loading">Loading IPOs...</div>';
        
        // Updated API path
        fetch('admin_get_ipos.php')
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data.error) {
                    ipoList.innerHTML = `<div class="error">Error: ${data.error}</div>`;
                    return;
                }
                
                if (!data.length) {
                    ipoList.innerHTML = '<div class="empty-state">No IPOs found. Add your first IPO using the form above.</div>';
                    return;
                }
                
                // Create a properly structured table
                let tableHTML = `
                    <div class="ipo-admin-table-container">
                        <table class="ipo-admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Open Date</th>
                                    <th>Close Date</th>
                                    <th>Price (₹)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.forEach(ipo => {
                    tableHTML += `
                        <tr>
                            <td><strong>${escapeHtml(ipo.name)}</strong></td>
                            <td>${formatDate(ipo.open_date)}</td>
                            <td>${formatDate(ipo.close_date)}</td>
                            <td>₹${parseFloat(ipo.price).toFixed(2)}</td>
                            <td><span class="status-badge status-${ipo.status}">${ipo.status}</span></td>
                            <td>
                                <button class="btn-edit" onclick="editIPO(${ipo.id})">Edit</button>
                                <button class="btn-delete" onclick="deleteIPO(${ipo.id})">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                
                tableHTML += '</tbody></table></div>';
                ipoList.innerHTML = tableHTML;
            })
            .catch(error => {
                console.error('Error loading IPOs:', error);
                ipoList.innerHTML = '<div class="error">Unable to load IPOs. Please check your connection and try again.</div>';
            });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleDateString('en-IN');
        } catch {
            return dateString;
        }
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((v, k) => data[k] = v.trim());
        
        // Client-side validation
        const errors = validateForm(data);
        if (errors.length > 0) {
            showMessage(msg, errors.join(' '), 'error');
            return;
        }
        
        // Disable form during submission
        submitBtn.disabled = true;
        submitBtn.textContent = 'Adding IPO...';
        showMessage(msg, 'Adding IPO...', 'info');
        
        // Updated API path
        fetch('admin_add_ipo.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                showMessage(msg, 'IPO added successfully!', 'success');
                form.reset();
                loadIPOs();
                // Focus on first input for next entry
                form.querySelector('input').focus();
            } else {
                showMessage(msg, data.error || 'Failed to add IPO. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error adding IPO:', error);
            showMessage(msg, 'Unable to add IPO. Please check your connection and try again.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Add IPO';
        });
    });

    // Load IPOs when page loads
    loadIPOs();
    
    // Add form validation on input
    form.addEventListener('input', function(e) {
        const field = e.target;
        
        // Real-time validation feedback
        if (field.name === 'close_date') {
            const openDate = form.querySelector('[name="open_date"]').value;
            if (openDate && field.value && new Date(openDate) >= new Date(field.value)) {
                field.setCustomValidity('Close date must be after open date');
            } else {
                field.setCustomValidity('');
            }
        }
        
        if (field.name === 'open_date') {
            const closeDate = form.querySelector('[name="close_date"]').value;
            if (closeDate && field.value && new Date(field.value) >= new Date(closeDate)) {
                form.querySelector('[name="close_date"]').setCustomValidity('Close date must be after open date');
            } else {
                form.querySelector('[name="close_date"]').setCustomValidity('');
            }
        }
    });
});
