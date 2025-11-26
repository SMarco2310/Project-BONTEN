/**
 * Manager History Page - Event Management History
 *
 * This module provides a data abstraction layer for event history management.
 * To connect to a real database:
 * 1. Replace the mock implementations in HistoryDataService
 * 2. Update the fetch methods to call your API endpoints
 */

// ============================================================================
// DATA SERVICE LAYER
// ============================================================================

class HistoryDataService {
    constructor() {
        this.apiBaseUrl = '/api';
        this.useMockData = true;
    }

    /**
     * Fetch all events for the manager
     * @param {Object} filters - Filter options (status, sort)
     * @returns {Promise<Object>} Events data categorized by status
     */
    async getManagerEvents(filters = {}) {
        if (this.useMockData) {
            return this._getMockManagerEvents(filters);
        }

        const params = new URLSearchParams(filters);
        const response = await fetch(`${this.apiBaseUrl}/manager/events?${params}`);
        if (!response.ok) throw new Error('Failed to fetch events');
        return response.json();
    }

    /**
     * Fetch event details by ID
     * @param {string} eventId - Event ID
     * @returns {Promise<Object>} Event details
     */
    async getEventDetails(eventId) {
        if (this.useMockData) {
            return this._getMockEventDetails(eventId);
        }

        const response = await fetch(`${this.apiBaseUrl}/events/${eventId}`);
        if (!response.ok) throw new Error('Failed to fetch event details');
        return response.json();
    }

    /**
     * Fetch summary statistics
     * @returns {Promise<Object>} Summary stats
     */
    async getSummaryStats() {
        if (this.useMockData) {
            return this._getMockSummaryStats();
        }

        const response = await fetch(`${this.apiBaseUrl}/manager/stats`);
        if (!response.ok) throw new Error('Failed to fetch stats');
        return response.json();
    }

