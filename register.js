document.getElementById("togglePassword").addEventListener("click", function (event) {
    event.stopPropagation(); // Prevent the input field from gaining focus
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

document.getElementById("termsLink").addEventListener("click", function (event) {
    event.preventDefault();
    alert("Terms and Conditions:\n\n1. Account Responsibility...\n2. Data Privacy...\n3. User Conduct...\n4. Intellectual Property...\n5. Termination...\n6. Limitation of Liability...\n7. Changes to Terms...");
});

function validatePassword() {
    const password = document.getElementById("password").value;
    const passwordError = document.getElementById("passwordError");
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!regex.test(password)) {
        passwordError.textContent = "Password must be at least 8 characters long and include uppercase, lowercase, numbers, and special characters.";
    } else {
        passwordError.textContent = "";
    }
}

document.getElementById("password").addEventListener("input", validatePassword);


//For Validating inputs
document.getElementById("register-form").addEventListener("submit", (e) => {
    let firstName = document.getElementById("firstName").value.trim();
    let lastName = document.getElementById("lastName").value.trim();
    let phoneNumber = document.getElementById("phoneNumber").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let termsAndCondition = document.getElementById("termsAndCondition").checked;


    let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/; // Regex for email validation

    if (firstName == "" || lastName == "" || phoneNumber == "" || email == "" || password == "") {
      alert("Please fill out all the details!");
      e.preventDefault();
      return;
    }

    if (!emailRegex.test(email)) {
      alert("Please enter a valid email Address!");
      e.preventDefault();
      return;
    }

    if (phoneNumber.length < 10) {
      alert("Please enter valid phone number!");
      e.preventDefault();
      return;
    }

    if(!termsAndCondition){
        alert("Please accept terms and conditions");
        e.preventDefault();
        return;
    }

  });
