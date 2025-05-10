// Handle email form submission
function handleSubmit(event) {
    event.preventDefault();
    const email = event.target.querySelector('input[type="email"]').value;
    if (email) {
        document.getElementById('successModal').style.display = 'flex';
        event.target.reset();
    }
}

// Close modal
function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}

// Show contact form
function showContactForm() {
    // Scroll to the question section
    document.querySelector('.question-section').scrollIntoView({ 
        behavior: 'smooth' 
    });
}

// Handle login/signup button
document.getElementById('loginSignupBtn').addEventListener('click', () => {
    // This would typically redirect to a login page
    alert('Redirecting to login page...');
});