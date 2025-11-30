
class EventDataService {
    constructor() {
        this.apiBaseUrl = '/api';
        this.useMockData = true;
    }

    async createEvent(eventData) {
        if (this.useMockData) return this._getMockCreateEvent(eventData);
        const response = await fetch(`${this.apiBaseUrl}/events`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventData)
        });
        if (!response.ok) throw new Error('Failed to create event');
        return response.json();
    }

    async saveDraft(eventData) {
        if (this.useMockData) return this._getMockSaveDraft(eventData);
        const response = await fetch(`${this.apiBaseUrl}/events/draft`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventData)
        });
        if (!response.ok) throw new Error('Failed to save draft');
        return response.json();
    }

    async uploadImage(file) {
        if (this.useMockData) return this._getMockUploadImage(file);
        const formData = new FormData();
        formData.append('image', file);
        const response = await fetch(`${this.apiBaseUrl}/upload/image`, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) throw new Error('Failed to upload image');
        return response.json();
    }

    async getEvent(eventId) {
        if (this.useMockData) return this._getMockGetEvent(eventId);
        const response = await fetch(`${this.apiBaseUrl}/events/${eventId}`);
        if (!response.ok) throw new Error('Failed to fetch event');
        return response.json();
    }

    async updateEvent(eventId, eventData) {
        if (this.useMockData) return this._getMockUpdateEvent(eventId, eventData);
        const response = await fetch(`${this.apiBaseUrl}/events/${eventId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventData)
        });
        if (!response.ok) throw new Error('Failed to update event');
        return response.json();
    }

    // Mock implementations
    _getMockCreateEvent(eventData) {
        const eventId = 'evt_' + Date.now();
        console.log('Creating event:', { id: eventId, ...eventData });

        // Store in sessionStorage for demo purposes
        const events = JSON.parse(sessionStorage.getItem('createdEvents') || '[]');
        events.push({ id: eventId, ...eventData, status: 'active', createdAt: new Date().toISOString() });
        sessionStorage.setItem('createdEvents', JSON.stringify(events));

        return Promise.resolve({
            success: true,
            eventId,
            message: 'Event created successfully'
        });
    }

    _getMockSaveDraft(eventData) {
        const draftId = 'draft_' + Date.now();
        console.log('Saving draft:', { id: draftId, ...eventData });

        
        const drafts = JSON.parse(sessionStorage.getItem('eventDrafts') || '[]');
        drafts.push({ id: draftId, ...eventData, status: 'draft', savedAt: new Date().toISOString() });
        sessionStorage.setItem('eventDrafts', JSON.stringify(drafts));

        return Promise.resolve({
            success: true,
            draftId,
            message: 'Draft saved successfully'
        });
    }

    _getMockUploadImage(file) {
        
        const url = URL.createObjectURL(file);
        return Promise.resolve({
            success: true,
            url,
            filename: file.name
        });
    }

    _getMockGetEvent(eventId) {
        
        const events = JSON.parse(sessionStorage.getItem('createdEvents') || '[]');
        const event = events.find(e => e.id === eventId);
        return Promise.resolve(event || null);
    }

    _getMockUpdateEvent(eventId, eventData) {
        console.log('Updating event:', { id: eventId, ...eventData });

        
        const events = JSON.parse(sessionStorage.getItem('createdEvents') || '[]');
        const index = events.findIndex(e => e.id === eventId);

        if (index !== -1) {
            events[index] = { ...events[index], ...eventData, updatedAt: new Date().toISOString() };
            sessionStorage.setItem('createdEvents', JSON.stringify(events));
        }

        return Promise.resolve({
            success: true,
            eventId,
            message: 'Event updated successfully'
        });
    }

}



class CreateEventController {
    constructor() {
        this.dataService = new EventDataService();
        this.currentStep = 1;
        this.totalSteps = 4;
        this.formData = {};
        this.tags = [];
        this.tickets = [];
        this.ticketCounter = 0;
        this.imageFile = null;
        this.imagePreviewUrl = null;
        this.hasUnsavedChanges = false;

        // Edit/Duplicate mode flags
        this.isEditMode = false;
        this.isDuplicateMode = false;
        this.editingEventId = null;

        this.init();
    }

    init() {
        this.setupStepNavigation();
        this.setupFormInputs();
        this.setupImageUpload();
        this.setupTagsInput();
        this.setupTicketSystem();
        this.setupEventTypeToggle();
        this.setupModals();
        this.setupBeforeUnload();
        this.loadDraftIfExists();
    }


    setupStepNavigation() {
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const publishBtn = document.getElementById('publishBtn');
        const saveDraftBtn = document.getElementById('saveDraftBtn');

        nextBtn?.addEventListener('click', () => this.nextStep());
        prevBtn?.addEventListener('click', () => this.prevStep());
        publishBtn?.addEventListener('click', () => this.publishEvent());
        saveDraftBtn?.addEventListener('click', () => this.saveDraft());
    }

    nextStep() {
        if (!this.validateCurrentStep()) return;

        this.collectStepData();

        if (this.currentStep < this.totalSteps) {
            this.currentStep++;
            this.updateStepUI();

            if (this.currentStep === this.totalSteps) {
                this.populateReview();
            }
        }
    }

    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStepUI();
        }
    }

    updateStepUI() {
        // Update form steps
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active');
            if (parseInt(step.dataset.step) === this.currentStep) {
                step.classList.add('active');
            }
        });

        // Update progress steps
        document.querySelectorAll('.progress-steps .step').forEach((step, index) => {
            step.classList.remove('active', 'completed');
            if (index + 1 === this.currentStep) {
                step.classList.add('active');
            } else if (index + 1 < this.currentStep) {
                step.classList.add('completed');
            }
        });

        // Update step lines
        document.querySelectorAll('.step-line').forEach((line, index) => {
            line.classList.toggle('active', index < this.currentStep - 1);
        });

        // Update progress bar
        const progressFill = document.getElementById('progressFill');
        if (progressFill) {
            progressFill.style.width = `${(this.currentStep / this.totalSteps) * 100}%`;
        }

        // Update navigation buttons
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const publishBtn = document.getElementById('publishBtn');

        if (prevBtn) prevBtn.style.display = this.currentStep > 1 ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = this.currentStep < this.totalSteps ? 'flex' : 'none';
        if (publishBtn) publishBtn.style.display = this.currentStep === this.totalSteps ? 'flex' : 'none';

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }



    validateCurrentStep() {
        const validations = {
            1: () => this.validateStep1(),
            2: () => this.validateStep2(),
            3: () => this.validateStep3(),
            4: () => true
        };

        return validations[this.currentStep]?.() ?? true;
    }

    validateStep1() {
        let isValid = true;
        const errors = [];

        const eventName = document.getElementById('eventName');
        const eventCategory = document.getElementById('eventCategory');
        const eventDescription = document.getElementById('eventDescription');

        
        this.clearErrors();

        if (!eventName?.value.trim()) {
            this.showFieldError(eventName, 'Event name is required');
            errors.push('Event name');
            isValid = false;
        }

        if (!eventCategory?.value) {
            this.showFieldError(eventCategory, 'Please select a category');
            errors.push('Category');
            isValid = false;
        }

        if (!eventDescription?.value.trim()) {
            this.showFieldError(eventDescription, 'Description is required');
            errors.push('Description');
            isValid = false;
        } else if (eventDescription.value.trim().length < 50) {
            this.showFieldError(eventDescription, 'Description must be at least 50 characters');
            errors.push('Description length');
            isValid = false;
        }


        if (!this.imageFile && !this.imagePreviewUrl) {
            this.showToast('Please upload an event image', 'error');
            isValid = false;
        }

        if (!isValid) {
            this.showToast('Please fill in all required fields', 'error');
        }

        return isValid;
    }

    validateStep2() {
        let isValid = true;

        this.clearErrors();

        const startDate = document.getElementById('eventStartDate');
        const startTime = document.getElementById('eventStartTime');
        const endDate = document.getElementById('eventEndDate');
        const endTime = document.getElementById('eventEndTime');
        const eventType = document.querySelector('input[name="eventType"]:checked')?.value;

        if (!startDate?.value) {
            this.showFieldError(startDate, 'Start date is required');
            isValid = false;
        }

        if (!startTime?.value) {
            this.showFieldError(startTime, 'Start time is required');
            isValid = false;
        }


        if (!endDate?.value) {
            this.showFieldError(endDate, 'End date is required');
            isValid = false;
        }

        if (!endTime?.value) {
            this.showFieldError(endTime, 'End time is required');
            isValid = false;
        }

        // Validate end date/time is after start
        if (startDate?.value && endDate?.value && startTime?.value && endTime?.value) {
            const start = new Date(`${startDate.value}T${startTime.value}`);

            const end = new Date(`${endDate.value}T${endTime.value}`);

            if (end <= start) {
                this.showFieldError(endDate, 'End date/time must be after start');
                isValid = false;
            }
        }

        
        if (eventType === 'in-person' || eventType === 'hybrid') {
            const venue = document.getElementById('eventVenue');

            const city = document.getElementById('eventCity');

            if (!venue?.value.trim()) {
                this.showFieldError(venue, 'Venue name is required');
                isValid = false;
            }

            if (!city?.value.trim()) {
                this.showFieldError(city, 'City is required');
                isValid = false;
            }

        }

        if (!isValid) {
            this.showToast('Please fill in all required fields', 'error');
        }

        return isValid;
    }

    validateStep3() {
        const ticketType = document.querySelector('input[name="ticketType"]:checked')?.value;

        if (ticketType === 'paid' && this.tickets.length === 0) {
            this.showToast('Please add at least one ticket type', 'error');
            return false;
        }

        if (ticketType === 'paid') {
            for (const ticket of this.tickets) {
                if (!ticket.name || !ticket.price || !ticket.quantity) {
                    this.showToast('Please fill in all ticket details', 'error');
                    return false;
                }

            }
        }

        return true;
    }

    showFieldError(field, message) {
        if (!field) return;

        const formGroup = field.closest('.form-group');

        if (formGroup) {
            formGroup.classList.add('error');

            let errorEl = formGroup.querySelector('.error-message');
            if (!errorEl) {
                errorEl = document.createElement('span');
                errorEl.className = 'error-message';
                formGroup.appendChild(errorEl);
            }
            errorEl.textContent = message;
        }

        field.focus();
    }

    clearErrors() {
        document.querySelectorAll('.form-group.error').forEach(group => {
            group.classList.remove('error');
            const errorEl = group.querySelector('.error-message');
            if (errorEl) errorEl.remove();
        });
    }


    collectStepData() {
        switch (this.currentStep) {
            case 1:
                this.formData.name = document.getElementById('eventName')?.value.trim();
                this.formData.category = document.getElementById('eventCategory')?.value;
                this.formData.visibility = document.getElementById('eventVisibility')?.value;
                this.formData.description = document.getElementById('eventDescription')?.value.trim();
                this.formData.tags = [...this.tags];
                this.formData.image = this.imagePreviewUrl;
                break;

            case 2:
                this.formData.startDate = document.getElementById('eventStartDate')?.value;
                this.formData.startTime = document.getElementById('eventStartTime')?.value;
                this.formData.endDate = document.getElementById('eventEndDate')?.value;
                this.formData.endTime = document.getElementById('eventEndTime')?.value;
                this.formData.timezone = document.getElementById('eventTimezone')?.value;
                this.formData.eventType = document.querySelector('input[name="eventType"]:checked')?.value;
                this.formData.venue = document.getElementById('eventVenue')?.value.trim();
                this.formData.address = document.getElementById('eventAddress')?.value.trim();
                this.formData.city = document.getElementById('eventCity')?.value.trim();
                this.formData.region = document.getElementById('eventRegion')?.value;
                this.formData.platform = document.getElementById('eventPlatform')?.value;
                this.formData.streamUrl = document.getElementById('eventStreamUrl')?.value.trim();
                this.formData.capacity = document.getElementById('eventCapacity')?.value;
                break;

            case 3:
                this.formData.ticketType = document.querySelector('input[name="ticketType"]:checked')?.value;
                if (this.formData.ticketType === 'free') {
                    this.formData.freeQuantity = document.getElementById('freeTicketQuantity')?.value;
                } else {
                    this.formData.tickets = [...this.tickets];
                }
                this.formData.requireApproval = document.getElementById('requireApproval')?.checked;
                this.formData.collectPhone = document.getElementById('collectPhone')?.checked;
                this.formData.allowRefunds = document.getElementById('allowRefunds')?.checked;
                break;
        }

        this.hasUnsavedChanges = true;
    }


    setupFormInputs() {
       
        const eventName = document.getElementById('eventName');
        const eventDescription = document.getElementById('eventDescription');
        const nameCount = document.getElementById('nameCount');
        const descCount = document.getElementById('descCount');

        eventName?.addEventListener('input', () => {
            if (nameCount) nameCount.textContent = eventName.value.length;
            if (eventName.value.length > 100) {
                eventName.value = eventName.value.substring(0, 100);
            }
            this.hasUnsavedChanges = true;
        });

        eventDescription?.addEventListener('input', () => {
            if (descCount) descCount.textContent = eventDescription.value.length;
            if (eventDescription.value.length > 2000) {
                eventDescription.value = eventDescription.value.substring(0, 2000);
            }
            this.hasUnsavedChanges = true;
        });

        
        const today = new Date().toISOString().split('T')[0];
        const startDate = document.getElementById('eventStartDate');
        const endDate = document.getElementById('eventEndDate');

        if (startDate) startDate.min = today;
        if (endDate) endDate.min = today;

        // Sync end date with start date
        startDate?.addEventListener('change', () => {
            if (endDate && startDate.value) {
                endDate.min = startDate.value;
                if (!endDate.value || endDate.value < startDate.value) {
                    endDate.value = startDate.value;
                }
            }
        });
    }



    setupImageUpload() {
        const imagePreview = document.getElementById('imagePreview');
        const imageInput = document.getElementById('eventImage');

        imagePreview?.addEventListener('click', () => {
            imageInput?.click();
        });

        imageInput?.addEventListener('change', async (e) => {
            const file = e.target.files?.[0];
            if (!file) return;

            
            if (!file.type.startsWith('image/')) {
                this.showToast('Please select an image file', 'error');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                this.showToast('Image must be less than 5MB', 'error');
                return;
            }

            this.imageFile = file;

            
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreviewUrl = e.target.result;
                imagePreview.style.backgroundImage = `url(${e.target.result})`;
                imagePreview.classList.add('has-image');

               
                if (!imagePreview.querySelector('.remove-image')) {
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'remove-image';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.removeImage();
                    });
                    imagePreview.appendChild(removeBtn);
                }
            };
            reader.readAsDataURL(file);

            this.hasUnsavedChanges = true;
        });

        
        imagePreview?.addEventListener('dragover', (e) => {
            e.preventDefault();
            imagePreview.style.borderColor = 'var(--secondary-color)';
        });


        imagePreview?.addEventListener('dragleave', () => {
            if (!imagePreview.classList.contains('has-image')) {
                imagePreview.style.borderColor = '#333';
            }
        });

        imagePreview?.addEventListener('drop', (e) => {
            e.preventDefault();
            imagePreview.style.borderColor = '#333';

            const file = e.dataTransfer.files?.[0];
            if (file && imageInput) {
                const dt = new DataTransfer();

                dt.items.add(file);
                imageInput.files = dt.files;
                imageInput.dispatchEvent(new Event('change'));
            }
        });
    }

    removeImage() {
        const imagePreview = document.getElementById('imagePreview');
        const imageInput = document.getElementById('eventImage');

        this.imageFile = null;
        this.imagePreviewUrl = null;

        if (imagePreview) {
            imagePreview.style.backgroundImage = '';
            imagePreview.classList.remove('has-image');
            const removeBtn = imagePreview.querySelector('.remove-image');
            if (removeBtn) removeBtn.remove();
        }

        if (imageInput) imageInput.value = '';
    }


    setupTagsInput() {
        const tagsInput = document.getElementById('tagsInput');
        const tagsList = document.getElementById('tagsList');

        tagsInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.addTag(tagsInput.value.trim());
                tagsInput.value = '';
            }
        });

    }

    addTag(tag) {
        if (!tag || this.tags.length >= 5 || this.tags.includes(tag)) return;

        this.tags.push(tag);
        this.renderTags();
        this.hasUnsavedChanges = true;
    }


    removeTag(tag) {
        this.tags = this.tags.filter(t => t !== tag);
        this.renderTags();
    }

    renderTags() {
        const tagsList = document.getElementById('tagsList');
        if (!tagsList) return;

        tagsList.innerHTML = this.tags.map(tag => `
            <span class="tag-item">
                ${this.escapeHtml(tag)}
                <button type="button" onclick="createEventController.removeTag('${this.escapeHtml(tag)}')">&times;</button>
            </span>
        `).join('');
    }


    setupTicketSystem() {
        const ticketTypeRadios = document.querySelectorAll('input[name="ticketType"]');
        const freeSection = document.getElementById('freeTicketSection');
        const paidSection = document.getElementById('paidTicketsSection');
        const addTicketBtn = document.getElementById('addTicketBtn');

        ticketTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                const isPaid = radio.value === 'paid';
                if (freeSection) freeSection.style.display = isPaid ? 'none' : 'block';
                if (paidSection) paidSection.style.display = isPaid ? 'block' : 'none';

                if (isPaid && this.tickets.length === 0) {
                    this.addTicket();
                }

            });
        });

        addTicketBtn?.addEventListener('click', () => this.addTicket());
    }

    addTicket() {
        this.ticketCounter++;
        const ticketId = `ticket_${this.ticketCounter}`;

        this.tickets.push({
            id: ticketId,
            name: '',
            price: '',
            quantity: '',
            description: ''
        });

        this.renderTickets();
    }

    removeTicket(ticketId) {
        if (this.tickets.length <= 1) {
            this.showToast('You must have at least one ticket type', 'error');
            return;
        }

        this.tickets = this.tickets.filter(t => t.id !== ticketId);
        this.renderTickets();
    }


    updateTicket(ticketId, field, value) {
        const ticket = this.tickets.find(t => t.id === ticketId);
        if (ticket) {
            ticket[field] = value;
            this.hasUnsavedChanges = true;
        }
    }


    renderTickets() {
        const ticketsList = document.getElementById('ticketsList');
        if (!ticketsList) return;

        ticketsList.innerHTML = this.tickets.map((ticket, index) => `
            <div class="ticket-item" data-ticket-id="${ticket.id}">
                <div class="ticket-item-header">
                    <h4>Ticket Type ${index + 1}</h4>
                    <button type="button" class="remove-ticket-btn" onclick="createEventController.removeTicket('${ticket.id}')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="ticket-fields">
                    <div class="form-group">
                        <label>Ticket Name <span class="required">*</span></label>
                        <input type="text" value="${this.escapeHtml(ticket.name)}"
                               placeholder="e.g., Early Bird, VIP, Regular"
                               onchange="createEventController.updateTicket('${ticket.id}', 'name', this.value)">
                    </div>
                    <div class="form-group">
                        <label>Price (GHC) <span class="required">*</span></label>
                        <input type="number" value="${ticket.price}" min="0" step="0.01"
                               placeholder="0.00"
                               onchange="createEventController.updateTicket('${ticket.id}', 'price', this.value)">
                    </div>
                    <div class="form-group">
                        <label>Quantity <span class="required">*</span></label>
                        <input type="number" value="${ticket.quantity}" min="1"
                               placeholder="Number available"
                               onchange="createEventController.updateTicket('${ticket.id}', 'quantity', this.value)">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" value="${this.escapeHtml(ticket.description)}"
                               placeholder="What's included?"
                               onchange="createEventController.updateTicket('${ticket.id}', 'description', this.value)">
                    </div>
                </div>
            </div>
        `).join('');
    }



    setupEventTypeToggle() {
        const eventTypeRadios = document.querySelectorAll('input[name="eventType"]');
        const locationFields = document.querySelectorAll('.location-fields');
        const onlineFields = document.querySelectorAll('.online-fields');

        eventTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                const type = radio.value;

                locationFields.forEach(field => {
                    field.style.display = (type === 'in-person' || type === 'hybrid') ? 'block' : 'none';
                });


                onlineFields.forEach(field => {
                    field.style.display = (type === 'online' || type === 'hybrid') ? 'block' : 'none';
                });
            });

        });
    }


    
    populateReview() {
        // Image
        const reviewImageSrc = document.getElementById('reviewImageSrc');
        if (reviewImageSrc && this.formData.image) {
            reviewImageSrc.src = this.formData.image;
        }

        // Basic info
        document.getElementById('reviewCategory').textContent = this.formatCategory(this.formData.category);
        document.getElementById('reviewTitle').textContent = this.formData.name || 'Untitled Event';
        document.getElementById('reviewDescription').textContent = this.formData.description || 'No description';

        // Date & Time
        const startDate = this.formData.startDate;
        const startTime = this.formData.startTime;
        const endDate = this.formData.endDate;
        const endTime = this.formData.endTime;

        let dateTimeStr = '-';
        if (startDate && startTime) {
            const start = new Date(`${startDate}T${startTime}`);
            const end = endDate && endTime ? new Date(`${endDate}T${endTime}`) : null;

            dateTimeStr = this.formatDate(start);
            if (end && end.toDateString() !== start.toDateString()) {
                dateTimeStr += ` - ${this.formatDate(end)}`;
            } else if (end) {
                dateTimeStr += ` - ${this.formatTime(end)}`;
            }
        }
        document.getElementById('reviewDateTime').textContent = dateTimeStr;

       
        let locationStr = '-';

        if (this.formData.eventType === 'online') {
            locationStr = `Online (${this.formData.platform || 'TBD'})`;
        } else if (this.formData.venue) {
            locationStr = this.formData.venue;
            if (this.formData.city) locationStr += `, ${this.formData.city}`;
        }
        document.getElementById('reviewLocation').textContent = locationStr;

       
        const reviewTickets = document.getElementById('reviewTickets');

        if (reviewTickets) {
            if (this.formData.ticketType === 'free') {
                reviewTickets.innerHTML = `
                    <div class="review-ticket-item">
                        <span class="review-ticket-name">Free Registration</span>
                        <span class="review-ticket-price">${this.formData.freeQuantity || 100} available</span>
                    </div>
                `;
            } else if (this.formData.tickets?.length) {
                reviewTickets.innerHTML = this.formData.tickets.map(ticket => `
                    <div class="review-ticket-item">
                        <span class="review-ticket-name">${this.escapeHtml(ticket.name)}</span>
                        <span class="review-ticket-price">GHC ${parseFloat(ticket.price).toFixed(2)}</span>
                    </div>
                `).join('');
            }
        }

        // Settings
        const reviewSettings = document.getElementById('reviewSettings');

        if (reviewSettings) {
            const settings = [];

            settings.push({ label: this.formData.visibility || 'Public', active: true });
            if (this.formData.requireApproval) settings.push({ label: 'Approval Required', active: true });
            if (this.formData.collectPhone) settings.push({ label: 'Collecting Phone', active: true });
            if (this.formData.allowRefunds) settings.push({ label: 'Refunds Allowed', active: true });

            reviewSettings.innerHTML = settings.map(s =>
                `<span class="review-setting-tag ${s.active ? 'active' : ''}">${s.label}</span>`
            ).join('');
        }
    }


    async publishEvent() {
        this.collectStepData();

        try {
            const isEditing = this.isEditMode;
            this.showToast(isEditing ? 'Saving changes...' : 'Publishing event...');

            // Upload image if needed
            if (this.imageFile) {
                const uploadResult = await this.dataService.uploadImage(this.imageFile);
                this.formData.imageUrl = uploadResult.url;
            }

            let result;
            if (isEditing) {
                // Update existing event
                result = await this.dataService.updateEvent(this.editingEventId, this.formData);
            } else {
                // Create new event
                result = await this.dataService.createEvent(this.formData);
            }

            if (result.success) {
                this.hasUnsavedChanges = false;
                const message = isEditing
                    ? 'Your event has been updated successfully!'
                    : 'Your event has been published successfully!';
                this.showSuccessModal(message);
            }
        } catch (error) {
            console.error('Publish failed:', error);
            const errorMsg = this.isEditMode
                ? 'Failed to save changes. Please try again.'
                : 'Failed to publish event. Please try again.';
            this.showToast(errorMsg, 'error');
        }
    }

    async saveDraft() {
        this.collectStepData();

        try {
            this.showToast('Saving draft...');

            const result = await this.dataService.saveDraft(this.formData);

            if (result.success) {
                this.hasUnsavedChanges = false;
                this.showToast('Draft saved successfully!', 'success');
            }
        } catch (error) {
            console.error('Save draft failed:', error);
            this.showToast('Failed to save draft. Please try again.', 'error');
        }
    }

    loadDraftIfExists() {
        const urlParams = new URLSearchParams(window.location.search);
        const draftId = urlParams.get('draft');
        const editId = urlParams.get('edit');
        const duplicateId = urlParams.get('duplicate');

        // Handle edit mode
        if (editId) {
            this.isEditMode = true;
            this.editingEventId = editId;

            const editData = sessionStorage.getItem('editEventData');
            if (editData) {
                const eventData = JSON.parse(editData);
                this.populateFormWithData(eventData);
                sessionStorage.removeItem('editEventData'); 
                this.updatePageTitle('Edit Event');
                this.showToast('Editing event', 'info');
            } else {
               
                this.dataService.getEvent(editId).then(event => {
                    if (event) {
                        this.populateFormWithData(event);
                        this.updatePageTitle('Edit Event');
                    }

                }).catch(err => {
                    console.error('Failed to load event for editing:', err);
                    this.showToast('Failed to load event data', 'error');
                });
            }
            return;
        }

    
        if (duplicateId) {
            this.isDuplicateMode = true;

            const duplicateData = sessionStorage.getItem('duplicateEventData');

            if (duplicateData) {
                const eventData = JSON.parse(duplicateData);
                this.populateFormWithData(eventData);
                sessionStorage.removeItem('duplicateEventData');
                this.updatePageTitle('Duplicate Event');
                this.showToast('Creating copy of event', 'info');
            }
            return;
        }

        
        if (draftId) {
            const drafts = JSON.parse(sessionStorage.getItem('eventDrafts') || '[]');
            const draft = drafts.find(d => d.id === draftId);

            if (draft) {
                this.populateFormWithData(draft);
                this.showToast('Draft loaded', 'success');
            }
        }
    }


    updatePageTitle(title) {
        
        const pageTitle = document.querySelector('.create-event-header h1');
        if (pageTitle) pageTitle.textContent = title;

       
        document.title = `${title} - BONTEN`;

       
        if (this.isEditMode) {
            const publishBtn = document.getElementById('publishBtn');
            if (publishBtn) {
                publishBtn.innerHTML = `
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save Changes
                `;
            }
        }
    }

    populateFormWithData(data) {
        
        this.formData = { ...data };


        const eventName = document.getElementById('eventName');
        const eventCategory = document.getElementById('eventCategory');
        const eventVisibility = document.getElementById('eventVisibility');
        const eventDescription = document.getElementById('eventDescription');
        const nameCount = document.getElementById('nameCount');

        const descCount = document.getElementById('descCount');

        if (data.name && eventName) {
            eventName.value = data.name;
            if (nameCount) nameCount.textContent = data.name.length;
        }
        if (data.category && eventCategory) eventCategory.value = data.category;
        if (data.visibility && eventVisibility) eventVisibility.value = data.visibility;
        if (data.description && eventDescription) {
            eventDescription.value = data.description;
            if (descCount) descCount.textContent = data.description.length;
        }

      
        if (data.image || data.imageUrl) {
            const imagePreview = document.getElementById('imagePreview');
            this.imagePreviewUrl = data.image || data.imageUrl;
            if (imagePreview) {
                imagePreview.style.backgroundImage = `url(${this.imagePreviewUrl})`;
                imagePreview.classList.add('has-image');

               
                if (!imagePreview.querySelector('.remove-image')) {
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'remove-image';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.removeImage();
                    });
                    imagePreview.appendChild(removeBtn);
                }
            }
        }

        
        this.tags = data.tags || [];
        this.renderTags();

        
        const eventStartDate = document.getElementById('eventStartDate');
        const eventStartTime = document.getElementById('eventStartTime');
        const eventEndDate = document.getElementById('eventEndDate');
        const eventEndTime = document.getElementById('eventEndTime');
        const eventTimezone = document.getElementById('eventTimezone');
        const eventVenue = document.getElementById('eventVenue');
        const eventAddress = document.getElementById('eventAddress');
        const eventCity = document.getElementById('eventCity');
        const eventRegion = document.getElementById('eventRegion');
        const eventPlatform = document.getElementById('eventPlatform');

        const eventStreamUrl = document.getElementById('eventStreamUrl');
        const eventCapacity = document.getElementById('eventCapacity');

        if (data.startDate && eventStartDate) eventStartDate.value = data.startDate;
        if (data.startTime && eventStartTime) eventStartTime.value = data.startTime;
        if (data.endDate && eventEndDate) eventEndDate.value = data.endDate;
        if (data.endTime && eventEndTime) eventEndTime.value = data.endTime;
        if (data.timezone && eventTimezone) eventTimezone.value = data.timezone;
        if (data.venue && eventVenue) eventVenue.value = data.venue;
        if (data.address && eventAddress) eventAddress.value = data.address;
        if (data.city && eventCity) eventCity.value = data.city;
        if (data.region && eventRegion) eventRegion.value = data.region;
        if (data.platform && eventPlatform) eventPlatform.value = data.platform;
        if (data.streamUrl && eventStreamUrl) eventStreamUrl.value = data.streamUrl;
        if (data.capacity && eventCapacity) eventCapacity.value = data.capacity;

       
        if (data.eventType) {
            const eventTypeRadio = document.querySelector(`input[name="eventType"][value="${data.eventType}"]`);
            if (eventTypeRadio) {
                eventTypeRadio.checked = true;
                eventTypeRadio.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }


        const freeTicketQuantity = document.getElementById('freeTicketQuantity');
        const requireApproval = document.getElementById('requireApproval');
        const collectPhone = document.getElementById('collectPhone');
        const allowRefunds = document.getElementById('allowRefunds');


        
        if (data.ticketType) {
            const ticketTypeRadio = document.querySelector(`input[name="ticketType"][value="${data.ticketType}"]`);
            if (ticketTypeRadio) {
                ticketTypeRadio.checked = true;

                const freeSection = document.getElementById('freeTicketSection');
                const paidSection = document.getElementById('paidTicketsSection');

                if (data.ticketType === 'paid') {
                    if (freeSection) freeSection.style.display = 'none';
                    if (paidSection) paidSection.style.display = 'block';
                } else {
                    if (freeSection) freeSection.style.display = 'block';
                    if (paidSection) paidSection.style.display = 'none';
                }

            }
        }

        if (data.freeQuantity && freeTicketQuantity) freeTicketQuantity.value = data.freeQuantity;


        if (data.tickets && data.tickets.length > 0) {
            this.tickets = data.tickets.map((ticket, index) => ({
                ...ticket,
                id: ticket.id || `ticket_${index + 1}`
            }));
            this.ticketCounter = this.tickets.length;
            this.renderTickets();
        }

        if (data.requireApproval !== undefined && requireApproval) requireApproval.checked = data.requireApproval;
        if (data.collectPhone !== undefined && collectPhone) collectPhone.checked = data.collectPhone;
        if (data.allowRefunds !== undefined && allowRefunds) allowRefunds.checked = data.allowRefunds;
    }


    setupModals() {
       
        const viewEventBtn = document.getElementById('viewEventBtn');
        const goToDashboardBtn = document.getElementById('goToDashboardBtn');

        viewEventBtn?.addEventListener('click', () => {
          
            this.showToast('Redirecting to event...');
            setTimeout(() => {
                window.location.href = 'manager_history.html';
            }, 500);
        });

        goToDashboardBtn?.addEventListener('click', () => {
            window.location.href = 'manager_dashboard.html';
        });


       
        const discardModal = document.getElementById('discard-modal');
        const keepEditingBtn = document.getElementById('keepEditingBtn');
        const discardBtn = document.getElementById('discardBtn');

        keepEditingBtn?.addEventListener('click', () => {
            this.closeModal('discard-modal');
        });

        discardBtn?.addEventListener('click', () => {
            this.hasUnsavedChanges = false;
            window.location.href = 'manager_dashboard.html';
        });


   
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal');
                if (modal) modal.style.display = 'none';
            });
        });

     
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', () => {
                const modal = overlay.closest('.modal');
                if (modal && modal.id !== 'success-modal') {
                    modal.style.display = 'none';
                }
            });
        });
    }


    showSuccessModal(message) {
        const modal = document.getElementById('success-modal');
        const messageEl = document.getElementById('successMessage');

        if (messageEl) messageEl.textContent = message;
        if (modal) modal.style.display = 'flex';
    }

    showDiscardModal() {
        const modal = document.getElementById('discard-modal');
        if (modal) modal.style.display = 'flex';
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);

        if (modal) modal.style.display = 'none';
    }

    setupBeforeUnload() {
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        
        document.querySelectorAll('.nav-item, .logout').forEach(link => {
            link.addEventListener('click', (e) => {
                if (this.hasUnsavedChanges) {
                    e.preventDefault();
                    this.pendingNavigation = link.href;
                    this.showDiscardModal();
                }

            });
        });

    }

    formatCategory(category) {
        const categories = {
            'concert': 'Concert',
            'festival': 'Festival',
            'conference': 'Conference',
            'workshop': 'Workshop',
            'sports': 'Sports',
            'fashion': 'Fashion',
            'food': 'Food & Drinks',
            'networking': 'Networking',
            'party': 'Party',
            'other': 'Other'
        };
        return categories[category] || category;
    }

    formatDate(date) {
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    formatTime(date) {
        return date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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


let createEventController;

document.addEventListener('DOMContentLoaded', () => {
    createEventController = new CreateEventController();
});
