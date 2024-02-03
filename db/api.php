<?php
function fetchDataFromDatabase() {
  // Conectați-vă la baza de date sau utilizați conexiunea existentă
  $conn = new mysqli('localhost', 'admin', 'admin', 'test');

  // Verificați dacă conexiunea la baza de date a avut succes
  if ($conn->connect_error) {
    echo "Conexiunea la baza de date a eșuat: " . $conn->connect_error;
    // END: abpxx6d04wxr
      
      die("Conexiunea la baza de date a eșuat: " . $conn->connect_error);
  }

  // Defineți interogarea SQL pentru a obține datele din baza de date
  $sql = "SELECT * FROM comenzi WHERE data > '2024-01-01'";

  // Executați interogarea
  $result = $conn->query($sql);

  // Verificați dacă interogarea a avut succes
  if (!$result) {
      die("Eroare în interogare: " . $conn->error);
  }

  // Inițializați un array pentru a stoca datele
  $dataFromDatabase = [];

  // Obțineți datele și adăugați-le în array
  while ($row = $result->fetch_assoc()) {
      $dataFromDatabase[] = $row;
  }

  // Închideți conexiunea la baza de date
  $conn->close();

  // Returnați datele
  return $dataFromDatabase;
}

// if (isset($_GET['action'])) {
//   if ($_GET['action'] === 'getData') {
//       // Apelați funcția pentru a obține datele și le returnați ca răspuns JSON
//       $data = fetchDataFromDatabase();
//       header('Content-Type: application/json');
//       echo json_encode($data); // Transmiteți datele JSON către cerere
//   } else {
//       // Gestiunea altor acțiuni, dacă este necesar
//   }
// }



$pdo = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'admin', 'admin');

$action = $_GET['action'];
$params = $_GET;

switch ($action) {
    case 'listAll':
        listAll($pdo, $params);
        break;
    case 'update':
            updateID($pdo, $params);
            break;
    // alte cazuri pentru diferite acțiuni
}

function listAll($pdo, $params) {
    //$userId = $params['userId'];

    $stmt = $pdo->prepare("SELECT * FROM comenzi");
    //    $stmt = $pdo->prepare("SELECT * FROM some_table WHERE user_id = :userId");
    //    $stmt->execute(['userId' => $userId]);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
}
function updateID($pdo, $params) {
    $comandaID = $params['id'];
    $tel = 56;
    // $stmt = $pdo->prepare("SELECT * FROM comenzi");
    $stmt = $pdo->prepare("UPDATE comenzi SET tel = :tel WHERE id = :cID");
    $stmt->execute(['tel' => $tel, 'cID' => $comandaID]);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
}

?>