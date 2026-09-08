async function logout() {
    const token = localStorage.getItem('token');

    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    try {
        await fetch('http://89.168.60.68:8000/logout', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
    } catch (error) {
        console.error('Erreur lors de la déconnexion :', error);
    }

    // Suppression du token côté navigateur
    localStorage.removeItem('token');

    // Retour à la page de connexion
    window.location.href = 'login.html';
}

document
    .getElementById('logoutButton')
    .addEventListener('click', logout);