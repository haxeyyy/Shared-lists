document.getElementById('loginForm').addEventListener('submit', async function (e) {

    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorBox = document.getElementById('errorMessage');
    errorBox.textContent = '';

    try {
        const response = await fetch('../api/auth.php?action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        });

        const result = await response.json();

        if (response.ok) {

            window.location.href = 'dashboard.html';
        } else {

            errorBox.textContent = result.error || 'Login failed';
        }
    } catch (error) {
        errorBox.textContent = 'Server connection error';
    }
});