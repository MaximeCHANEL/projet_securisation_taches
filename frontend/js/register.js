const API_URL = window.location.hostname === "localhost"
    ? "http://localhost:8000"
    : "http://89.168.60.68:8000";

const registerForm = document.getElementById("registerForm");
const message = document.getElementById("message");
const loginButton = document.getElementById("loginButton");

registerForm.addEventListener("submit", async function (event) {

    event.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    // Vérification des mots de passe
    if (password !== confirmPassword) {
        message.textContent = "Les mots de passe ne correspondent pas.";
        return;
    }

    try {

        const response = await fetch(`${API_URL}/register`, {
            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                email: email,
                password: password
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.error || "Erreur lors de l'inscription"
            );
        }

        message.textContent = "Compte créé avec succès !";

        // Retour à la connexion après 1 seconde
        setTimeout(function () {
            window.location.href = "login.html";
        }, 1000);

    } catch (error) {

        console.error(error);

        message.textContent = error.message;
    }
});


// Bouton retour à la connexion
loginButton.addEventListener("click", function () {
    window.location.href = "login.html";
});