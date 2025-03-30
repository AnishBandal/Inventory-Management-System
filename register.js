// Password toggle functionality
document.getElementById("togglePassword").addEventListener("click", function (event) {
    event.stopPropagation();
    const passwordInput = document.getElementById("password");
    const toggleIcon = document.getElementById("togglePassword");
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.replace("fa-eye-slash", "fa-eye");
    }
});

// Terms and conditions modal
document.getElementById("termsLink").addEventListener("click", function (event) {
    event.preventDefault();
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Terms and Conditions</h2>
            <ol>
                <li><strong>Account Responsibility:</strong> You are responsible for maintaining the confidentiality of your account.</li>
                <li><strong>Data Privacy:</strong> We collect and process your data in accordance with our Privacy Policy.</li>
                <li><strong>User Conduct:</strong> You agree to use this system for lawful purposes only.</li>
                <li><strong>Intellectual Property:</strong> All content is owned by the company.</li>
                <li><strong>Termination:</strong> We may terminate accounts that violate these terms.</li>
                <li><strong>Limitation of Liability:</strong> We are not liable for indirect damages.</li>
                <li><strong>Changes to Terms:</strong> Terms may be updated periodically.</li>
            </ol>
            <button class="accept-btn">I Understand</button>
        </div>
    `;
    document.body.appendChild(modal);
    
    // Close modal handlers
    modal.querySelector('.close').addEventListener('click', () => modal.remove());
    modal.querySelector('.accept-btn').addEventListener('click', () => {
        document.getElementById("termsAndCondition").checked = true;
        modal.remove();
    });
});

// Password validation
function validatePassword() {
    const password = document.getElementById("password").value;
    const passwordError = document.getElementById("passwordError");
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    
    if (password.length > 0 && !regex.test(password)) {
        passwordError.textContent = "Password must be at least 8 characters with uppercase, lowercase, number, and special character.";
        return false;
    } else {
        passwordError.textContent = "";
        return true;
    }
}

document.getElementById("password").addEventListener("input", validatePassword);

// Form submission with AJAX
document.getElementById("register-form").addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Validate inputs
    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const phoneNumber = document.getElementById("phoneNumber").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const termsAndCondition = document.getElementById("termsAndCondition").checked;
    
    // Client-side validation
    if (!firstName || !lastName || !phoneNumber || !email || !password) {
        showAlert("Please fill out all fields", "error");
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Register';
        return;
    }
    
    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
        showAlert("Please enter a valid email address", "error");
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Register';
        return;
    }
    
    if (phoneNumber.length < 10 || !/^\d+$/.test(phoneNumber)) {
        showAlert("Please enter a valid phone number", "error");
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Register';
        return;
    }
    
    if (!validatePassword()) {
        showAlert("Please fix password requirements", "error");
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Register';
        return;
    }
    
    if (!termsAndCondition) {
        showAlert("Please accept terms and conditions", "error");
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Register';
        return;
    }
    
    // Prepare form data
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('register.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert("Registration successful! Redirecting...", "success");
            
            // Redirect after a brief delay
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showAlert(data.message || "Registration failed", "error");
        }
    } catch (error) {
        showAlert("Network error occurred", "error");
        console.error('Error:', error);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Register';
    }
});

// Helper function to show alerts
function showAlert(message, type) {
    const alertBox = document.createElement('div');
    alertBox.className = `alert ${type}`;
    alertBox.textContent = message;
    document.body.appendChild(alertBox);
    
    setTimeout(() => {
        alertBox.classList.add('fade-out');
        setTimeout(() => alertBox.remove(), 500);
    }, 3000);
}