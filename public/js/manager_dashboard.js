// https://www.chartjs.org/docs/latest/

class DashboardDataService {
    constructor() {
        this.apiBaseUrl = '/api';
        this.useMockData = true;
    }

    async getSummaryMetrics(period = 'january') {
        if (this.useMockData) {
            return this._getMockSummaryMetrics(period);
        }

        const response = await fetch(`${this.apiBaseUrl}/metrics/summary?period=${period}`);
        if (!response.ok) throw new Error('Failed to fetch summary metrics');
        return response.json();
    }

    async getStatisticsData(period = 'january') {
        if (this.useMockData) {
            return this._getMockStatisticsData(period);
        }

        const response = await fetch(`${this.apiBaseUrl}/metrics/statistics?period=${period}`);
        if (!response.ok) throw new Error('Failed to fetch statistics');
        return response.json();
    }

    async getSecondaryMetrics() {
        if (this.useMockData) {
            return this._getMockSecondaryMetrics();
        }

        const response = await fetch(`${this.apiBaseUrl}/metrics/secondary`);
        if (!response.ok) throw new Error('Failed to fetch secondary metrics');
        return response.json();
    }

    async getEventReviews(searchQuery = '') {
        if (this.useMockData) {
            return this._getMockEventReviews(searchQuery);
        }

        const url = searchQuery
            ? `${this.apiBaseUrl}/reviews?search=${encodeURIComponent(searchQuery)}`
            : `${this.apiBaseUrl}/reviews`;
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch reviews');
        return response.json();
    }

    async getManagerProfile() {
        if (this.useMockData) {
            return this._getMockManagerProfile();
        }

        const response = await fetch(`${this.apiBaseUrl}/manager/profile`);
        if (!response.ok) throw new Error('Failed to fetch profile');
        return response.json();
    }

    async exportData(format = 'csv') {
        if (this.useMockData) {
            return this._getMockExportData(format);
        }

        const response = await fetch(`${this.apiBaseUrl}/export?format=${format}`);
        if (!response.ok) throw new Error('Failed to export data');
        return response.blob();
    }

    _getMockSummaryMetrics(period) {
        const monthlyData = {
            january: { revenue: 7000, tickets: 12000, events: 5, revenueChange: 11.5, ticketsChange: -11.5 },
            february: { revenue: 8500, tickets: 14500, events: 6, revenueChange: 21.4, ticketsChange: 20.8 },
            march: { revenue: 6200, tickets: 10800, events: 4, revenueChange: -27.1, ticketsChange: -25.5 },
            april: { revenue: 9100, tickets: 15200, events: 7, revenueChange: 46.8, ticketsChange: 40.7 },
            may: { revenue: 7800, tickets: 13100, events: 5, revenueChange: -14.3, ticketsChange: -13.8 },
            june: { revenue: 11200, tickets: 18500, events: 8, revenueChange: 43.6, ticketsChange: 41.2 },
            july: { revenue: 12500, tickets: 20100, events: 9, revenueChange: 11.6, ticketsChange: 8.6 },
            august: { revenue: 10800, tickets: 17800, events: 7, revenueChange: -13.6, ticketsChange: -11.4 },
            september: { revenue: 8900, tickets: 14600, events: 6, revenueChange: -17.6, ticketsChange: -18.0 },
            october: { revenue: 9500, tickets: 15900, events: 6, revenueChange: 6.7, ticketsChange: 8.9 },
            november: { revenue: 13200, tickets: 21500, events: 10, revenueChange: 38.9, ticketsChange: 35.2 },
            december: { revenue: 15800, tickets: 25200, events: 12, revenueChange: 19.7, ticketsChange: 17.2 }
        };

        const data = monthlyData[period.toLowerCase()] || monthlyData.january;

        return Promise.resolve({
            totalRevenue: data.revenue,
            ticketsSold: data.tickets,
            activeEvents: data.events,
            revenueChange: data.revenueChange,
            ticketsChange: data.ticketsChange,
            currency: 'GHC'
        });
    }

    _getMockStatisticsData(period) {
        const baseData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            sales: [32000, 45000, 28000, 52000, 38000, 48000, 55000, 42000, 35000, 58000, 62000, 48000],
            returns: [8000, 12000, 6000, 10000, 9000, 11000, 13000, 10000, 8000, 14000, 15000, 11000]
        };

