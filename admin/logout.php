<?php
// Începe sesiunea pentru a avea acces la variabilele de sesiune
session_start();

// Golirea tuturor variabilelor de sesiune
session_unset();

// Distrugerea sesiunii
session_destroy();

// Verificare dacă există un cookie de sesiune și ștergerea acestuia
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Redirecționarea către pagina de login sau homepage
header('Location: ../index.php');
exit;
?>