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
print($params);
switch ($action) {
    case 'fetchFromMysql':
        fetchFromMysql($pdo, $params);
        break;

    case 'listAll':
        listAll($pdo, $params);
        break;
    case 'update':       
            updateID($pdo, $params);
            break;
    case 'deleteOrder':       
        $params = $_GET['id'];
        print($params);
       // $params = json_decode($params, true); // true pentru a obține un array asociativ
       deleteOrder($pdo, $params);

                break;
    case 'addNewOrder':
        $params = $_GET['comandaNouaParam'];
        print($params);
        $params = json_decode($params, true); // true pentru a obține un array asociativ

        if (is_array($params)) {
            // Acum $params este un array și poți accesa elementele sale
            addNewOrder($pdo, $params);
        } else {
            // Gestionarea erorii în cazul în care JSON-ul nu poate fi decodat
            echo "Eroarea la decodarea JSON. din api.php";
        }
        //addNewOrder($pdo, $params);
        break;
     case "newOrdersFromSite":
        $pdo2 = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'admin', 'admin');
        newOrders($pdo2, $params);
        break;

     case 'updateOrder':
            $params = $_GET['comandaMod'];
            print($params);
            $params = json_decode($params, true); // true pentru a obține un array asociativ
    
            if (is_array($params)) {
                // Acum $params este un array și poți accesa elementele sale
                updateOrder($pdo, $params);
            } else {
                // Gestionarea erorii în cazul în care JSON-ul nu poate fi decodat
                echo "Eroarea la decodarea JSON din api.php de la updateorder.";
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

        // Adăugați comanda în tabela comenzi
        $pdo->beginTransaction();
            // Asigurați-vă că toate datele necesare sunt prezente
        if (!isset($orderData['client'], $orderData['telefon'], $orderData['data'], $orderData['termenLivrare'], $orderData['urgenta'], $orderData['status'], $orderData['note'], $orderData['detalii'], $orderData['total'], $orderData['produse'])) {
            throw new Exception("Lipsesc date necesare pentru inserarea comenzii.");
        }

        $urgentaValue = $orderData['urgenta'] ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO comenzi (id, client, telefon, data, termenLivrare, urgenta, status, note, detalii, total) VALUES (:id, :client, :telefon, :data, :termenLivrare, :urgenta, :status, :note, :detalii, :total)");
        $stmt->execute([
            'id' => $orderData['id'],
            'client' => $orderData['client'],
            'telefon' => $orderData['telefon'],
            'data' => $orderData['data'],
            'termenLivrare' => $orderData['termenLivrare'],
            'urgenta' => $urgentaValue,
            'status' => $orderData['status'],
            'note' => $orderData['note'],
            'detalii' => $orderData['detalii'],
            'total' => $orderData['total']
        ]);

            // Obțineți ID-ul comenzii inserate
    $comandaID = $pdo->lastInsertId();

    // Inserarea produselor asociate comenzii

        
        foreach ($orderData['produse'] as $produs) {
            if (!isset($produs['nume'], $produs['cantitate'], $produs['valoare'], $produs['etapaFabricatie'])) {
                throw new Exception("Lipsesc date necesare pentru inserarea produselor.");
            }
            $stmt = $pdo->prepare("INSERT INTO produse (comanda_id, nume, cantitate, valoare, etapaFabricatie) VALUES (:comanda_id, :nume, :cantitate, :valoare, :etapaFabricatie)");
            $stmt->execute([
                'comanda_id' => $comandaID,
                'nume' => $produs['nume'],
                'cantitate' => $produs['cantitate'],
                'valoare' => $produs['valoare'],
                'etapaFabricatie' => $produs['etapaFabricatie']
            ]);
        }
        // Confirmați tranzacția
        $pdo->commit();

        // Afișați un mesaj de succes în răspunsul JSON
        return json_encode(['success' => true, 'message' => 'Comanda a fost adăugată cu succes.']);
    } catch (PDOException $e) {
        // Revocați tranzacția pentru a menține integritatea datelor
        $pdo->rollBack();
    
        // Afișați un mesaj de eroare în răspunsul JSON
        echo json_encode(['success' => false, 'message' => 'Eroare la inserarea comenzii: ' . $e->getMessage()]);
    }
}

function fetchFromMysql($pdo, $params) {
    // Preia toate comenzile
    $stmt = $pdo->prepare("SELECT * FROM comenzi");
    $stmt->execute();
    $comenzi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Iterează prin fiecare comandă pentru a prelua produsele corespunzătoare
    foreach ($comenzi as $index => $comanda) {
        $stmtProduse = $pdo->prepare("SELECT * FROM produse WHERE comanda_id = :comanda_id");
        $stmtProduse->execute(['comanda_id' => $comanda['id']]);
        $produse = $stmtProduse->fetchAll(PDO::FETCH_ASSOC);

        // Conversia valorii 'urgenta' din numeric în boolean pentru coerență cu structura dorită
        $comenzi[$index]['urgenta'] = $comanda['urgenta'] ? true : false;

        // Adaugă produsele la comandă
        $comenzi[$index]['produse'] = $produse;
    }

    // Returnează toate comenzile și produsele lor ca JSON
    echo json_encode($comenzi);
}


