const API_URL = window.location.hostname === "localhost"
    ? "http://localhost:8000"
    : "http://89.168.60.68:8000";

// Récupération du token
const token = localStorage.getItem("token");

// Si aucun token n'est présent,
// retour vers la page de connexion
if (!token) {
    window.location.href = "login.html";
}


/*
    * Fonction permettant de récupérer
    * les headers avec le token
    */
function getHeaders() {

    return {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${token}`
    };

}


/*
    * Récupération des tâches
    */
async function loadTasks() {

    const tasksList = document.getElementById("tasksList");

    try {

        const response = await fetch(
            `${API_URL}/tasks`,
            {
                method: "GET",
                headers: getHeaders()
            }
        );

        const data = await response.json();

        // Token invalide ou expiré
        if (response.status === 401) {

            localStorage.removeItem("token");

            window.location.href = "login.html";

            return;
        }

        if (!response.ok) {

            throw new Error(
                data.error || "Impossible de récupérer les tâches"
            );

        }

        /*
            * On suppose que l'API renvoie
            * directement un tableau :
            *
            * [
            *   {
            *      "id": 1,
            *      "title": "...",
            *      "description": "..."
            *   }
            * ]
            */
        const tasks = Array.isArray(data)
            ? data
            : data.tasks || data.data || [];

        displayTasks(tasks);

    } catch (error) {

        console.error(error);

        tasksList.innerHTML =
            `<p>${error.message}</p>`;

    }

}


/*
    * Affichage des tâches
    */
function displayTasks(tasks) {

    const tasksList =
        document.getElementById("tasksList");

    if (tasks.length === 0) {

        tasksList.innerHTML =
            "<p>Aucune tâche.</p>";

        return;
    }

    tasksList.innerHTML = "";

    tasks.forEach(function(task) {

        const div = document.createElement("div");

        div.innerHTML = `
            <h3>${task.titre}</h3>

            <p>
                ${task.description || ""}
            </p>

            <button onclick="editTask(${task.id})">
                Modifier
            </button>

            <button onclick="deleteTask(${task.id})">
                Supprimer
            </button>

            <hr>
        `;

        tasksList.appendChild(div);

    });

}


/*
    * Ajout d'une tâche
    */
document
    .getElementById("taskForm")
    .addEventListener("submit", async function(event) {

        event.preventDefault();

        const titre =
            document.getElementById("titre").value;

        const description =
            document.getElementById("description").value;

        try {

            const response = await fetch(
                `${API_URL}/tasks`,
                {
                    method: "POST",

                    headers: getHeaders(),

                    body: JSON.stringify({
                        titre: titre,
                        description: description
                    })
                }
            );

            const data = await response.json();

            if (!response.ok) {

                throw new Error(
                    data.error ||
                    "Impossible de créer la tâche"
                );

            }

            document.getElementById("message")
                .textContent =
                "Tâche créée avec succès.";

            document.getElementById("taskForm").reset();

            loadTasks();

        } catch (error) {

            console.error(error);

            document.getElementById("message")
                .textContent = error.message;

        }

    });


/*
    * Modification d'une tâche
    */
async function editTask(id) {

    const titre =
        prompt("Nouveau titre :");

    if (titre === null) {
        return;
    }

    const description =
        prompt("Nouvelle description :");

    if (description === null) {
        return;
    }

    try {

        const response = await fetch(
            `${API_URL}/tasks/${id}`,
            {
                method: "PUT",

                headers: getHeaders(),

                body: JSON.stringify({
                    titre: titre,
                    description: description
                })
            }
        );

        const data = await response.json();

        if (!response.ok) {

            throw new Error(
                data.error ||
                "Impossible de modifier la tâche"
            );

        }

        document.getElementById("message")
            .textContent =
            "Tâche modifiée avec succès.";

        loadTasks();

    } catch (error) {

        console.error(error);

        document.getElementById("message")
            .textContent = error.message;

    }

}


/*
    * Suppression d'une tâche
    */
async function deleteTask(id) {

    const confirmation =
        confirm(
            "Voulez-vous vraiment supprimer cette tâche ?"
        );

    if (!confirmation) {
        return;
    }

    try {

        const response = await fetch(
            `${API_URL}/tasks/${id}`,
            {
                method: "DELETE",
                headers: getHeaders()
            }
        );

        const data = await response.json();

        if (!response.ok) {

            throw new Error(
                data.error ||
                "Impossible de supprimer la tâche"
            );

        }

        document.getElementById("message")
            .textContent =
            "Tâche supprimée avec succès.";

        loadTasks();

    } catch (error) {

        console.error(error);

        document.getElementById("message")
            .textContent = error.message;

    }

}


/*
    * Déconnexion
    */
document
    .getElementById("logoutButton")
    .addEventListener("click", function() {

        localStorage.removeItem("token");

        window.location.href = "login.html";

    });


// Chargement initial des tâches
loadTasks();