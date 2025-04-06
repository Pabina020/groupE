function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleButton = passwordInput.nextElementSibling;
    const toggleIcon = toggleButton.querySelector('img');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.src = 'https://api.iconify.design/lucide:eye.svg';
        toggleIcon.alt = 'Hide password';
    } else {
        passwordInput.type = 'password';
        toggleIcon.src = 'https://api.iconify.design/lucide:eye-off.svg';
        toggleIcon.alt = 'Show password';
    }
}