function updateOrder($pdo, $params) {
    try {
        // Începeți o tranzacție
        $pdo->beginTransaction();
        header('Content-Type: application/json');

        // Verifică dacă toate datele necesare sunt prezente
        if (!isset($params['id'], $params['client'], $params['telefon'], $params['data'], $params['termenLivrare'], $params['urgenta'], $params['status'], $params['note'], $params['detalii'], $params['total'])) {
            throw new Exception("Lipsesc date necesare pentru actualizarea comenzii.");
        }

        $urgentaValue = $params['urgenta'] ? 1 : 0;

        // Actualizează comanda
        $stmt = $pdo->prepare("UPDATE comenzi SET client = :client, telefon = :telefon, data = :data, termenLivrare = :termenLivrare, urgenta = :urgenta, status = :status, note = :note, detalii = :detalii, total = :total WHERE id = :id");
        $stmt->execute([
            'id' => $params['id'],
            'client' => $params['client'],
            'telefon' => $params['telefon'],
            'data' => $params['data'],
            'termenLivrare' => $params['termenLivrare'],
            'urgenta' => $urgentaValue,
            'status' => $params['status'],
            'note' => $params['note'],
            'detalii' => $params['detalii'],
            'total' => $params['total']
        ]);

        // Presupunând că dorești să înlocuiești complet lista de produse, mai întâi șterge produsele existente
        $stmt = $pdo->prepare("DELETE FROM produse WHERE comanda_id = :comanda_id");
        $stmt->execute(['comanda_id' => $params['id']]);

        // Adaugă produsele noi (sau actualizate)
        foreach ($params['produse'] as $produs) {
            if (!isset($produs['nume'], $produs['cantitate'], $produs['valoare'], $produs['etapaFabricatie'])) {
                throw new Exception("Lipsesc date necesare pentru inserarea produselor.");
            }
            $stmt = $pdo->prepare("INSERT INTO produse (comanda_id, nume, cantitate, valoare, etapaFabricatie) VALUES (:comanda_id, :nume, :cantitate, :valoare, :etapaFabricatie)");
            $stmt->execute([
                'comanda_id' => $params['id'],
                'nume' => $produs['nume'],
                'cantitate' => $produs['cantitate'],
                'valoare' => $produs['valoare'],
                'etapaFabricatie' => $produs['etapaFabricatie']
            ]);
        }

        // Confirmați tranzacția
        $pdo->commit();

        // Afișați un mesaj de succes în răspunsul JSON
        echo json_encode(['success' => true, 'message' => 'Comanda a fost actualizată cu succes.']);
    } catch (PDOException $e) {
        // Revocați tranzacția pentru a menține integritatea datelor
        $pdo->rollBack();

        // Afișați un mesaj de eroare în răspunsul JSON
        echo json_encode(['success' => false, 'message' => 'Eroare la actualizarea comenzii: ' . $e->getMessage()]);
    }
}

function deleteOrder($pdo, $params) {
    try {
        // Începeți o tranzacție
        $pdo->beginTransaction();

        $comandaID = $params;

        // Mai întâi, ștergeți produsele asociate comenzii
        $stmt = $pdo->prepare("DELETE FROM produse WHERE comanda_id = :comandaID");
        $stmt->execute(['comandaID' => $comandaID]);

        // Apoi, ștergeți comanda
        $stmt = $pdo->prepare("DELETE FROM comenzi WHERE id = :comandaID");
        $stmt->execute(['comandaID' => $comandaID]);

        // Confirmați tranzacția
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Comanda și produsele asociate au fost șterse.']);
    } catch (PDOException $e) {
        // Revocați tranzacția în caz de eroare
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Eroare la ștergerea comenzii: ' . $e->getMessage()]);
    }
}
function newOrders($pdo2, $params) {
        // Obține data curentă
        $currentDate = new DateTime(); // Implicit, obține data de azi
                // Obține data de acum o lună
        $oneMonthAgo = $currentDate->sub(new DateInterval('P2M'))->format('Y-m-d');

        // Pregătește interogarea SQL cu clauza WHERE pentru a selecta comenzile din ultima lună
        $stmt = $pdo2->prepare("SELECT * FROM comenzi WHERE data_comanda > :oneMonthAgo");

        // Legarea parametrului
        $stmt->bindParam(':oneMonthAgo', $oneMonthAgo);

        // Execută interogarea
        $stmt->execute();

        // Obține rezultatele
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');

    echo json_encode($data);
}
?>