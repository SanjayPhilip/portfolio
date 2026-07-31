document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation Toggle
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }
    
    // Age validation for registration
    const dobInput = document.getElementById('dob');
    const registerForm = document.getElementById('register-form');
    
    if (registerForm && dobInput) {
        registerForm.addEventListener('submit', function(e) {
            const dob = new Date(dobInput.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            
            if (age < 18) {
                e.preventDefault();
                alert('You must be at least 18 years old to register.');
                return false;
            }
        });
    }
    
    // Blood request form validation
    const requestForm = document.getElementById('request-form');
    
    if (requestForm) {
        requestForm.addEventListener('submit', function(e) {
            const units = document.getElementById('units').value;
            const urgency = document.getElementById('urgency').value;
            
            if (isNaN(units) || units <= 0) {
                e.preventDefault();
                alert('Please enter a valid number of units.');
                return false;
            }
            
            if (!urgency) {
                e.preventDefault();
                alert('Please select the urgency level.');
                return false;
            }
        });
    }
    
    // Donation form validation
    const donateForm = document.getElementById('donate-form');
    
    if (donateForm) {
        donateForm.addEventListener('submit', function(e) {
            const lastDonation = document.getElementById('last_donation').value;
            
            if (lastDonation) {
                const lastDonationDate = new Date(lastDonation);
                const today = new Date();
                const diffTime = Math.abs(today - lastDonationDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays < 90) {
                    e.preventDefault();
                    alert('You must wait at least 90 days between blood donations.');
                    return false;
                }
            }
        });
    }
    
    // Response to request confirmation
    const respondButtons = document.querySelectorAll('.respond-btn');
    
    if (respondButtons.length > 0) {
        respondButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to respond to this blood request?')) {
                    return true;
                } else {
                    return false;
                }
            });
        });
    }
    
    // Fade in animations
    const fadeElements = document.querySelectorAll('.fade-in');
    
    if (fadeElements.length > 0) {
        const fadeInOptions = {
            threshold: 0.3,
            rootMargin: "0px 0px -100px 0px"
        };
        
        const fadeInObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('appear');
                    observer.unobserve(entry.target);
                }
            });
        }, fadeInOptions);
        
        fadeElements.forEach(element => {
            fadeInObserver.observe(element);
        });
    }
}); 