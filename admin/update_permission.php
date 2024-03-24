<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_reporting(0); // Ascunde erorile PHP pentru a evita coruperea răspunsului JSON
    header('Content-Type: application/json');

    // Decodifică datele primite în format JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['userId'];
    $newPermissionLevel = $data['permissionLevel'];

    // Conectare la baza de date
    require '../db/connectDB.php';
    $conn = connectDBwithPDO('mobiromr');

    $query = "UPDATE user_accounts SET permission_level = :newPermissionLevel WHERE id = :userId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':newPermissionLevel', $newPermissionLevel, PDO::PARAM_INT);
    $stmt->execute();

    // Verifică dacă actualizarea a avut loc și setează mesajul corespunzător
    if ($stmt->rowCount() > 0) {
        $response = ['success' => true, 'message' => 'Nivelul de permisiune a fost actualizat.'];
    } else {
        $response = ['success' => false, 'message' => 'Nu s-au făcut actualizări sau utilizatorul nu a fost găsit.'];
    }
} else {
    $response = ['success' => false, 'message' => 'Metodă neacceptată.'];
}

echo json_encode($response); // Trimite răspunsul JSON
