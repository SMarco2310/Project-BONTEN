


document.addEventListener('DOMContentLoaded', () => {
    
    const eventImg = document.getElementById('event-img');
    
    const eventName = document.getElementById('event-name');
    
    const eventTag = document.getElementById('event-tag');
    
    const eventDescription = document.getElementById('event-description');

    if (!eventImg || !eventName || !eventTag || !eventDescription) {
        console.warn('Event page elements not found');
        return;
    }

   
    if (eventName.textContent.trim()) {
        console.log('Event data loaded from PHP');
        
        
        sessionStorage.removeItem('currentEvent');
        return;
    }

   
    console.warn('Event data not loaded from PHP, event may not exist');
});