    /**
     * Cancel an event
     * @param {string} eventId - Event ID
     * @param {string} reason - Cancellation reason
     * @returns {Promise<Object>} Result
     */
    async cancelEvent(eventId, reason) {
        if (this.useMockData) {
            return this._getMockCancelEvent(eventId, reason);
        }

        const response = await fetch(`${this.apiBaseUrl}/events/${eventId}/cancel`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reason })
        });
        if (!response.ok) throw new Error('Failed to cancel event');
        return response.json();
    }

    /**
     * Delete a draft event
     * @param {string} eventId - Event ID
     * @returns {Promise<Object>} Result
     */
    async deleteEvent(eventId) {
        if (this.useMockData) {
            return this._getMockDeleteEvent(eventId);
        }

        const response = await fetch(`${this.apiBaseUrl}/events/${eventId}`, {
            method: 'DELETE'
        });
        if (!response.ok) throw new Error('Failed to delete event');
        return response.json();
    }

    /**
     * Export history data
     * @param {string} format - Export format (csv, pdf)
     * @returns {Promise<Blob>} Exported data
     */
    async exportHistory(format = 'csv') {
        if (this.useMockData) {
            return this._getMockExportData(format);
        }

        const response = await fetch(`${this.apiBaseUrl}/manager/export?format=${format}`);
        if (!response.ok) throw new Error('Failed to export data');
        return response.blob();
    }

    // ========================================================================
    // MOCK DATA IMPLEMENTATIONS
    // ========================================================================

    _getMockManagerEvents(filters = {}) {
        const allEvents = [
            // Active Events
            {
                id: 'ashchella-2024',
                name: 'Ashchella 2024',
                category: 'ASC Week',
                image: '../assets/ashchella.jpg',
                date: 'December 25, 2024',
                dateObj: new Date('2024-12-25'),
                location: 'Ashesi University',
                ticketsSold: 850,
                totalTickets: 1000,
                revenue: 42500,
                status: 'active',
                rating: null,
                ticketTypes: [
                    { name: 'Early Bird', sold: 200, total: 200, price: 40 },
                    { name: 'Regular', sold: 500, total: 600, price: 50 },
                    { name: 'VIP', sold: 150, total: 200, price: 80 }
                ]
            },
            {
                id: 'y2k-neon-2024',
                name: 'Y2K Neon Party',
                category: 'Fashion',
                image: '../assets/y2k.JPG',
                date: 'December 28, 2024',
                dateObj: new Date('2024-12-28'),
                location: 'Republic Bar & Grill',
                ticketsSold: 320,
                totalTickets: 500,
                revenue: 19200,
                status: 'active',
                rating: null,
                ticketTypes: [
                    { name: 'General', sold: 250, total: 400, price: 50 },
                    { name: 'VIP', sold: 70, total: 100, price: 100 }
                ]
            },
            {
                id: 'new-year-bash',
                name: 'New Year Bash 2025',
                category: 'Concert',
                image: '../assets/detty.webp',
                date: 'December 31, 2024',
                dateObj: new Date('2024-12-31'),
                location: 'Labadi Beach Hotel',
                ticketsSold: 1200,
                totalTickets: 2000,
                revenue: 96000,
                status: 'active',
                rating: null,
                ticketTypes: [
                    { name: 'Standard', sold: 800, total: 1500, price: 60 },
                    { name: 'Premium', sold: 300, total: 400, price: 120 },
                    { name: 'VVIP', sold: 100, total: 100, price: 250 }
                ]
            },
            // Past Events
            {
                id: 'tidal-rave-2023',
                name: 'Tidal Rave 2023',
                category: 'Concert',
                image: '../assets/tidalrave.jpg',
                date: 'December 20, 2023',
                dateObj: new Date('2023-12-20'),
                location: 'Labadi Beach',
                ticketsSold: 1500,
                totalTickets: 1500,
                revenue: 112500,
                status: 'completed',
                rating: 4.5,
                checkins: 1420,
                reviews: [
                    { name: 'Kofi Mensah', rating: 5, text: 'Amazing experience! Best beach party ever.' },
                    { name: 'Ama Serwaa', rating: 4, text: 'Great music, great vibes. Will come again!' }
                ],
                ticketTypes: [
                    { name: 'Early Bird', sold: 500, total: 500, price: 60 },
                    { name: 'Regular', sold: 800, total: 800, price: 75 },
                    { name: 'VIP', sold: 200, total: 200, price: 120 }
                ]
            },
            {
                id: 'gff-2023',
                name: 'Global Football Festival',
                category: 'Football',
                image: '../assets/gff.jpg',
                date: 'December 1, 2023',
                dateObj: new Date('2023-12-01'),
                location: 'Accra Sports Stadium',
                ticketsSold: 2200,
                totalTickets: 2500,
                revenue: 88000,
                status: 'completed',
                rating: 4.2,
                checkins: 2050,
                reviews: [
                    { name: 'Kwame Asante', rating: 4, text: 'Good event but food lines were too long.' },
                    { name: 'Akosua Boateng', rating: 5, text: 'My kids loved it! Great family event.' }
                ],
                ticketTypes: [
                    { name: 'Adult', sold: 1500, total: 1800, price: 40 },
                    { name: 'Child', sold: 500, total: 500, price: 20 },
                    { name: 'Family Pack', sold: 200, total: 200, price: 100 }
                ]
            },
            {
                id: 'rapperholic-2023',
                name: 'Rapperholic 2023',
                category: 'Concert',
                image: '../assets/rapperholic.jpeg',
                date: 'November 28, 2023',
                dateObj: new Date('2023-11-28'),
                location: 'Accra International Conference Centre',
                ticketsSold: 3000,
                totalTickets: 3000,
                revenue: 225000,
                status: 'completed',
                rating: 4.8,
                checkins: 2890,
                reviews: [
                    { name: 'Yaw Darko', rating: 5, text: 'Sarkodie never disappoints! Legendary show.' },
                    { name: 'Efua Mensah', rating: 5, text: 'Best concert I\'ve attended in years!' }
                ],
                ticketTypes: [
                    { name: 'Regular', sold: 2000, total: 2000, price: 60 },
                    { name: 'VIP', sold: 800, total: 800, price: 100 },
                    { name: 'VVIP', sold: 200, total: 200, price: 200 }
                ]
            },
            {
                id: 'imullar-2023',
                name: 'iMullar Experience',
                category: 'Concert',
                image: '../assets/imullar.jpg',
                date: 'November 20, 2023',
                dateObj: new Date('2023-11-20'),
                location: '+233 Jazz Bar & Grill',
                ticketsSold: 450,
                totalTickets: 500,
                revenue: 45000,
                status: 'completed',
                rating: 4.6,
                checkins: 440,
                reviews: [
                    { name: 'Nana Aba', rating: 5, text: 'Intimate setting, great performances!' }
                ],
                ticketTypes: [
                    { name: 'Standard', sold: 350, total: 400, price: 80 },
                    { name: 'VIP Table', sold: 100, total: 100, price: 200 }
                ]
            },
            // Cancelled Events
            {
                id: 'summer-splash-2023',
                name: 'Summer Splash',
                category: 'Pool Party',
                image: '../assets/t&b.jpg',
                date: 'August 15, 2023',
                dateObj: new Date('2023-08-15'),
                location: 'Aqua Safari Resort',
                status: 'cancelled',
                reason: 'Weather conditions - Heavy rainfall forecasted',
                createdAt: 'July 20, 2023'
            },
            // Draft Events
            {
                id: 'valentine-special',
                name: 'Valentine Special',
                category: 'Concert',
                image: '../assets/hero.png',
                date: 'February 14, 2025',
                dateObj: new Date('2025-02-14'),
                location: 'TBD',
                status: 'draft',
                reason: 'Incomplete - Missing venue confirmation',
                createdAt: 'November 15, 2024'
            },
            {
                id: 'easter-fest',
                name: 'Easter Festival 2025',
                category: 'Festival',
                image: '../assets/hero.png',
                date: 'April 20, 2025',
                dateObj: new Date('2025-04-20'),
                location: 'TBD',
                status: 'draft',
                reason: 'Incomplete - Artist lineup pending',
                createdAt: 'November 10, 2024'
            }
        ];

        // Apply filters
        let filteredEvents = [...allEvents];

        // Status filter
        if (filters.status && filters.status !== 'all') {
            filteredEvents = filteredEvents.filter(e => e.status === filters.status);
        }

        // Search filter
        if (filters.search) {
            const searchLower = filters.search.toLowerCase();
            filteredEvents = filteredEvents.filter(e =>
                e.name.toLowerCase().includes(searchLower) ||
                e.category.toLowerCase().includes(searchLower) ||
                e.location.toLowerCase().includes(searchLower)
            );
        }

        // Sort
        if (filters.sort) {
            switch (filters.sort) {
                case 'date-desc':
                    filteredEvents.sort((a, b) => b.dateObj - a.dateObj);
                    break;
                case 'date-asc':
                    filteredEvents.sort((a, b) => a.dateObj - b.dateObj);
                    break;
                case 'revenue-desc':
                    filteredEvents.sort((a, b) => (b.revenue || 0) - (a.revenue || 0));
                    break;
                case 'revenue-asc':
                    filteredEvents.sort((a, b) => (a.revenue || 0) - (b.revenue || 0));
                    break;
                case 'tickets-desc':
                    filteredEvents.sort((a, b) => (b.ticketsSold || 0) - (a.ticketsSold || 0));
                    break;
            }
        }

        // Categorize events
        const activeEvents = filteredEvents.filter(e => e.status === 'active');
        const pastEvents = filteredEvents.filter(e => e.status === 'completed');
        const otherEvents = filteredEvents.filter(e => e.status === 'cancelled' || e.status === 'draft');

        return Promise.resolve({
            active: activeEvents,
            past: pastEvents,
            other: otherEvents,
            total: filteredEvents.length
        });
    }

    _getMockEventDetails(eventId) {
        return this._getMockManagerEvents().then(data => {
            const allEvents = [...data.active, ...data.past, ...data.other];
            const event = allEvents.find(e => e.id === eventId);
            if (!event) throw new Error('Event not found');
            return event;
        });
    }

    _getMockSummaryStats() {
        return Promise.resolve({
            totalEvents: 10,
            totalTicketsSold: 9520,
            totalRevenue: 628200,
            avgRating: 4.5
        });
    }

    _getMockCancelEvent(eventId, reason) {
        console.log(`Cancelling event ${eventId}: ${reason}`);
        return Promise.resolve({ success: true });
    }

    _getMockDeleteEvent(eventId) {
        console.log(`Deleting event ${eventId}`);
        return Promise.resolve({ success: true });
    }

    _getMockExportData(format) {
        const csvContent = `Event Name,Date,Tickets Sold,Revenue,Status,Rating
Ashchella 2024,Dec 25 2024,850,42500,Active,-
Y2K Neon Party,Dec 28 2024,320,19200,Active,-
New Year Bash 2025,Dec 31 2024,1200,96000,Active,-
Tidal Rave 2023,Dec 20 2023,1500,112500,Completed,4.5
Global Football Festival,Dec 1 2023,2200,88000,Completed,4.2
Rapperholic 2023,Nov 28 2023,3000,225000,Completed,4.8
iMullar Experience,Nov 20 2023,450,45000,Completed,4.6`;

        const blob = new Blob([csvContent], { type: 'text/csv' });
        return Promise.resolve(blob);
    }
}

