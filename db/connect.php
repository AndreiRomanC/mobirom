<?php
// Conectarea la baza de date MySQL
$servername = "localhost";
$username = "utilizator";
$password = "parola";
$dbname = "nume_baza_de_date";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Conexiunea la baza de date a eșuat: " . $conn->connect_error);
}

// Obținerea comenzilor din baza de date
$sql = "SELECT comanda, client, data, status_comanda FROM comenzi";
$result = $conn->query($sql);

// Verificarea și afișarea comenzilor
if ($result->num_rows > 0) {
  echo "<ul>";
  while ($row = $result->fetch_assoc()) {
    echo "<li><a href='detalii_comanda.php?id=" . $row["id"] . "'>" . $row["comanda"] . "</a></li>";
  }
  echo "</ul>";
} else {
  echo "Nu există comenzi în baza de date.";
}

// Închiderea conexiunii cu baza de date
$conn->close();
?>
