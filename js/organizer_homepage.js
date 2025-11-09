// Organizer Dashboard JavaScript
// This file handles the statistics chart and other interactive elements

document.addEventListener('DOMContentLoaded', function() {
    // Draw the statistics chart
    drawStatisticsChart();
    
    // Add any other interactive functionality here
});

function drawStatisticsChart() {
    const canvas = document.getElementById('statisticsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = canvas.offsetWidth;
    const height = canvas.offsetHeight;
    
    // Set canvas dimensions
    canvas.width = width;
    canvas.height = height;
    
    // Chart configuration
    const padding = { top: 20, right: 20, bottom: 30, left: 40 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    
    // Sample data - in a real app, this would come from an API
    const data = [
        { sales: 45000, returns: 5000 },
        { sales: 52000, returns: 6000 },
        { sales: 48000, returns: 5500 },
        { sales: 61000, returns: 7000 },
        { sales: 55000, returns: 6500 },
        { sales: 67000, returns: 8000 },
        { sales: 59000, returns: 7000 },
        { sales: 64000, returns: 7500 }
    ];
    
    // Calculate max value for scaling
    const maxValue = Math.max(...data.map(d => d.sales + d.returns));
    const yAxisMax = Math.ceil(maxValue / 10000) * 10000; // Round up to nearest 10k
    const yAxisStep = yAxisMax / 6; // 6 steps on Y-axis
    
    // Colors
    const salesColor = '#C05F47'; // Secondary color (reddish-brown)
    const returnsColor = '#FFFFFF'; // White
    const gridColor = '#333333';
    const textColor = '#888888';
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    
    // Draw Y-axis labels and grid lines
    ctx.font = '12px Poppins';
    ctx.fillStyle = textColor;
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    
    for (let i = 0; i <= 6; i++) {
        const value = i * yAxisStep;
        const y = padding.top + chartHeight - (value / yAxisMax) * chartHeight;
        
        // Draw grid line
        ctx.strokeStyle = gridColor;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(padding.left, y);
        ctx.lineTo(padding.left + chartWidth, y);
        ctx.stroke();
        
        // Draw label
        const label = value / 1000 + 'K';
        ctx.fillText(label, padding.left - 10, y);
    }
    
    // Draw bars
    const barWidth = chartWidth / (data.length * 2.5); // Width of each bar in a pair
    const gap = barWidth * 0.3; // Gap between bars in a pair
    const pairGap = barWidth * 0.5; // Gap between pairs
    
    data.forEach((item, index) => {
        const x = padding.left + index * (barWidth * 2 + gap + pairGap) + pairGap / 2;
        
        // Draw Sales bar
        const salesHeight = (item.sales / yAxisMax) * chartHeight;
        ctx.fillStyle = salesColor;
        ctx.fillRect(x, padding.top + chartHeight - salesHeight, barWidth, salesHeight);
        
        // Draw Returns bar
        const returnsHeight = (item.returns / yAxisMax) * chartHeight;
        ctx.fillStyle = returnsColor;
        ctx.fillRect(x + barWidth + gap, padding.top + chartHeight - returnsHeight, barWidth, returnsHeight);
    });
    
    // Draw X-axis line
    ctx.strokeStyle = gridColor;
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padding.left, padding.top + chartHeight);
    ctx.lineTo(padding.left + chartWidth, padding.top + chartHeight);
    ctx.stroke();
}

// Redraw chart on window resize
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        drawStatisticsChart();
    }, 250);
});