        return Promise.resolve(baseData);
    }

    _getMockSecondaryMetrics() {
        return Promise.resolve({
            avgRating: 4.4,
            maxRating: 5,
            engagementRate: 5.6,
            ratingTrend: 'down',
            engagementTrend: 'down'
        });
    }

    _getMockEventReviews(searchQuery = '') {
        const reviews = [
            {
                id: 1,
                name: 'Guy Hawkins',
                comment: 'Amazing event! The organization was perfect and the atmosphere was incredible.',
                date: 'May 29, 2024',
                rating: 5,
                avatar: '#C05F47'
            },
            {
                id: 2,
                name: 'Robert Fox',
                comment: 'Great experience overall. Would definitely recommend to others.',
                date: 'May 11, 2024',
                rating: 4,
                avatar: '#C05F47'
            },
            {
                id: 3,
                name: 'Jenny Wilson',
                comment: 'The venue was spectacular. Looking forward to the next one!',
                date: 'Apr 28, 2024',
                rating: 5,
                avatar: '#8B5A4A'
            },
            {
                id: 4,
                name: 'Devon Lane',
                comment: 'Good event but the check-in process could be improved.',
                date: 'Apr 15, 2024',
                rating: 3,
                avatar: '#C05F47'
            },
            {
                id: 5,
                name: 'Kristin Watson',
                comment: 'Absolutely loved it! Best event I attended this year.',
                date: 'Mar 22, 2024',
                rating: 5,
                avatar: '#8B5A4A'
            }
        ];

        if (searchQuery) {
            const query = searchQuery.toLowerCase();
            return Promise.resolve(
                reviews.filter(r =>
                    r.name.toLowerCase().includes(query) ||
                    r.comment.toLowerCase().includes(query)
                )
            );
        }

        return Promise.resolve(reviews);
    }

    _getMockManagerProfile() {
        const storedUserData = sessionStorage.getItem('userData');
        if (storedUserData) {
            const userData = JSON.parse(storedUserData);
            return Promise.resolve({
                id: 1,
                firstName: userData.firstName || 'Jerome',
                lastName: userData.lastName || 'Adedze',
                email: userData.email || 'jerome@bonten.com',
                avatar: '../assets/jerome.jpeg',
                role: 'Event Manager'
            });
        }

        return Promise.resolve({
            id: 1,
            firstName: 'Jerome',
            lastName: 'Adedze',
            email: 'jerome@bonten.com',
            avatar: '../assets/jerome.jpeg',
            role: 'Event Manager'
        });
    }

    _getMockExportData(format) {
        const csvContent = `Date,Revenue,Tickets Sold,Events
January,7000,12000,5
February,8500,14500,6
March,6200,10800,4
April,9100,15200,7
May,7800,13100,5
June,11200,18500,8`;

        const blob = new Blob([csvContent], { type: 'text/csv' });
        return Promise.resolve(blob);
    }
}

class DashboardController {
    constructor() {
        this.dataService = new DashboardDataService();
        this.chartInstance = null;
        this.currentPeriod = 'january';

        this.init();
    }

    async init() {
        try {
            await this.loadManagerProfile();
            await this.loadAllMetrics();
            this.setupEventListeners();
        } catch (error) {
            console.error('Dashboard initialization failed:', error);
            this.showToast('Failed to load dashboard data', 'error');
        }
    }

