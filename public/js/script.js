const signUpForm = document.getElementById("signup-form");

const loginForm = document.getElementById("login-form");

const switchToLoginLink = document.getElementById("switch-to-login");

const switchToSignUpLink = document.getElementById("switch-to-signup");

const leftSide = document.querySelector(".left-side");

const tabNameText = document.querySelector(".tab-name");

const passwordFieldBtn = document.querySelector(".pwd-eye");

function showLoginForm() {


  signUpForm.style.display = "none";


  loginForm.style.cssText =


    "display: flex;flex-direction: column;justify-content: center;align-items: center;text-align: center;gap: 10px;padding: 20px;";
  const paragraphs = loginForm.querySelectorAll("label p");

  paragraphs.forEach((paragraph) => {
    paragraph.style.cssText = "padding-bottom: 10px;";

  });

  loginForm.style.transition = "all 0.7s ease-in-out";

  tabNameText.textContent = "Login";
}

function showSignUpForm() {

  loginForm.style.display = "none";

  signUpForm.style.cssText =

    "display: flex;flex-direction: column;justify-content: center;align-items: center;text-align: center;gap: 10px;padding: 20px;";


  const paragraphs = signUpForm.querySelectorAll("label p");

  paragraphs.forEach((paragraph) => {

    paragraph.style.cssText = "padding-bottom: 10px;";

  });

  signUpForm.style.transition = "all 0.7s ease-in-out";


  tabNameText.textContent = "Sign Up";
}

switchToLoginLink.addEventListener("click", (e) => {
  e.preventDefault();
  showLoginForm();
});

switchToSignUpLink.addEventListener("click", (e) => {

  e.preventDefault();

  showSignUpForm();

});

showLoginForm();

function togglePasswordVisibility() {

  const signupPasswordField = document.getElementById("signup-password-field");

  const loginPasswordField = document.getElementById("password-field");

  const isSignupForm = signUpForm.style.display !== "none";
  const passwordField = isSignupForm ? signupPasswordField : loginPasswordField;

  if (!passwordField) return;

  const eyeIcon = document.getElementById("eye-icon");

  if (passwordField.type === "password") {


    passwordField.type = "text";


    eyeIcon.innerHTML =

      '<path d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t37.5-4q75 0 127.5 52.5T660-500q0 20-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302Zm20 246L624-222q-35 11-70.5 16.5T480-200q-151 0-269-83.5T40-500q21-53 53-98.5t73-81.5L56-792l56-56 736 736-56 56ZM222-624q-29 26-53 57t-41 67q50 101 143.5 160.5T480-280q20 0 39-2.5t39-5.5l-36-38q-11 3-21 4.5t-21 1.5q-75 0-127.5-52.5T300-500q0-11 1.5-21t4.5-21l-84-82Zm319 93Zm-151 75Z"/>';

  } else {

    passwordField.type = "password";

    eyeIcon.innerHTML =


      '<path d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"/>';

  }
}

passwordFieldBtn.addEventListener("click", togglePasswordVisibility);

const images = [

  "../public/assets/ashchella.JPG",

  "../public/assets/imullar.jpg",

  "../public/assets/y2k.JPG",

  "../public/assets/gff.jpg",


  "../public/assets/detty.webp",


];

function changeBackgroundImage() {


  const randomIndex = Math.floor(Math.random() * images.length);


  leftSide.style.backgroundImage = `linear-gradient(rgba(147, 84, 45, 0.36), rgba(0, 0, 0, 0.5)), url(${images[randomIndex]})`;


  leftSide.style.transition = "background-image 1s ease-in-out";

  leftSide.style.backgroundSize = "cover";
  leftSide.style.backgroundPosition = "center";

}

setInterval(changeBackgroundImage, 2500);

changeBackgroundImage();

signUpForm.addEventListener("submit", function (e) {

  const firstName = document.getElementById("first-name-field").value.trim();
  const lastName = document.getElementById("last-name-field").value.trim();
  const email = document.getElementById("signup-email-field").value.trim();


  const password = document.getElementById("signup-password-field").value;

  const phone = document.getElementById("phoneNumber-field").value.trim();


  const role = document.getElementById("role-field").value;

  if (!firstName || !lastName || !email || !password || !phone || !role) {

    alert("Please fill in all fields");

    e.preventDefault();

    return false;


  }

  if (typeof getPasswordError === "function") {

    const passwordError = getPasswordError(password);


    if (passwordError) {


      alert(passwordError);

      e.preventDefault();


      return false;

    }
  } else {
    if (password.length < 8) {

      alert("Password must be at least 8 characters long");
      e.preventDefault();


      return false;

    }

    const hasNumber = /[0-9]/.test(password);

    if (!hasNumber) {

      alert("Password must contain at least one number");

      e.preventDefault();


      return false;

    }

    const hasUppercase = /[A-Z]/.test(password);

    if (!hasUppercase) {
      alert("Password must contain at least one uppercase letter");

      e.preventDefault();

      return false;

    }

    const hasLowercase = /[a-z]/.test(password);

    if (!hasLowercase) {


      alert("Password must contain at least one lowercase letter");
      e.preventDefault();

      return false;

    }
  }

  return true;

});

function validatePassword() {
  const signupPasswordField = document.getElementById("signup-password-field");

  const loginPasswordField = document.getElementById("password-field");

  const isSignupForm = signUpForm.style.display !== "none";
  const passwordField = isSignupForm ? signupPasswordField : loginPasswordField;

  if (!passwordField) return true;

  const password = passwordField.value;

  const passwordCue = document.getElementById("password-cue");

  if (isSignupForm) {
    if (typeof getPasswordError === "function") {


      const error = getPasswordError(password);


      if (error) {
        passwordCue.textContent = error;
        passwordCue.style.color = "red";
        return false;


      }

    } else {

      if (password.length < 8) {

        passwordCue.textContent = "Password must be at least 8 characters long";


        passwordCue.style.color = "red";

        return false;
      }

      const hasNumber = /[0-9]/.test(password);
      if (!hasNumber) {

        passwordCue.textContent = "Password must contain at least one number";


        passwordCue.style.color = "red";


        return false;


      }

      const hasUppercase = /[A-Z]/.test(password);

      if (!hasUppercase) {

        passwordCue.textContent =
          "Password must contain at least one uppercase letter";

        passwordCue.style.color = "red";

        return false;
      }

      const hasLowercase = /[a-z]/.test(password);

      if (!hasLowercase) {

        passwordCue.textContent =

          "Password must contain at least one lowercase letter";

        passwordCue.style.color = "red";

        return false;


      }


    }


  }

  passwordCue.textContent = "";
  return true;


}

const signupPasswordField = document.getElementById("signup-password-field");
if (signupPasswordField) {
  signupPasswordField.addEventListener("input", validatePassword);

  signupPasswordField.addEventListener("blur", validatePassword);

}
