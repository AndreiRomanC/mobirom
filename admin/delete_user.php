<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'];

    // Aici conectezi la baza de date cu PDO
    require '../db/connectDB.php';
    $conn = connectDBwithPDO('mobiromr');

    $query = "DELETE FROM user_accounts WHERE id = :userId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "Utilizatorul a fost șters.";
    } else {
        echo "Nu s-a putut șterge utilizatorul.";
    }
}
