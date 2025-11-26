/**
 * Manager Settings Page Controller
 *
 * Handles profile, payment details, notifications, and security settings
 */

class ManagerSettingsController {
    constructor() {
        this.currentSection = 'profile';
        this.paymentData = {
            bank: null,
            momo: null,
            paystack: null
        };

        this.init();
    }

    init() {
        this.loadUserProfile();
        this.loadPaymentData();
        this.setupNavigationHandlers();
        this.setupProfileHandlers();
        this.setupPaymentHandlers();
        this.setupNotificationHandlers();
        this.setupSecurityHandlers();
        this.setupModalHandlers();
    }

    // ========================================================================
    // USER PROFILE
    // ========================================================================

    loadUserProfile() {
        const userData = sessionStorage.getItem('userData');
        if (userData) {
            const user = JSON.parse(userData);

            // Update form fields
            const firstName = document.getElementById('firstName');
            const lastName = document.getElementById('lastName');
            const email = document.getElementById('email');
            const headerName = document.getElementById('headerName');

            if (firstName && user.firstName) firstName.value = user.firstName;
            if (lastName && user.lastName) lastName.value = user.lastName;
            if (email && user.email) email.value = user.email;
            if (headerName) headerName.textContent = `${user.firstName || 'User'} ${user.lastName || ''}`;
        }
    }

    // ========================================================================
    // NAVIGATION
    // ========================================================================

    setupNavigationHandlers() {
        const navItems = document.querySelectorAll('.settings-nav-item');

        navItems.forEach(item => {
            item.addEventListener('click', () => {
                const section = item.dataset.section;
                this.switchSection(section);
            });
        });
    }

    switchSection(section) {
        // Update nav items
        document.querySelectorAll('.settings-nav-item').forEach(item => {
            item.classList.toggle('active', item.dataset.section === section);
        });

        // Update sections
        document.querySelectorAll('.settings-section').forEach(sec => {
            sec.classList.remove('active');
        });

        const targetSection = document.getElementById(`${section}-section`);
        if (targetSection) {
            targetSection.classList.add('active');
        }

        this.currentSection = section;
    }

    // ========================================================================
    // PROFILE HANDLERS
    // ========================================================================

