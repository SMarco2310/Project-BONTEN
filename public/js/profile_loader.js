

document.addEventListener('DOMContentLoaded', () => {

    const savedProfilePic = localStorage.getItem('userProfilePicture');

    if (savedProfilePic) {


        const allProfileImages = document.querySelectorAll('.profile_picture, #profile-avatar-img, .profile-avatar img');

        allProfileImages.forEach(img => {

            img.src = savedProfilePic;

        });
    }


});
