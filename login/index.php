<?php
session_start(); // Inițializează sesiunea PHP

include_once '../db/connectDB.php'; // Include fișierul de conectare

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Conectează-te la baza de date folosind funcția definită
        $pdo = connectDBwithPDO('mobiromr'); // Înlocuiește 'numele_bazei_de_date' cu numele real al bazei tale de date

        // Prepară și execută interogarea SQL într-un mod sigur
        $stmt = $pdo->prepare("SELECT * FROM user_accounts WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            // Autentificare reușită, setează datele utilizatorului în sesiune
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['permission_level'] = $user['permission_level'];

            // Redirecționează către pagina principală sau dashboard
            header("Location: ../index.php");
            exit();
        } else {
            // Autentificare eșuată
            $error = "Nume de utilizator sau parolă incorectă.". $user['id'].$username;

        }
    } catch (\PDOException $e) {
        // Gestionează excepțiile, cum ar fi erori de conectare la baza de date
        $error = "Eroare la conectarea la baza de date: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="login.css">
    <title>Login</title>
    <!-- Adaugă aici stilurile necesare -->
</head>
<body>
    <div id="loginForm">
        <form action="index.php" method="post">
            <h2>Login</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <div>
                <label for="username">Nume utilizator:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div>
                <label for="password">Parolă:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div>
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
