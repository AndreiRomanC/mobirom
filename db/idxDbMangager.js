/**
 * Deschide o conexiune către baza de date IndexedDB.
 */
function openDatabase() {
  return new Promise((resolve, reject) => {
      const request = indexedDB.open("ComenziDatabase", 1);

      request.onupgradeneeded = function(event) {
          const db = event.target.result;
          if (!db.objectStoreNames.contains("comenzi")) {
              db.createObjectStore("comenzi", { keyPath: "id" });
          }
      };

      request.onsuccess = function(event) {
          resolve(event.target.result);
      };

      request.onerror = function(event) {
          reject(event.target.error);
      };
  });
}

function citesteProduse() {
  deschideBazaDeDate().then(db => {
      const transaction = db.transaction(['produse'], 'readonly');
      const store = transaction.objectStore('produse');
      const request = store.getAll(); // Preia toate produsele din store

      request.onsuccess = function() {
          console.log('Produse:', request.result); // Aici, request.result conține datele citite
      };

      request.onerror = function(event) {
          console.error('Eroare la citirea produselor:', event.target.error);
      };
  }).catch(error => {
      console.error('Eroare la deschiderea bazei de date:', error);
  });
}


export function adaugaComandaIDB(comanda) {
  return new Promise((resolve, reject) => {
      openDatabase().then(db => {
          const transaction = db.transaction(['comenzi'], 'readwrite');
          const store = transaction.objectStore('comenzi');
          const addRequest = store.add(comanda);

          addRequest.onsuccess = () => {
              resolve(addRequest.result); // Rezolvă promisiunea cu ID-ul comenzii adăugate
          };

          addRequest.onerror = () => {
              reject(addRequest.error); // Respinge promisiunea cu eroarea apărută
          };

          // O alternativă este să folosești transaction.oncomplete și transaction.onerror pentru a gestiona rezolvarea și respingerea
          transaction.oncomplete = () => {
              console.log("Tranzacție completată cu succes.");
          };

          transaction.onerror = (event) => {
              reject(transaction.error); // Respinge promisiunea cu eroarea tranzacției
          };
      }).catch(error => {
          reject(error); // Respinge promisiunea dacă deschiderea bazei de date eșuează
      });
  });
}

export function copieazaComenziInDatabaseIDX(listaComenzi) {
  return new Promise((resolve, reject) => {
      openDatabase().then(db => {
          const transaction = db.transaction(['comenzi'], 'readwrite');
          const store = transaction.objectStore('comenzi');
          let numarComenziAdaugate = 0;

          listaComenzi.forEach(comanda => {
              let request = store.add(comanda);
              request.onsuccess = () => {
                  numarComenziAdaugate++;
                  if (numarComenziAdaugate === listaComenzi.length) {
                      resolve(`Toate comenzile au fost adăugate. Total: ${numarComenziAdaugate}`);
                  }
              };
              request.onerror = (e) => {
                  // Aici poți decide să continui sau să oprești procesul în caz de eroare
                  console.error("Eroare la adăugarea comenzii:", e.target.error);
                  reject(e.target.error);
              };
          });

          transaction.oncomplete = () => {
              console.log("Toate tranzacțiile de adăugare a comenzilor au fost finalizate cu succes.");
          };

          transaction.onerror = (event) => {
              reject(transaction.error);
          };

      }).catch(error => {
          reject(error);
      });
  });
}

export function stergeComandaDtbIdx(idComanda) {
  return new Promise((resolve, reject) => {
      openDatabase().then(db => {
          const transaction = db.transaction(['comenzi'], 'readwrite');
          const store = transaction.objectStore('comenzi');
          const request = store.delete(idComanda);

          request.onsuccess = () => {
              resolve(`Comanda cu ID-ul ${idComanda} a fost ștearsă cu succes.`);
          };

          request.onerror = (event) => {
              reject(`Eroare la ștergerea comenzii cu ID-ul ${idComanda}: ${event.target.error}`);
          };
      }).catch(error => {
          reject(`Eroare la deschiderea bazei de date: ${error}`);
      });
  });
}


export function incarcaListaComenziDtbIdx() {
  return new Promise((resolve, reject) => {
      openDatabase().then(db => {
          const transaction = db.transaction(['comenzi'], 'readonly');
          const store = transaction.objectStore('comenzi');
          const request = store.getAll(); // Preia toate înregistrările din object store-ul 'comenzi'

          request.onsuccess = (event) => {
              const lComenzi = event.target.result; // Rezultatul este un array cu toate comenzile
              resolve(lComenzi); // Rezolvă promisiunea cu lista de comenzi încărcată
          };

          request.onerror = (event) => {
              reject("Eroare la încărcarea comenzilor: " + event.target.error);
          };
      }).catch(error => {
          reject("Eroare la deschiderea bazei de date: " + error);
      });
  });
}
