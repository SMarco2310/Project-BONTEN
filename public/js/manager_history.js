

class HistoryDataService {

    constructor() {

        this.apiBaseUrl = '../api/manager_events.php';

    }

    async getManagerEvents(filters = {}) {

        const params = new URLSearchParams({

            action: 'list',
            ...filters

        });


        const response = await fetch(`${this.apiBaseUrl}?${params}`);

        if (!response.ok) throw new Error('Failed to fetch events');


        return response.json();


    }

    async getEventDetails(eventId) {

        const realId = eventId.replace('event-', '');
        const response = await fetch(`${this.apiBaseUrl}?action=details&id=${realId}`);


        if (!response.ok) throw new Error('Failed to fetch event details');

        return response.json();
    }

    async getSummaryStats() {
        return Promise.resolve({});


    }

    async cancelEvent(eventId, reason) {

        const realId = eventId.replace('event-', '');

        const response = await fetch(`${this.apiBaseUrl}?action=cancel`, {

            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: realId, reason })
        });

        if (!response.ok) throw new Error('Failed to cancel event');


        return response.json();

    }

    async deleteEvent(eventId) {
        const realId = eventId.replace('event-', '');

        const response = await fetch(`${this.apiBaseUrl}?action=delete`, {

            method: 'POST',


            headers: { 'Content-Type': 'application/json' },

            body: JSON.stringify({ event_id: realId })
        });

        if (!response.ok) throw new Error('Failed to delete event');

        return response.json();

    }

    async exportHistory(format = 'csv') {

        const params = new URLSearchParams({

            action: 'export',
            format: format

        });

        const response = await fetch(`${this.apiBaseUrl}?${params}`);

        if (!response.ok) throw new Error('Failed to export data');

        return response.blob();

    }

    _getMockExportData(format) {

        const csvContent = `Event Name,Date,Tickets Sold,Revenue,Status,Rating
Export Data,N/A,0,0,N/A,N/A`;

        const blob = new Blob([csvContent], { type: 'text/csv' });
        return Promise.resolve(blob);


    }

}

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

            this.parseUrlParams();

            await this.loadEvents();
            this.setupEventListeners();

        } catch (error) {

            console.error('History initialization failed:', error);

            this.showToast('Failed to load history data', 'error');
        }

    }

    setupEventListeners() {


        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {

            statusFilter.addEventListener('change', (e) => {

                this.currentFilters.status = e.target.value;
                this.loadEvents();

            });

        }

        const sortBy = document.getElementById('sortBy');
        if (sortBy) {
            sortBy.addEventListener('change', (e) => {

                this.currentFilters.sort = e.target.value;
                this.loadEvents();


            });
        }

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

        const exportBtn = document.getElementById('exportHistoryBtn');


        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.handleExport());

        }

        const otherHeader = document.getElementById('otherEventsHeader');
        if (otherHeader) {
            otherHeader.addEventListener('click', () => {

                const section = otherHeader.closest('.collapsible');
                section.classList.toggle('open');

            });


        }

        this.setupModalHandlers();

    }

    setupModalHandlers() {
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

                    sessionStorage.setItem('analyticsEventId', this.currentEventId);

                    window.location.href = `event_analytics.html?id=${this.currentEventId}`;
                }

            });

        }

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

    async loadEvents() {


        try {
            const data = await this.dataService.getManagerEvents(this.currentFilters);

            this.renderActiveEvents(data.active);


            this.renderPastEvents(data.past);

            this.renderOtherEvents(data.other);

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


                        <span class="rating-value">${event.rating ? event.rating.toFixed(1) : 'N/A'}</span>

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
            this.currentEventId = eventId;

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
        this.dataService.getEventDetails(eventId).then(event => {

            sessionStorage.setItem('editEventData', JSON.stringify(event));

            window.location.href = `create_event.php?edit=${eventId}`;
        }).catch(() => {
            window.location.href = `create_event.php?edit=${eventId}`;
        });
    }

    duplicateEvent(eventId) {


        this.dataService.getEventDetails(eventId).then(event => {

            const duplicateData = { ...event };

            delete duplicateData.id;
            delete duplicateData.status;
            duplicateData.name = `${event.name} (Copy)`;

            sessionStorage.setItem('duplicateEventData', JSON.stringify(duplicateData));


            window.location.href = `create_event.php?duplicate=${eventId}`;

        }).catch(() => {

            window.location.href = `create_event.php?duplicate=${eventId}`;

        });

    }

    parseUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);

        const status = urlParams.get('status');
        if (status && ['all', 'active', 'completed', 'cancelled', 'draft'].includes(status)) {


            this.currentFilters.status = status;

            const statusFilter = document.getElementById('statusFilter');
            if (statusFilter) statusFilter.value = status;

        }

        const sort = urlParams.get('sort');

        if (sort && ['date-desc', 'date-asc', 'revenue-desc', 'revenue-asc', 'tickets-desc'].includes(sort)) {


            this.currentFilters.sort = sort;


            const sortBy = document.getElementById('sortBy');


            if (sortBy) sortBy.value = sort;

        }

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

            stars += `<span class="star ${i <= fullStars ? 'filled' : ''}"></span>`;

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

let historyController;

document.addEventListener('DOMContentLoaded', () => {


    historyController = new HistoryController();


});
