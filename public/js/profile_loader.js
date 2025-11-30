document.addEventListener('DOMContentLoaded', () => {
    
    const profileImages = document.querySelectorAll('.profile_picture, #profile-avatar-img, .profile-avatar img');

    if (profileImages.length > 0) {
        const currentProfilePic = profileImages[0].src;

        
        if (currentProfilePic) {
            localStorage.setItem('userProfilePicture', currentProfilePic);
        }

      
        profileImages.forEach(img => {
            if (currentProfilePic) {
                img.src = currentProfilePic;
            }
        });
    }
});
