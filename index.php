<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Dacă nu este setat user_id în sesiune, redirecționează către pagina de login
    header("Location: .\login\index.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestionare Comenzi</title>
    <link rel="stylesheet" type="text/css" href="style_comenzi.css">
    <link rel="stylesheet" type="text/css" href="general.css">
    <script type="module" src="script.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="intro.css">

    <meta charset="UTF-8">

</head>
<body>
<div id="userInfo" data-username="<?php echo htmlspecialchars($_SESSION['username']); ?>" style="display: none;"></div>

<div class="menu">
    <a href="#" id="butonMeniu1" onclick="">Comanda Noua</a>
    <a href="#" id="butonMeniu3" onclick="">Comenzi noi de pe Site</a>
    <a href="#" id="butonMeniu2" onclick="">Actualizare server</a>
    <div class="accordion">
        <button class="accordion-button">Options</button>
        <div class="panel">
            <a href="./admin/logout.php" id="butonMeniu4">Logout</a>
            <a href="./admin/" id="adminButton">Admin</a>
        </div>
    </div>
</div>

    <div id="container">
      <div id="listaComenzi">
        <div id="formFiltrareContainer">
          <!-- Formularul de filtrare aici -->
          <form id="formFiltrare">
            <input type="date" id="dataStart" name="dataStart">
            <input type="date" id="dataEnd" name="dataEnd">
            <button id="sortButton" type="button">
               <i class="fa fa-sort"></i></button>
          </form>
            <form id="searchForm">
              <input type="text" id="searchInput" name="search" placeholder="Caută...">
              <button id="searchButton" type="submit"><i class="fa fa-search"></i></button>
            </form> 
            <select id="filterByStatus">
              <option value="all">Toate comenzile</option>
              <option value="Finalizată">Comenzi finalizate</option>
              <option value="În așteptare">Comenzi în așteptare</option>
              <option value="Urgente">Comenzi urgente</option>
              <option value="Anulată">Comenzi anulate</option>
            </select>         
        </div>
        <div id="comenziContainer">
          <!-- Lista de comenzi va fi aici -->
        </div>
      </div>
      <div id="detaliiComanda">
        <div class="container">
          <div class="letters-container">
            <div class="letter" style="--delay: 0s;">W</div>
            <div class="letter" style="--delay: 0.1s;">E</div>
            <div class="letter" style="--delay: 0.2s;">L</div>
            <div class="letter" style="--delay: 0.3s;">C</div>
            <div class="letter" style="--delay: 0.4s;">O</div>
            <div class="letter" style="--delay: 0.5s;">M</div>
            <div class="letter" style="--delay: 0.6s;">E</div>
          </div>
          <div class="subtitle">
            Version 1 - Your reliable order management system
          </div>
          <img src="mobirom.png" alt="Company Logo" class="logo">
        </div>
        

        
        <script>
          // Exemplu JavaScript pentru a adăuga interactivitate sau control asupra animației
          document.getElementById('chair-animation').addEventListener('click', function() {
            // Acțiuni la click, de exemplu: oprire/pornire animație
          });
        </script>
        <!-- Detalii Comanda vor fi aici -->
      </div>
    </div>
  </body>
</html>