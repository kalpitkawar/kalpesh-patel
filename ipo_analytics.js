// Simple chart rendering for IPO analytics (listing gains)
// Requires: <canvas id="ipo-analytics-chart"></canvas> in HTML
function renderIpoAnalytics(applications) {
    const summaryDiv = document.getElementById('ipo-analytics-summary');
    const chartCanvas = document.getElementById('ipo-analytics-chart');
    if (!applications.length) {
        summaryDiv.textContent = 'No IPO applications to analyze.';
        if (chartCanvas) chartCanvas.style.display = 'none';
        return;
    }
    // Calculate stats
    let totalApps = applications.length;
    let allotted = applications.filter(a => a.status === 'allotted').length;
    let totalGain = applications.reduce((sum, a) => sum + (parseFloat(a.listing_gain) || 0), 0);
    summaryDiv.innerHTML = `<b>Total Applications:</b> ${totalApps} | <b>Allotted:</b> ${allotted} | <b>Total Listing Gain:</b> ₹${totalGain.toFixed(2)}`;
    // Prepare chart data (IPO name vs listing gain)
    const labels = applications.map(a => a.ipo_name);
    const gains = applications.map(a => parseFloat(a.listing_gain) || 0);
    // Draw simple bar chart
    if (chartCanvas) {
        chartCanvas.width = Math.max(400, labels.length * 60);
        chartCanvas.height = 300;
        const ctx = chartCanvas.getContext('2d');
        ctx.clearRect(0, 0, chartCanvas.width, chartCanvas.height);
        // Axes
        ctx.beginPath();
        ctx.moveTo(40, 10);
        ctx.lineTo(40, 260);
        ctx.lineTo(chartCanvas.width - 10, 260);
        ctx.strokeStyle = '#333';
        ctx.stroke();
        // Bars
        const maxGain = Math.max(...gains, 10);
        for (let i = 0; i < gains.length; i++) {
            const x = 60 + i * 60;
            const y = 260 - (gains[i] / maxGain) * 200;
            ctx.fillStyle = '#3949ab';
            ctx.fillRect(x, y, 40, 260 - y);
            ctx.fillStyle = '#222';
            ctx.fillText(labels[i].slice(0,8), x, 280);
            ctx.fillText(gains[i], x, y - 5);
        }
    }
}