    setupEventListeners() {
        const periodSelector = document.getElementById('summaryPeriod');
        if (periodSelector) {
            periodSelector.addEventListener('change', (e) => {
                this.currentPeriod = e.target.value;
                this.loadAllMetrics();
            });
        }

        const searchInput = document.getElementById('insightsSearch');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.loadReviews(e.target.value);
                }, 300);
            });

            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && searchInput.value.trim()) {
                    window.location.href = `manager_history.php?search=${encodeURIComponent(searchInput.value.trim())}`;
                }
            });
        }

        const addReviewBtn = document.getElementById('addReviewBtn');
        if (addReviewBtn) {
            addReviewBtn.addEventListener('click', () => {
                window.location.href = '../../views/manager_history.php';
            });
        }

        this.setupMetricCardClicks();
    }

    setupMetricCardClicks() {
        const revenueCard = document.querySelector('.metric-card:has(#totalRevenue)');
        if (revenueCard) {
            revenueCard.style.cursor = 'pointer';
            revenueCard.addEventListener('click', () => {
                window.location.href = '../../views/manager_history.php?sort=revenue-desc';
            });
        }

        const ticketsCard = document.querySelector('.metric-card:has(#ticketsSold)');
        if (ticketsCard) {
            ticketsCard.style.cursor = 'pointer';
            ticketsCard.addEventListener('click', () => {
                window.location.href = '../../views/manager_history.php?sort=tickets-desc';
            });
        }

        const eventsCard = document.querySelector('.metric-card:has(#activeEvents)');
        if (eventsCard) {
            eventsCard.style.cursor = 'pointer';
            eventsCard.addEventListener('click', () => {
                window.location.href = '../../views/manager_history.php?status=active';
            });
        }

        const ratingCard = document.querySelector('.metric-card.secondary:has(#avgRating)');
        if (ratingCard) {
            ratingCard.style.cursor = 'pointer';
            ratingCard.addEventListener('click', () => {
                window.location.href = '../../views/manager_history.php?status=completed';
            });
        }

        const engagementCard = document.querySelector('.metric-card.secondary:has(#engagementRate)');
        if (engagementCard) {
            engagementCard.style.cursor = 'pointer';
            engagementCard.addEventListener('click', () => {
                window.location.href = '../../views/manager_history.php';
            });
        }

        document.querySelectorAll('.metric-arrow').forEach(arrow => {
            arrow.style.cursor = 'pointer';
            arrow.addEventListener('click', (e) => {
                e.stopPropagation();
                window.location.href = '../../views/manager_history.php';
            });
        });
    }

    async loadAllMetrics() {
        this.showLoadingState();

        try {
            await Promise.all([
                this.loadSummaryMetrics(),
                this.loadStatistics(),
                this.loadSecondaryMetrics(),
                this.loadReviews()
            ]);
        } catch (error) {
            console.error('Error loading metrics:', error);
            this.showToast('Error loading some metrics', 'error');
        }
    }

    async loadManagerProfile() {
        try {
            const profile = await this.dataService.getManagerProfile();

            const welcomeName = document.getElementById('welcomeName');
            if (welcomeName) {
                welcomeName.textContent = profile.firstName;
            }

            const managerName = document.getElementById('managerName');
            if (managerName) {
                managerName.textContent = `${profile.firstName} ${profile.lastName}`;
            }
        } catch (error) {
            console.error('Error loading profile:', error);
        }
    }

    async loadSummaryMetrics() {
        try {
            const metrics = await this.dataService.getSummaryMetrics(this.currentPeriod);

            const revenueEl = document.getElementById('totalRevenue');
            if (revenueEl) {
                revenueEl.textContent = `${metrics.currency}${metrics.totalRevenue.toLocaleString()}`;
                revenueEl.classList.remove('loading');
            }

            const revenueChangeEl = document.getElementById('revenueChange');
            if (revenueChangeEl) {
                this.updateChangeIndicator(revenueChangeEl, metrics.revenueChange);
            }

            const ticketsEl = document.getElementById('ticketsSold');
            if (ticketsEl) {
                ticketsEl.textContent = metrics.ticketsSold.toLocaleString();
                ticketsEl.classList.remove('loading');
            }

            const ticketsChangeEl = document.getElementById('ticketsChange');
            if (ticketsChangeEl) {
                this.updateChangeIndicator(ticketsChangeEl, metrics.ticketsChange);
            }

            const eventsEl = document.getElementById('activeEvents');
            if (eventsEl) {
                eventsEl.textContent = metrics.activeEvents;
                eventsEl.classList.remove('loading');
            }
        } catch (error) {
            console.error('Error loading summary metrics:', error);
            throw error;
        }
    }

    updateChangeIndicator(element, changeValue) {
        const isPositive = changeValue >= 0;
        element.className = `metric-change ${isPositive ? 'positive' : 'negative'}`;

        const iconPath = isPositive
            ? 'M7 14l5-5 5 5H7z'
            : 'M7 10l5 5 5-5H7z';

        element.innerHTML = `
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                <path d="${iconPath}"/>
            </svg>
            <span>${Math.abs(changeValue).toFixed(1)}%</span>
        `;
    }

    async loadStatistics() {
        try {
            const data = await this.dataService.getStatisticsData(this.currentPeriod);
            this.renderChart(data);
        } catch (error) {
            console.error('Error loading statistics:', error);
            throw error;
        }
    }

    renderChart(data) {
        const canvas = document.getElementById('salesChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        if (this.chartInstance) {
            this.chartInstance.destroy();
        }

        if (typeof Chart === 'undefined') {
            this.loadChartJS().then(() => {
                this.createChart(ctx, data);
            });
        } else {
            this.createChart(ctx, data);
        }
    }

    loadChartJS() {
        return new Promise((resolve, reject) => {
            if (typeof Chart !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    createChart(ctx, data) {
        this.chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Sales',
                        data: data.sales,
                        backgroundColor: '#C05F47',
                        borderRadius: 4,
                        barThickness: 20
                    },
                    {
                        label: 'Returns',
                        data: data.returns,
                        backgroundColor: '#8B5A4A',
                        borderRadius: 4,
                        barThickness: 20
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleColor: '#fff',
                        bodyColor: '#888',
                        borderColor: '#333',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: GHC ${context.parsed.y.toLocaleString()}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#666',
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: '#1a1a1a'
                        },
                        ticks: {
                            color: '#666',
                            font: {
                                size: 10
                            },
                            callback: function(value) {
                                if (value >= 1000) {
                                    return (value / 1000) + 'k';
                                }
                                return value;
                            }
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    async loadSecondaryMetrics() {
        try {
            const metrics = await this.dataService.getSecondaryMetrics();

            const ratingEl = document.getElementById('avgRating');
            if (ratingEl) {
                ratingEl.textContent = `${metrics.avgRating}/${metrics.maxRating}`;
            }

            const engagementEl = document.getElementById('engagementRate');
            if (engagementEl) {
                engagementEl.textContent = `${metrics.engagementRate}%`;
            }

            this.updateTrendIcon('avgRating', metrics.ratingTrend);
            this.updateTrendIcon('engagementRate', metrics.engagementTrend);
        } catch (error) {
            console.error('Error loading secondary metrics:', error);
            throw error;
        }
    }

    updateTrendIcon(metricId, trend) {
        const metricCard = document.getElementById(metricId)?.closest('.metric-card');
        if (!metricCard) return;

        const trendIcon = metricCard.querySelector('.trend-icon');
        if (!trendIcon) return;

        trendIcon.classList.remove('positive', 'negative');

        if (trend === 'up') {
            trendIcon.classList.add('positive');
            trendIcon.innerHTML = '<path d="M7 14l5-5 5 5H7z"/>';
        } else if (trend === 'down') {
            trendIcon.classList.add('negative');
            trendIcon.innerHTML = '<path d="M7 10l5 5 5-5H7z"/>';
        }
    }

    async loadReviews(searchQuery = '') {
        try {
            const reviews = await this.dataService.getEventReviews(searchQuery);
            this.renderReviewsTable(reviews);
        } catch (error) {
            console.error('Error loading reviews:', error);
            throw error;
        }
    }

    renderReviewsTable(reviews) {
        const tbody = document.getElementById('reviewsTableBody');
        if (!tbody) return;

        if (reviews.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: #666;">
                        No reviews found
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = reviews.map(review => `
            <tr>
                <td>
                    <div class="reviewer-avatar" style="background-color: ${review.avatar}"></div>
                </td>
                <td class="reviewer-name">${this.escapeHtml(review.name)}</td>
                <td class="review-comment" title="${this.escapeHtml(review.comment)}">${this.escapeHtml(review.comment)}</td>
                <td class="review-date">${review.date}</td>
                <td>
                    <div class="star-rating">
                        ${this.renderStars(review.rating)}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += `<span class="star ${i <= rating ? 'filled' : ''}">★</span>`;
        }
        return stars;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showLoadingState() {
        const metricValues = document.querySelectorAll('.metric-value');
        metricValues.forEach(el => {
            el.classList.add('loading');
        });
    }


    showToast(message, type = 'info') {
        const existingToast = document.querySelector('.toast');
        if (existingToast) {
            existingToast.remove();
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
}

function formatCurrency(value, currency = 'GHC') {
    return `${currency}${value.toLocaleString()}`;
}

function formatNumber(value) {
    if (value >= 1000000) {
        return (value / 1000000).toFixed(1) + 'M';
    }
    if (value >= 1000) {
        return (value / 1000).toFixed(1) + 'K';
    }
    return value.toString();
}

function calculatePercentageChange(current, previous) {
    if (previous === 0) return 0;
    return ((current - previous) / previous) * 100;
}

document.addEventListener('DOMContentLoaded', () => {
    window.dashboardController = new DashboardController();
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        DashboardDataService,
        DashboardController,
        formatCurrency,
        formatNumber,
        calculatePercentageChange
    };
}