// ============================================================================
// HISTORY CONTROLLER
// ============================================================================

class HistoryController {
    constructor() {
        this.dataService = new HistoryDataService();
        this.currentFilters = {
            status: 'all',
            sort: 'date-desc',
            search: ''
        };
        this.currentEventId = null;
        this.pendingAction = null;

        this.init();
    }

    async init() {
        try {
            // Parse URL parameters for initial filters
            this.parseUrlParams();
            await this.loadSummaryStats();
            await this.loadEvents();
            this.setupEventListeners();
        } catch (error) {
            console.error('History initialization failed:', error);
            this.showToast('Failed to load history data', 'error');
        }
    }

    setupEventListeners() {
        // Status filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.currentFilters.status = e.target.value;
                this.loadEvents();
            });
        }

        // Sort filter
        const sortBy = document.getElementById('sortBy');
        if (sortBy) {
            sortBy.addEventListener('change', (e) => {
                this.currentFilters.sort = e.target.value;
                this.loadEvents();
            });
        }

        // Search input
        const searchInput = document.getElementById('eventSearch');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.currentFilters.search = e.target.value;
                    this.loadEvents();
                }, 300);
            });
        }

        // Export button
        const exportBtn = document.getElementById('exportHistoryBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.handleExport());
        }

        // Collapsible section
        const otherHeader = document.getElementById('otherEventsHeader');
        if (otherHeader) {
            otherHeader.addEventListener('click', () => {
                const section = otherHeader.closest('.collapsible');
                section.classList.toggle('open');
            });
        }

        // Modal close handlers
        this.setupModalHandlers();
    }

    setupModalHandlers() {
        // Event details modal
        const detailsModal = document.getElementById('event-details-modal');
        if (detailsModal) {
            const overlay = detailsModal.querySelector('.modal-overlay');
            const closeBtn = detailsModal.querySelector('.modal-close');

            overlay?.addEventListener('click', () => this.closeModal('event-details-modal'));
            closeBtn?.addEventListener('click', () => this.closeModal('event-details-modal'));

            document.getElementById('modalEditBtn')?.addEventListener('click', () => {
                if (this.currentEventId) {
                    this.editEvent(this.currentEventId);
                }
            });

            document.getElementById('modalViewAnalytics')?.addEventListener('click', () => {
                if (this.currentEventId) {
                    // Store event ID for analytics page
                    sessionStorage.setItem('analyticsEventId', this.currentEventId);
                    // Navigate to event analytics page
                    window.location.href = `event_analytics.html?id=${this.currentEventId}`;
                }
            });
        }

        // Confirm modal
        const confirmModal = document.getElementById('confirm-modal');
        if (confirmModal) {
            const overlay = confirmModal.querySelector('.modal-overlay');
            const closeBtn = confirmModal.querySelector('.modal-close');
            const cancelBtn = document.getElementById('confirmCancelBtn');
            const actionBtn = document.getElementById('confirmActionBtn');

            overlay?.addEventListener('click', () => this.closeModal('confirm-modal'));
            closeBtn?.addEventListener('click', () => this.closeModal('confirm-modal'));
            cancelBtn?.addEventListener('click', () => this.closeModal('confirm-modal'));
            actionBtn?.addEventListener('click', () => this.executePendingAction());
        }
    }

    async loadSummaryStats() {
        try {
            const stats = await this.dataService.getSummaryStats();

            document.getElementById('totalEvents').textContent = stats.totalEvents;
            document.getElementById('totalTicketsSold').textContent = stats.totalTicketsSold.toLocaleString();
            document.getElementById('totalRevenue').textContent = `GHC ${stats.totalRevenue.toLocaleString()}`;
            document.getElementById('avgRating').textContent = stats.avgRating.toFixed(1);
        } catch (error) {
            console.error('Error loading summary stats:', error);
        }
    }

    async loadEvents() {
        try {
            const data = await this.dataService.getManagerEvents(this.currentFilters);

            this.renderActiveEvents(data.active);
            this.renderPastEvents(data.past);
            this.renderOtherEvents(data.other);

            // Update counts
            document.getElementById('activeCount').textContent = `${data.active.length} event${data.active.length !== 1 ? 's' : ''}`;
            document.getElementById('pastCount').textContent = `${data.past.length} event${data.past.length !== 1 ? 's' : ''}`;
            document.getElementById('otherCount').textContent = `${data.other.length} event${data.other.length !== 1 ? 's' : ''}`;
        } catch (error) {
            console.error('Error loading events:', error);
            this.showToast('Failed to load events', 'error');
        }
    }

    renderActiveEvents(events) {
        const tbody = document.getElementById('activeEventsBody');
        if (!tbody) return;

        if (events.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="empty-state">
                        <p>No active events</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = events.map(event => `
            <tr data-event-id="${event.id}">
                <td>
                    <div class="event-cell">
                        <img src="${event.image}" alt="${event.name}" class="event-thumbnail">
                        <div class="event-info">
                            <span class="event-name">${this.escapeHtml(event.name)}</span>
                            <span class="event-category">${event.category}</span>
                        </div>
                    </div>
                </td>
                <td>${event.date}</td>
                <td>${event.ticketsSold.toLocaleString()} / ${event.totalTickets.toLocaleString()}</td>
                <td>GHC ${event.revenue.toLocaleString()}</td>
                <td><span class="status-badge active">Active</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn primary" onclick="historyController.viewEventDetails('${event.id}')">View</button>
                        <button class="action-btn" onclick="historyController.editEvent('${event.id}')">Edit</button>
                        <button class="action-btn danger" onclick="historyController.confirmCancelEvent('${event.id}', '${this.escapeHtml(event.name)}')">Cancel</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderPastEvents(events) {
        const tbody = document.getElementById('pastEventsBody');
        if (!tbody) return;

        if (events.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="empty-state">
                        <p>No past events</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = events.map(event => `
            <tr data-event-id="${event.id}">
                <td>
                    <div class="event-cell">
                        <img src="${event.image}" alt="${event.name}" class="event-thumbnail">
                        <div class="event-info">
                            <span class="event-name">${this.escapeHtml(event.name)}</span>
                            <span class="event-category">${event.category}</span>
                        </div>
                    </div>
                </td>
                <td>${event.date}</td>
                <td>${event.ticketsSold.toLocaleString()} / ${event.totalTickets.toLocaleString()}</td>
                <td>GHC ${event.revenue.toLocaleString()}</td>
                <td>
                    <div class="rating-display">
                        <div class="rating-stars">${this.renderStars(event.rating)}</div>
                        <span class="rating-value">${event.rating.toFixed(1)}</span>
                    </div>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn primary" onclick="historyController.viewEventDetails('${event.id}')">View</button>
                        <button class="action-btn" onclick="historyController.duplicateEvent('${event.id}')">Duplicate</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderOtherEvents(events) {
        const tbody = document.getElementById('otherEventsBody');
        if (!tbody) return;

        if (events.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="empty-state">
                        <p>No cancelled or draft events</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = events.map(event => `
            <tr data-event-id="${event.id}">
                <td>
                    <div class="event-cell">
                        <img src="${event.image}" alt="${event.name}" class="event-thumbnail">
                        <div class="event-info">
                            <span class="event-name">${this.escapeHtml(event.name)}</span>
                            <span class="event-category">${event.category}</span>
                        </div>
                    </div>
                </td>
                <td>${event.createdAt || event.date}</td>
                <td><span class="status-badge ${event.status}">${this.capitalizeFirst(event.status)}</span></td>
                <td>${event.reason || '-'}</td>
                <td>
                    <div class="action-buttons">
                        ${event.status === 'draft' ? `
                            <button class="action-btn primary" onclick="historyController.editEvent('${event.id}')">Continue</button>
                            <button class="action-btn danger" onclick="historyController.confirmDeleteEvent('${event.id}', '${this.escapeHtml(event.name)}')">Delete</button>
                        ` : `
                            <button class="action-btn" onclick="historyController.duplicateEvent('${event.id}')">Recreate</button>
                        `}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async viewEventDetails(eventId) {
        try {
            this.currentEventId = eventId; // Store for modal button actions
            const event = await this.dataService.getEventDetails(eventId);
            this.showEventDetailsModal(event);
        } catch (error) {
            console.error('Error loading event details:', error);
            this.showToast('Failed to load event details', 'error');
        }
    }

    showEventDetailsModal(event) {
        document.getElementById('modalEventImage').src = event.image;
        document.getElementById('modalEventImage').alt = event.name;
        document.getElementById('modalEventName').textContent = event.name;

        const statusBadge = document.getElementById('modalEventStatus');
        statusBadge.textContent = this.capitalizeFirst(event.status);
        statusBadge.className = `modal-badge ${event.status}`;

        document.getElementById('modalEventDate').textContent = event.date;
        document.getElementById('modalEventLocation').textContent = event.location;
        document.getElementById('modalTicketsSold').textContent = `${event.ticketsSold?.toLocaleString() || 0} / ${event.totalTickets?.toLocaleString() || 0}`;
        document.getElementById('modalRevenue').textContent = `GHC ${event.revenue?.toLocaleString() || 0}`;
        document.getElementById('modalRating').textContent = event.rating ? `${event.rating}/5` : 'N/A';
        document.getElementById('modalCheckins').textContent = event.checkins?.toLocaleString() || 'N/A';

        // Ticket breakdown
        const ticketTypesContainer = document.getElementById('modalTicketTypes');
        if (event.ticketTypes && event.ticketTypes.length > 0) {
            ticketTypesContainer.innerHTML = event.ticketTypes.map(type => `
                <div class="ticket-type-row">
                    <span class="ticket-type-name">${type.name}</span>
                    <div class="ticket-type-stats">
                        <span>${type.sold}</span> / ${type.total} sold
                        &bull;
                        GHC <span>${type.price}</span> each
                    </div>
                </div>
            `).join('');
        } else {
            ticketTypesContainer.innerHTML = '<p style="color: #666; font-size: 13px;">No ticket data available</p>';
        }

        // Reviews
        const reviewsContainer = document.getElementById('modalReviews');
        if (event.reviews && event.reviews.length > 0) {
            reviewsContainer.innerHTML = event.reviews.map(review => `
                <div class="review-item">
                    <div class="review-header">
                        <span class="reviewer-name">${this.escapeHtml(review.name)}</span>
                        <div class="review-rating">${this.renderStars(review.rating)}</div>
                    </div>
                    <p class="review-text">${this.escapeHtml(review.text)}</p>
                </div>
            `).join('');
        } else {
            reviewsContainer.innerHTML = '<p style="color: #666; font-size: 13px;">No reviews yet</p>';
        }

        this.openModal('event-details-modal');
    }

    editEvent(eventId) {
        // Store the event data in sessionStorage for the create event page to load
        this.dataService.getEventDetails(eventId).then(event => {
            sessionStorage.setItem('editEventData', JSON.stringify(event));
            window.location.href = `../../views/create_event.html?edit=${eventId}`;
        }).catch(() => {
            window.location.href = `../../views/create_event.html?edit=${eventId}`;
        });
    }

    duplicateEvent(eventId) {
        // Store the event data in sessionStorage for the create event page to load as duplicate
        this.dataService.getEventDetails(eventId).then(event => {
            // Remove id and status for duplication
            const duplicateData = { ...event };
            delete duplicateData.id;
            delete duplicateData.status;
            duplicateData.name = `${event.name} (Copy)`;
            sessionStorage.setItem('duplicateEventData', JSON.stringify(duplicateData));
            window.location.href = `../../views/create_event.html?duplicate=${eventId}`;
        }).catch(() => {
            window.location.href = `../../views/create_event.html?duplicate=${eventId}`;
        });
    }

    parseUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);

        // Check for status filter
        const status = urlParams.get('status');
        if (status && ['all', 'active', 'completed', 'cancelled', 'draft'].includes(status)) {
            this.currentFilters.status = status;
            const statusFilter = document.getElementById('statusFilter');
            if (statusFilter) statusFilter.value = status;
        }

        // Check for sort filter
        const sort = urlParams.get('sort');
        if (sort && ['date-desc', 'date-asc', 'revenue-desc', 'revenue-asc', 'tickets-desc'].includes(sort)) {
            this.currentFilters.sort = sort;
            const sortBy = document.getElementById('sortBy');
            if (sortBy) sortBy.value = sort;
        }

        // Check for search query
        const search = urlParams.get('search');
        if (search) {
            this.currentFilters.search = search;
            const searchInput = document.getElementById('eventSearch');
            if (searchInput) searchInput.value = search;
        }
    }

    confirmCancelEvent(eventId, eventName) {
        this.currentEventId = eventId;
        this.pendingAction = 'cancel';

        document.getElementById('confirmTitle').textContent = 'Cancel Event';
        document.getElementById('confirmMessage').innerHTML = `
            Are you sure you want to cancel <strong>${eventName}</strong>?<br><br>
            <span style="color: #f44336;">This will notify all ticket holders and process refunds.</span>
        `;
        document.getElementById('confirmActionBtn').textContent = 'Yes, Cancel Event';
        document.getElementById('confirmActionBtn').className = 'btn-danger';

        this.openModal('confirm-modal');
    }

    confirmDeleteEvent(eventId, eventName) {
        this.currentEventId = eventId;
        this.pendingAction = 'delete';

        document.getElementById('confirmTitle').textContent = 'Delete Draft';
        document.getElementById('confirmMessage').innerHTML = `
            Are you sure you want to delete <strong>${eventName}</strong>?<br><br>
            <span style="color: #888;">This action cannot be undone.</span>
        `;
        document.getElementById('confirmActionBtn').textContent = 'Yes, Delete';
        document.getElementById('confirmActionBtn').className = 'btn-danger';

        this.openModal('confirm-modal');
    }

    async executePendingAction() {
        if (!this.currentEventId || !this.pendingAction) return;

        try {
            if (this.pendingAction === 'cancel') {
                await this.dataService.cancelEvent(this.currentEventId, 'Cancelled by organizer');
                this.showToast('Event cancelled successfully', 'success');
            } else if (this.pendingAction === 'delete') {
                await this.dataService.deleteEvent(this.currentEventId);
                this.showToast('Draft deleted successfully', 'success');
            }

            this.closeModal('confirm-modal');
            await this.loadEvents();
            await this.loadSummaryStats();
        } catch (error) {
            console.error('Action failed:', error);
            this.showToast('Action failed. Please try again.', 'error');
        } finally {
            this.currentEventId = null;
            this.pendingAction = null;
        }
    }

    async handleExport() {
        try {
            this.showToast('Preparing export...');
            const blob = await this.dataService.exportHistory('csv');

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `event-history-${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            this.showToast('Export downloaded!', 'success');
        } catch (error) {
            console.error('Export failed:', error);
            this.showToast('Export failed. Please try again.', 'error');
        }
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    renderStars(rating) {
        let stars = '';
        const fullStars = Math.floor(rating || 0);
        for (let i = 1; i <= 5; i++) {
            stars += `<span class="star ${i <= fullStars ? 'filled' : ''}">★</span>`;
        }
        return stars;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    showToast(message, type = 'info') {
        const existingToast = document.querySelector('.toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 3000);
    }
}

// ============================================================================
// INITIALIZE
// ============================================================================

let historyController;

document.addEventListener('DOMContentLoaded', () => {
    historyController = new HistoryController();
});
