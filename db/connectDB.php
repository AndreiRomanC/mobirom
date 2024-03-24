<?php
function connectDatabase() {
  // Conectați-vă la baza de date sau utilizați conexiunea existentă
  $conn = new mysqli('localhost', 'admin', 'admin', 'test');

  // Verificați dacă conexiunea la baza de date a avut succes
  if ($conn->connect_error) {
    echo "Conexiunea la baza de date a eșuat: " . $conn->connect_error;
    // END: abpxx6d04wxr
    die("Conexiunea la baza de date a eșuat: " . $conn->connect_error);
  }

  return $conn;
}

function connectDBwithPDO($dbName) {
  $host = 'localhost';
  $user = 'admin';
  $password = 'admin'; // Asigură-te că aceasta este parola corectă pentru utilizatorul bazei de date
  $charset = 'utf8mb4';

  $dsn = "mysql:host=$host;dbname=$dbName;charset=$charset";
  $options = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
  ];

  try {
      $pdo = new PDO($dsn, $user, $password, $options);
      return $pdo;
  } catch (\PDOException $e) {
      throw new \PDOException($e->getMessage(), (int)$e->getCode());
  }
}



?>