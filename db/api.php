<?php
function fetchDataFromDatabase() {
  // Conectați-vă la baza de date sau utilizați conexiunea existentă
  $conn = new mysqli('localhost', 'admin', 'admin', 'test');

  // Verificați dacă conexiunea la baza de date a avut succes
  if ($conn->connect_error) {
      die("Conexiunea la baza de date a eșuat: " . $conn->connect_error);
  }

  // Defineți interogarea SQL pentru a obține datele din baza de date
  $sql = "SELECT * FROM tabela";

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

if (isset($_GET['action'])) {
  if ($_GET['action'] === 'getData') {
      // Apelați funcția pentru a obține datele și le returnați ca răspuns JSON
      $data = array(
        "nume" => "Valoare 1",
        "altNume" => "Valoare 2"
    );
      header('Content-Type: application/json');
      echo json_encode($data); // Transmiteți datele JSON către cerere
  } else {
      // Gestiunea altor acțiuni, dacă este necesar
  }
}


?>