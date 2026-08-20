const API_URL = window.location.hostname === "localhost"
    ? "http://localhost:8000"
    : "http://89.168.60.68:8000";

const loginForm = document.getElementById("loginForm");
const message = document.getElementById("message");

loginForm.addEventListener("submit", async function(event) {

    event.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {

        const response = await fetch(`${API_URL}/login`, {
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
                data.error || "Erreur lors de la connexion"
            );
        }

        // Enregistrement du token
        localStorage.setItem("token", data.token);

        message.textContent = "Connexion réussie !";

        // Redirection vers les tâches
        window.location.href = "taches.html";

    } catch (error) {

        console.error(error);

        message.textContent = error.message;
    }

});

const registerButton = document.getElementById("registerButton");

registerButton.addEventListener("click", function () {
    window.location.href = "register.html";
});