    setupProfileHandlers() {
        const profileForm = document.getElementById('profileForm');
        const changeAvatarBtn = document.getElementById('changeAvatarBtn');
        const removeAvatarBtn = document.getElementById('removeAvatarBtn');
        const avatarInput = document.getElementById('avatarInput');
        const cancelProfileBtn = document.getElementById('cancelProfileBtn');

        changeAvatarBtn?.addEventListener('click', () => {
            avatarInput?.click();
        });

        avatarInput?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (file) {
                this.handleAvatarUpload(file);
            }
        });

        removeAvatarBtn?.addEventListener('click', () => {
            this.removeAvatar();
        });

        cancelProfileBtn?.addEventListener('click', () => {
            this.loadUserProfile(); // Reset form
            this.showToast('Changes discarded', 'info');
        });

        profileForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveProfile();
        });
    }

    handleAvatarUpload(file) {
        if (!file.type.startsWith('image/')) {
            this.showToast('Please select an image file', 'error');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            this.showToast('Image must be less than 5MB', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            const profileAvatar = document.getElementById('profileAvatar');
            const headerAvatar = document.getElementById('headerAvatar');

            if (profileAvatar) profileAvatar.src = e.target.result;
            if (headerAvatar) headerAvatar.src = e.target.result;

            this.showToast('Avatar updated', 'success');
        };
        reader.readAsDataURL(file);
    }

    removeAvatar() {
        const defaultAvatar = '../assets/jerome.jpeg';
        const profileAvatar = document.getElementById('profileAvatar');
        const headerAvatar = document.getElementById('headerAvatar');

        if (profileAvatar) profileAvatar.src = defaultAvatar;
        if (headerAvatar) headerAvatar.src = defaultAvatar;

        this.showToast('Avatar removed', 'success');
    }

    saveProfile() {
        const firstName = document.getElementById('firstName')?.value;
        const lastName = document.getElementById('lastName')?.value;
        const email = document.getElementById('email')?.value;
        const phone = document.getElementById('phone')?.value;
        const bio = document.getElementById('bio')?.value;
        const company = document.getElementById('company')?.value;

        // Validate
        if (!firstName || !lastName || !email) {
            this.showToast('Please fill in all required fields', 'error');
            return;
        }

        // Save to sessionStorage
        const userData = JSON.parse(sessionStorage.getItem('userData') || '{}');
        userData.firstName = firstName;
        userData.lastName = lastName;
        userData.email = email;
        userData.phone = phone;
        userData.bio = bio;
        userData.company = company;
        sessionStorage.setItem('userData', JSON.stringify(userData));

        // Update header
        const headerName = document.getElementById('headerName');
        if (headerName) headerName.textContent = `${firstName} ${lastName}`;

        this.showToast('Profile saved successfully', 'success');
    }

    // ========================================================================
    // PAYMENT HANDLERS
    // ========================================================================

    loadPaymentData() {
        const paymentData = sessionStorage.getItem('paymentData');
        if (paymentData) {
            this.paymentData = JSON.parse(paymentData);
            this.updatePaymentUI();
        }
    }

    savePaymentData() {
        sessionStorage.setItem('paymentData', JSON.stringify(this.paymentData));
    }

    updatePaymentUI() {
        // Bank Account
        if (this.paymentData.bank) {
            const bankStatus = document.getElementById('bankStatus');
            const bankDetails = document.getElementById('bankDetails');
            const addBankBtn = document.getElementById('addBankBtn');

            if (bankStatus) {
                bankStatus.textContent = 'Connected';
                bankStatus.classList.add('configured');
            }
            if (addBankBtn) addBankBtn.textContent = 'Edit';
            if (bankDetails) {
                bankDetails.style.display = 'block';
                document.getElementById('bankName').textContent = this.paymentData.bank.bankName;
                document.getElementById('accountName').textContent = this.paymentData.bank.accountName;
                document.getElementById('accountNumber').textContent = this.maskAccountNumber(this.paymentData.bank.accountNumber);
                document.getElementById('bankBranch').textContent = this.paymentData.bank.branch || '-';
            }
        }

        // Mobile Money
        if (this.paymentData.momo) {
            const momoStatus = document.getElementById('momoStatus');
            const momoDetails = document.getElementById('momoDetails');
            const addMomoBtn = document.getElementById('addMomoBtn');

            if (momoStatus) {
                momoStatus.textContent = 'Connected';
                momoStatus.classList.add('configured');
            }
            if (addMomoBtn) addMomoBtn.textContent = 'Edit';
            if (momoDetails) {
                momoDetails.style.display = 'block';
                document.getElementById('momoProvider').textContent = this.paymentData.momo.provider;
                document.getElementById('momoNumber').textContent = this.paymentData.momo.phoneNumber;
                document.getElementById('momoAccountName').textContent = this.paymentData.momo.accountName;
            }
        }

        // Paystack
        if (this.paymentData.paystack) {
            const paystackStatus = document.getElementById('paystackStatus');
            const connectBtn = document.getElementById('connectPaystackBtn');

            if (paystackStatus) {
                paystackStatus.textContent = 'Connected';
                paystackStatus.classList.add('configured');
            }
            if (connectBtn) {
                connectBtn.textContent = 'Disconnect';
                connectBtn.classList.remove('btn-primary');
                connectBtn.classList.add('btn-secondary');
            }
        }
    }

    maskAccountNumber(number) {
        if (!number || number.length < 4) return number;
        return '****' + number.slice(-4);
    }

    setupPaymentHandlers() {
        const addBankBtn = document.getElementById('addBankBtn');
        const addMomoBtn = document.getElementById('addMomoBtn');
        const connectPaystackBtn = document.getElementById('connectPaystackBtn');
        const bankForm = document.getElementById('bankForm');
        const momoForm = document.getElementById('momoForm');

        addBankBtn?.addEventListener('click', () => {
            this.openModal('bank-modal');
            if (this.paymentData.bank) {
                // Pre-fill form for editing
                document.getElementById('bankNameInput').value = this.paymentData.bank.bankName;
                document.getElementById('accountNameInput').value = this.paymentData.bank.accountName;
                document.getElementById('accountNumberInput').value = this.paymentData.bank.accountNumber;
                document.getElementById('branchInput').value = this.paymentData.bank.branch || '';
            }
        });

        addMomoBtn?.addEventListener('click', () => {
            this.openModal('momo-modal');
            if (this.paymentData.momo) {
                // Pre-fill form for editing
                document.getElementById('momoProviderInput').value = this.paymentData.momo.provider;
                document.getElementById('momoNumberInput').value = this.paymentData.momo.phoneNumber;
                document.getElementById('momoNameInput').value = this.paymentData.momo.accountName;
            }
        });

        connectPaystackBtn?.addEventListener('click', () => {
            if (this.paymentData.paystack) {
                // Disconnect
                this.paymentData.paystack = null;
                this.savePaymentData();
                this.showToast('Paystack disconnected', 'success');

                const paystackStatus = document.getElementById('paystackStatus');
                if (paystackStatus) {
                    paystackStatus.textContent = 'Connect for instant payouts';
                    paystackStatus.classList.remove('configured');
                }
                connectPaystackBtn.textContent = 'Connect Paystack';
                connectPaystackBtn.classList.remove('btn-secondary');
                connectPaystackBtn.classList.add('btn-primary');
            } else {
                // Connect (simulate OAuth flow)
                this.showToast('Redirecting to Paystack...', 'info');
                setTimeout(() => {
                    this.paymentData.paystack = {
                        connected: true,
                        connectedAt: new Date().toISOString()
                    };
                    this.savePaymentData();
                    this.updatePaymentUI();
                    this.showToast('Paystack connected successfully!', 'success');
                }, 1500);
            }
        });

        bankForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveBankAccount();
        });

        momoForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveMomoAccount();
        });

        // Payout settings
        const payoutSchedule = document.getElementById('payoutSchedule');
        const minimumPayout = document.getElementById('minimumPayout');

        payoutSchedule?.addEventListener('change', () => {
            this.showToast('Payout schedule updated', 'success');
        });

        minimumPayout?.addEventListener('change', () => {
            const value = parseInt(minimumPayout.value);
            if (value < 50) {
                minimumPayout.value = 50;
                this.showToast('Minimum payout cannot be less than GHC 50', 'error');
            } else {
                this.showToast('Minimum payout updated', 'success');
            }
        });
    }

    saveBankAccount() {
        const bankName = document.getElementById('bankNameInput')?.value;
        const accountName = document.getElementById('accountNameInput')?.value;
        const accountNumber = document.getElementById('accountNumberInput')?.value;
        const branch = document.getElementById('branchInput')?.value;

        if (!bankName || !accountName || !accountNumber) {
            this.showToast('Please fill in all required fields', 'error');
            return;
        }

        this.paymentData.bank = {
            bankName,
            accountName,
            accountNumber,
            branch
        };

        this.savePaymentData();
        this.updatePaymentUI();
        this.closeModal('bank-modal');
        this.showToast('Bank account saved successfully', 'success');
    }

    saveMomoAccount() {
        const provider = document.getElementById('momoProviderInput')?.value;
        const phoneNumber = document.getElementById('momoNumberInput')?.value;
        const accountName = document.getElementById('momoNameInput')?.value;

        if (!provider || !phoneNumber || !accountName) {
            this.showToast('Please fill in all required fields', 'error');
            return;
        }

        this.paymentData.momo = {
            provider,
            phoneNumber,
            accountName
        };

        this.savePaymentData();
        this.updatePaymentUI();
        this.closeModal('momo-modal');
        this.showToast('Mobile Money saved successfully', 'success');
    }

    // ========================================================================
    // NOTIFICATION HANDLERS
    // ========================================================================

    setupNotificationHandlers() {
        const notificationsForm = document.getElementById('notificationsForm');

        notificationsForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveNotificationPreferences();
        });
    }

    saveNotificationPreferences() {
        const preferences = {
            emailTicketSales: document.getElementById('emailTicketSales')?.checked,
            emailEventReminders: document.getElementById('emailEventReminders')?.checked,
            emailReviews: document.getElementById('emailReviews')?.checked,
            emailPayouts: document.getElementById('emailPayouts')?.checked,
            smsEventAlerts: document.getElementById('smsEventAlerts')?.checked,
            smsUrgent: document.getElementById('smsUrgent')?.checked
        };

        sessionStorage.setItem('notificationPreferences', JSON.stringify(preferences));
        this.showToast('Notification preferences saved', 'success');
    }

    // ========================================================================
    // SECURITY HANDLERS
    // ========================================================================

    setupSecurityHandlers() {
        const updatePasswordBtn = document.getElementById('updatePasswordBtn');
        const deleteAccountBtn = document.getElementById('deleteAccountBtn');
        const enable2FA = document.getElementById('enable2FA');

        updatePasswordBtn?.addEventListener('click', () => {
            this.updatePassword();
        });

        deleteAccountBtn?.addEventListener('click', () => {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                this.showToast('Account deletion requested. You will receive a confirmation email.', 'info');
            }
        });

        enable2FA?.addEventListener('change', () => {
            if (enable2FA.checked) {
                this.showToast('2FA enabled. Set up your authenticator app.', 'success');
            } else {
                this.showToast('2FA disabled', 'info');
            }
        });
    }

    updatePassword() {
        const currentPassword = document.getElementById('currentPassword')?.value;
        const newPassword = document.getElementById('newPassword')?.value;
        const confirmPassword = document.getElementById('confirmPassword')?.value;

        if (!currentPassword || !newPassword || !confirmPassword) {
            this.showToast('Please fill in all password fields', 'error');
            return;
        }

        if (newPassword.length < 8) {
            this.showToast('Password must be at least 8 characters', 'error');
            return;
        }

        if (newPassword !== confirmPassword) {
            this.showToast('Passwords do not match', 'error');
            return;
        }

        // Clear fields
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';

        this.showToast('Password updated successfully', 'success');
    }

    // ========================================================================
    // MODAL HANDLERS
    // ========================================================================

    setupModalHandlers() {
        // Bank modal
        const cancelBankBtn = document.getElementById('cancelBankBtn');
        cancelBankBtn?.addEventListener('click', () => this.closeModal('bank-modal'));

        // Momo modal
        const cancelMomoBtn = document.getElementById('cancelMomoBtn');
        cancelMomoBtn?.addEventListener('click', () => this.closeModal('momo-modal'));

        // Close on overlay click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', () => {
                const modal = overlay.closest('.modal');
                if (modal) modal.style.display = 'none';
            });
        });

        // Close buttons
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal');
                if (modal) modal.style.display = 'none';
            });
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // ========================================================================
    // UTILITIES
    // ========================================================================

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

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    new ManagerSettingsController();
});
