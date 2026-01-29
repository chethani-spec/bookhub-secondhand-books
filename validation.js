// Form Validation Functions

// Email validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Phone validation
function validatePhone(phone) {
    const re = /^[0-9]{10}$/;
    return re.test(phone);
}

// Password strength validation
function validatePassword(password) {
    return password.length >= 6;
}

// Display error message
function showError(input, message) {
    const formGroup = input.parentElement;
    let error = formGroup.querySelector('.error');
    
    if (!error) {
        error = document.createElement('small');
        error.className = 'error';
        formGroup.appendChild(error);
    }
    
    error.textContent = message;
    input.style.borderColor = '#ff6b6b';
}

// Clear error message
function clearError(input) {
    const formGroup = input.parentElement;
    const error = formGroup.querySelector('.error');
    
    if (error) {
        error.remove();
    }
    
    input.style.borderColor = '#e0e0e0';
}

// Registration Form Validation
function validateRegistrationForm() {
    const form = document.getElementById('registerForm');
    
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Username validation
        const username = document.getElementById('username');
        if (username.value.trim().length < 3) {
            e.preventDefault();
            showError(username, 'Username must be at least 3 characters long');
            isValid = false;
        } else {
            clearError(username);
        }
        
        // Email validation
        const email = document.getElementById('email');
        if (!validateEmail(email.value.trim())) {
            e.preventDefault();
            showError(email, 'Please enter a valid email address');
            isValid = false;
        } else {
            clearError(email);
        }
        
        // Password validation
        const password = document.getElementById('password');
        if (!validatePassword(password.value)) {
            e.preventDefault();
            showError(password, 'Password must be at least 6 characters long');
            isValid = false;
        } else {
            clearError(password);
        }
        
        // Confirm password validation
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword && password.value !== confirmPassword.value) {
            e.preventDefault();
            showError(confirmPassword, 'Passwords do not match');
            isValid = false;
        } else if (confirmPassword) {
            clearError(confirmPassword);
        }
        
        // Phone validation
        const phone = document.getElementById('phone');
        if (phone && phone.value.trim() !== '' && !validatePhone(phone.value.trim())) {
            e.preventDefault();
            showError(phone, 'Please enter a valid 10-digit phone number');
            isValid = false;
        } else if (phone) {
            clearError(phone);
        }
        
        return isValid;
    });
}

// Login Form Validation
function validateLoginForm() {
    const form = document.getElementById('loginForm');
    
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Username validation
        const username = document.getElementById('username');
        if (username.value.trim() === '') {
            e.preventDefault();
            showError(username, 'Username is required');
            isValid = false;
        } else {
            clearError(username);
        }
        
        // Password validation
        const password = document.getElementById('password');
        if (password.value === '') {
            e.preventDefault();
            showError(password, 'Password is required');
            isValid = false;
        } else {
            clearError(password);
        }
        
        return isValid;
    });
}

// Book Form Validation
function validateBookForm() {
    const form = document.getElementById('bookForm');
    
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Title validation
        const title = document.getElementById('title');
        if (title.value.trim().length < 3) {
            e.preventDefault();
            showError(title, 'Book title must be at least 3 characters long');
            isValid = false;
        } else {
            clearError(title);
        }
        
        // Author validation
        const author = document.getElementById('author');
        if (author.value.trim() === '') {
            e.preventDefault();
            showError(author, 'Author name is required');
            isValid = false;
        } else {
            clearError(author);
        }
        
        // Price validation
        const price = document.getElementById('price');
        if (price.value === '' || parseFloat(price.value) <= 0) {
            e.preventDefault();
            showError(price, 'Please enter a valid price');
            isValid = false;
        } else {
            clearError(price);
        }
        
        // Description validation
        const description = document.getElementById('description');
        if (description.value.trim().length < 10) {
            e.preventDefault();
            showError(description, 'Description must be at least 10 characters long');
            isValid = false;
        } else {
            clearError(description);
        }
        
        return isValid;
    });
}

// Order Form Validation
function validateOrderForm() {
    const form = document.getElementById('orderForm');
    
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Delivery address validation
        const address = document.getElementById('delivery_address');
        if (address.value.trim().length < 10) {
            e.preventDefault();
            showError(address, 'Please enter a complete delivery address');
            isValid = false;
        } else {
            clearError(address);
        }
        
        return isValid;
    });
}

// Initialize validations when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    validateRegistrationForm();
    validateLoginForm();
    validateBookForm();
    validateOrderForm();
    
    // Image preview for book upload
    const imageInput = document.getElementById('cover_image');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.getElementById('imagePreview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = 'imagePreview';
                        preview.style.maxWidth = '200px';
                        preview.style.marginTop = '10px';
                        preview.style.borderRadius = '10px';
                        imageInput.parentElement.appendChild(preview);
                    }
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
});