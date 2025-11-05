const signUpForm = document.getElementById('signup-form');
const loginForm = document.getElementById('login-form');
const switchToLoginLink = document.getElementById('switch-to-login');
const switchToSignUpLink = document.getElementById('switch-to-signup');
const leftSide = document.querySelector('.left-side');
const tabNameText = document.querySelector('.tab-name');
// Function to show the login form and hide the sign-up form
function showLoginForm() {
    signUpForm.style.display = 'none';
    loginForm.style.cssText = 'display: flex;flex-direction: column;justify-content: center;align-items: center;text-align: center;gap: 10px;padding: 20px;';
    const paragraphs = loginForm.querySelectorAll('label p');
    paragraphs.forEach(paragraph => {
        paragraph.style.cssText = 'padding-bottom: 10px;';
    });
    loginForm.style.transition = 'all 0.7s ease-in-out';
    tabNameText.textContent = 'Login';
}

// Function to show the sign-up form and hide the login form
function showSignUpForm() {
    loginForm.style.display = 'none';
    // Recommended fix: Use cssText
    signUpForm.style.cssText = 'display: flex;flex-direction: column;justify-content: center;align-items: center;text-align: center;gap: 10px;padding: 20px;';
    const paragraphs = signUpForm.querySelectorAll('label p');
    paragraphs.forEach(paragraph => {
        paragraph.style.cssText = 'padding-bottom: 10px;';
    });    signUpForm.style.transition = 'all 0.7s ease-in-out';
    tabNameText.textContent = 'Sign Up';
}

// Event listeners for switching forms
switchToLoginLink.addEventListener('click', (e) => {
    e.preventDefault();
    showLoginForm();
});

switchToSignUpLink.addEventListener('click', (e) => {
    e.preventDefault();
    showSignUpForm();
});

// Initially, show the login form and hide the sign-up form   
showLoginForm();

// You can add form submission handling here as needed


const images= ['../assets/ashchella.JPG','../assets/imullar.jpg','../assets/y2k.JPG'];

function changeBackgroundImage() {
    const randomIndex = Math.floor(Math.random() * images.length);
    leftSide.style.backgroundImage = `linear-gradient(rgba(147, 84, 45, 0.36), rgba(0, 0, 0, 0.5)), url(${images[randomIndex]})`;
    leftSide.style.transition = 'background-image 1s ease-in-out';
    leftSide.style.backgroundSize = 'cover';
    leftSide.style.backgroundPosition = 'center';
    
}

// Change background image every 2.5 seconds
setInterval(changeBackgroundImage, 2500);

// Initial call to set a background image when the page loads
changeBackgroundImage();