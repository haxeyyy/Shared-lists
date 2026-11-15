const form = document.getElementById('loginForm')
          || document.getElementById('registrationForm');

if (form) {
  const isRegistration = form.id === 'registrationForm';

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorBox = document.getElementById('errorMessage');
    errorBox.textContent = '';

    try {
      const action = isRegistration ? 'register' : 'login';

      const response = await fetch(`../api/auth.php?action=${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });

      const result = await response.json();

      if (response.ok) {
        window.location.href = 'dashboard.html';
      } else {
        errorBox.textContent = result.error || 'Request failed';
      }
    } catch (error) {
      errorBox.textContent = 'Server connection error';
    }
  });
}
