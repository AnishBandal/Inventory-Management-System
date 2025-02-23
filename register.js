function validatePassword()
{
    let password = document.getElementById('password').value;
    let passwordError = document.getElementById('passwordError');

    if(password.length < 8)
    {
        passwordError.textContent = "Your password is to short"
    }
    else{
        passwordError.textContent = ""
    }
};

document.getElementById("password").addEventListener("input", validatePassword);



