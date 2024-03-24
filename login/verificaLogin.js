// verificaLogin.js
document.getElementById('loginForm').addEventListener('submit', function(e) {
  e.preventDefault();
  // Aici ai verifica credențialele, de exemplu, printr-un request AJAX către server.
  // Dacă loginul este corect, redirecționezi utilizatorul.
  window.location.href = 'index.html'; // Pagina principală după login.
});
