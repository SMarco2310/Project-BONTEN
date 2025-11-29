function isValidEmail(email) {

  if (!email || email.trim() === '') {
    return false;
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  return emailPattern.test(email);
}

function isValidPassword(password) {

  if (!password || password.trim() === '') {
    return false;
  }


  if (password.length < 8) {
    return false;
  }


  const hasNumber = /[0-9]/.test(password);
  if (!hasNumber) {
    return false;
  }


  const hasUppercase = /[A-Z]/.test(password);
  if (!hasUppercase) {
    return false;
  }


  const hasLowercase = /[a-z]/.test(password);
  if (!hasLowercase) {
    return false;
  }

  return true;
}


function getEmailError(email) {
  if (!email || email.trim() === '') {
    return 'Email is required';
  }

  if (!email.includes('@')) {
    return 'Email must contain @ symbol';
  }

  if (!email.includes('.')) {
    return 'Email must contain a domain (e.g., .com)';
  }

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    return 'Please enter a valid email address';
  }

  return '';
}


function getPasswordError(password) {
  if (!password || password.trim() === '') {
    return 'Password is required';
  }

  if (password.length < 8) {
    return 'Your password must be at least 8 characters long';
  }

 
  const hasNumber = /[0-9]/.test(password);
  if (!hasNumber) {
    return 'Your password must contain at least one number';
  }


  const hasUppercase = /[A-Z]/.test(password);
  if (!hasUppercase) {
    return 'Your password must contain at least one uppercase letter';
  }


  const hasLowercase = /[a-z]/.test(password);
  if (!hasLowercase) {
    return 'Your password must contain at least one lowercase letter';
  }

  return '';
}


function showError(inputElement, message) {

  removeError(inputElement);


  inputElement.style.borderColor = 'red';


  const errorElement = document.createElement('div');
  errorElement.className = 'validation-error';
  errorElement.style.color = 'red';
  errorElement.style.fontSize = '12px';
  errorElement.style.marginTop = '5px';
  errorElement.textContent = message;


  inputElement.parentElement.appendChild(errorElement);
}


function removeError(inputElement) {

  inputElement.style.borderColor = '';


  const errorElement = inputElement.parentElement.querySelector('.validation-error');
  if (errorElement) {
    errorElement.remove();
  }
}


function validateEmailField(emailInput) {
  const email = emailInput.value;
  const errorMessage = getEmailError(email);

  if (errorMessage) {
    showError(emailInput, errorMessage);
    return false;
  } else {
    removeError(emailInput);
    return true;
  }
}


function validatePasswordField(passwordInput) {
  const password = passwordInput.value;
  const errorMessage = getPasswordError(password);

  if (errorMessage) {
    showError(passwordInput, errorMessage);
    return false;
  } else {
    removeError(passwordInput);
    return true;
  }
}
