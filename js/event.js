const events = [
    {
        id: 'ashchella',
        name: 'Ashchella',
        tag: 'ASC Week',
        imageUrl: '/assets/ashchella.JPG',
        description: 'Ashchella Description'
    },
    {
        id: 'y2k-neon',
        name: 'Y2K Neon',
        tag: 'Fashion',
        imageUrl: '/assets/y2k.JPG',
        description: 'Y2K Neon Description'
    },
    {
        id: 'global-footbal-festival',
        name: 'Global Football Festival',
        tag: 'Football',
        imageUrl: '/assets/gff.jpg',
        description: 'Global Football Festival Description'
    },
    {
        id: 'tanks-and-bikinis',
        name: 'Tanks and Bikinis',
        tag: 'Concert',
        imageUrl: '/assets/t&b.jpg',
        description: 'Tanks and Bikinis Description'
    },
    {
        id: 'tidal-rave',
        name: 'Tidal Rave',
        tag: 'Concert',
        imageUrl: '/assets/tidalrave.jpg',
        description: 'Tidal Rave Description'
    },
    {
        id: 'detty-december',
        name: 'Detty December',
        tag: 'Concert',
        imageUrl: '/assets/detty.webp',
        description: 'Detty December Description'
    },
    {
        id: 'rapperholic',
        name: 'Rapperholic',
        tag: 'Concert',
        imageUrl: '/assets/rapperholic.jpeg',
        description: 'Rapperholic Description'
    },
    {
        id: 'imullar',
        name: 'iMullar',
        tag: 'Concert',
        imageUrl: '/assets/imullar.jpg',
        description: 'iMullar Description'
    }
];

document.addEventListener('DOMContentLoaded', () => {
    let backgroundBox, eventNameText, eventTagText, eventDescriptionText;
    backgroundBox = document.getElementById('event-img');
    eventNameText = document.getElementById('event-name');
    eventTagText = document.getElementById('event-tag');
    eventDescriptionText = document.getElementById('event-description');

    if(!backgroundBox || !eventNameText || !eventTagText || !eventDescriptionText){
        console.warn('Script is running on a page without the required IDs');
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const eventId = urlParams.get('id');
    console.log('EventID:', eventId);

    let selectedEvent;

    if (eventId) {
        const cleanEventId = eventId.trim().toLowerCase();
        
        console.log(`Starting search for: [${cleanEventId}]`); // <-- New log

        selectedEvent = events.find(event => {
            const idFromArr = event.id.trim().toLowerCase();
            
            // This log will show us the *exact* comparison
            console.log(`Comparing... Array ID: [${idFromArr}] --- URL ID: [${cleanEventId}]`); 
            
            return idFromArr === cleanEventId;
        });
    }

    let imageUrl;

    if(selectedEvent){
        backgroundBox.style.backgroundImage = `url('${selectedEvent.imageUrl}')`;
        eventNameText.innerHTML = selectedEvent.name;
        eventTagText.innerHTML = selectedEvent.tag;
        eventDescriptionText.innerHTML = selectedEvent.description;
    } else{
        imageUrl = '/assets/hero.png'
        console.warn(`No event found with ${eventId}`)
    }
});