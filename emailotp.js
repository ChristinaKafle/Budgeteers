function SendOTP() {
    const email = document.getElementById("email").value;

    if (!email) {
        alert("Please enter a valid email address.");
        return;
    }

    fetch("send_otp.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "email=" + encodeURIComponent(email)
    })
    .then(response => response.text())
    .then(data => {
        if (data === "OTP sent successfully") {
            alert("OTP sent to your email.");
            document.getElementsByClassName("email-verify")[0].style.display = "block";
        } else {
            alert("Failed to send OTP. Try again.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error sending the OTP.');
    });
}

// ✅ OUTSIDE SendOTP
document.getElementById("btn-verify-otp").addEventListener("click", function() {
    const otp = document.getElementById("otp-input").value;

    fetch("verify_otp.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "otp=" + encodeURIComponent(otp)
    })
    .then(response => response.text())
    .then(data => {
        if (data === "OTP verified") {
            alert("OTP verified successfully!");
        } else {
            alert("Invalid OTP. Please try again.");
        }
    })
    .catch(error => {
        console.error("Error verifying OTP:", error);
        alert("Error occurred while verifying OTP.");
    });
});
