<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

//session_start();

// Verifică dacă utilizatorul este logat și are permisiunile de admin
// if (!isset($_SESSION['user_id']) || $_SESSION['permission_level'] != 'admin') {
//     header('Location: login.php');
//     exit;
// }

// Asigură-te că metoda de request este POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Conectarea la baza de date
    require '../db/connectDB.php';
    $conn = connectDBwithPDO('mobiromr');
    try {
        // Curățarea datelor de intrare
        $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
        $password = $_POST['password']; // Parola va fi hash-uită, deci nu necesită să fie curățată
        $permissionLevel = htmlspecialchars($_POST['permission_level'], ENT_QUOTES, 'UTF-8');
    
        // Criptarea parolei
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
        // Inserarea în baza de date folosind PDO
        $stmt = $conn->prepare("INSERT INTO user_accounts (username, password_hash, permission_level) VALUES (?, ?, ?)");
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $passwordHash);
        $stmt->bindParam(3, $permissionLevel);
        $stmt->execute();
    
        if ($stmt->rowCount() === 1) {
            echo "Utilizator adăugat cu succes.";
        } else {
            echo "Eroare la adăugarea utilizatorului.";
        }
    
    } catch (PDOException $e) {
        // Tratează orice eroare PDO care poate apărea
        echo "A apărut o eroare: " . $e->getMessage();
    } finally {
        // Închide conexiunea la baza de date
        $conn = null;
    }
    
} else {
    // Dacă metoda de request nu este POST, redirecționează către formular
    header('Location: add_user_form.php');
    exit;
}
?>
