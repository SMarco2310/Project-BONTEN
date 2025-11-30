



class LogoutHandler {
    static handleLogout(e) {
        if (e) {
            e.preventDefault();
        }
        
        
        try {
            sessionStorage.clear();
            localStorage.clear();
        } catch (error) {
            console.warn('Could not clear storage:', error);
        }
        
        
        if (typeof showToast === 'function') {
            showToast('Logging out...', 'info');
        }
        
        
        fetch('./logout.php', {
            method: 'POST',
            credentials: 'same-origin'
        }).then(response => {
            if (response.ok) {
                window.location.href = './index.php';
            } else {
                window.location.href = './logout.php';
            }
        }).catch(() => {
            window.location.href = './logout.php';
        });
    }
    
    static initLogoutButtons() {
        
        
        const logoutButtons = document.querySelectorAll('a.logout, .logout-btn, [data-logout]');
        
        logoutButtons.forEach(button => {
            button.addEventListener('click', LogoutHandler.handleLogout);
        });
    }
    
    static checkAuthState() {
        
        if (window.location.pathname.includes('index.php') || window.location.pathname.endsWith('/')) {
            const hasSessionData = sessionStorage.getItem('userData') || 
                                 localStorage.getItem('userProfilePicture') ||
                                 sessionStorage.getItem('currentEvent');
            
            if (hasSessionData && !document.body.classList.contains('authenticated')) {
                
                sessionStorage.clear();
                
                localStorage.clear();
            }
        }
    }
}


document.addEventListener('DOMContentLoaded', () => {

    LogoutHandler.initLogoutButtons();

    LogoutHandler.checkAuthState();
});


if (typeof module !== 'undefined' && module.exports) {
    module.exports = LogoutHandler;
}


window.LogoutHandler = LogoutHandler;
