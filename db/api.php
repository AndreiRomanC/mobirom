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

if (isset($_GET['action'])) {
  if ($_GET['action'] === 'getData') {
      // Apelați funcția pentru a obține datele și le returnați ca răspuns JSON
      $data = fetchDataFromDatabase();
      header('Content-Type: application/json');
      echo json_encode($data); // Transmiteți datele JSON către cerere
  } else {
      // Gestiunea altor acțiuni, dacă este necesar
  }
}

$pdo = new PDO('mysql:host=localhost;dbname=mobiromr;charset=utf8', 'admin', 'admin');

$action = $_GET['action'];
$params = $_GET['comandaNouaParam'];
print($params);
switch ($action) {
    case 'listAll':
        listAll($pdo, $params);
        break;
    case 'update':
            updateID($pdo, $params);
            break;
    case 'addNewOrder':
        print($params);
        $params = json_decode($params, true); // true pentru a obține un array asociativ

        if (is_array($params)) {
            // Acum $params este un array și poți accesa elementele sale
            addNewOrder($pdo, $params);
        } else {
            // Gestionarea erorii în cazul în care JSON-ul nu poate fi decodat
            echo "Eroarea la decodarea JSON.";
        }
        //addNewOrder($pdo, $params);
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

function addNewOrder($pdo, $params) {
    try {
        // Începeți o tranzacție
         $orderData = $params;
        header('Content-Type: application/json');

       //print($params);

       // Adăugați comanda în tabela comenzi
       try {
        // Începeți o tranzacție
        $pdo->beginTransaction();
    
        // Adăugați comanda în tabela comenzi
        $stmt = $pdo->prepare("INSERT INTO comenzi (client, telefon, data, termenLivrare, urgenta, status, note, detalii, total) VALUES (:client, :telefon, :data, :termenLivrare, :urgenta, :status, :note, :detalii, :total)");
        $stmt->execute([
            'client' => $orderData['client'],
            'telefon' => $orderData['telefon'],
            'data' => $orderData['data'],
            'termenLivrare' => $orderData['termenLivrare'],
            'urgenta' => $orderData['urgenta'],
            'status' => $orderData['status'],
            'note' => $orderData['note'],
            'detalii' => $orderData['detalii'],
            'total' => $orderData['total']
        ]);
        print("sunt in try de la insert ");
        // Obțineți ID-ul comenzii inserate
        $comandaID = 5;
    
        // Aici puteți adăuga cod pentru a insera produsele asociate comenzii, dacă este cazul
    
        // Confirmați tranzacția
        $pdo->commit();
    
        // Dacă totul a mers bine, puteți răspunde cu succes
        // De exemplu: echo json_encode(['success' => true, 'comandaID' => $comandaID]);
    } catch (PDOException $e) {
        // Revocați tranzacția pentru a menține integritatea datelor
        $pdo->rollBack();
    
        // Logați eroarea sau gestionați-o cum considerați necesar
        print('Eroare la inserarea comenzii: ' . $e->getMessage());
    
        // Răspundeți cu un mesaj de eroare
        // Într-un mediu de producție, evitați să expuneți mesajele de eroare direct utilizatorului
        echo json_encode(['success' => false, 'message' => 'A apărut o eroare la procesarea comenzii.']);
    
        // Opțional, puteți alege să aruncați excepția mai departe sau să finalizați execuția scriptului
        // throw $e;
        // sau
        // exit;
    }

        // //Construim un array asociativ cu ID-ul comenzii
        // $responseData = array('comandaID' => $comandaID);
     //   Adăugați produsele asociate comenzii în tabela produse
        // foreach ($orderData['produse'] as $produs) {
        //     $stmt = $pdo->prepare("INSERT INTO produse (comanda_id, nume, cantitate, valoare, etapaFabricatie) VALUES (:comanda_id, :nume, :cantitate, :valoare, :etapaFabricatie)");
        //     $stmt->execute([
        //         'comanda_id' => $comandaID,
        //         'nume' => $produs['nume'],
        //         'cantitate' => $produs['cantitate'],
        //         'valoare' => $produs['valoare'],
        //         'etapaFabricatie' => $produs['etapaFabricatie']
        //     ]);
        // }

        // // Confirmați tranzacția
         // $pdo->commit();
        // header('Content-Type: application/json');
        // // Returnați ID-ul comenzii adăugate sau altă informație relevantă
       echo json_encode($params);
    } catch (PDOException $e) {
        // În caz de eroare, revocați tranzacția
        $pdo->rollBack();
        throw $e; // Tratați eroarea în mod corespunzător sau relansați-o pentru a fi tratată mai sus
    }
}

?>