fetch('api.php?action=getData') // Adresa API-ului și acțiunea
   .then(response => response.json()) // Parsați răspunsul JSON
   .then(data => {
       // Acum, variabila 'data' conține datele returnate din PHP
       console.log(data);

       // Aici puteți efectua operații suplimentare cu datele, cum ar fi afișarea lor într-un tabel sau altfel.
   })
   .catch(error => console.error('Eroare: ' + error));