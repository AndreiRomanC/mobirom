export function salveazaDateInIndexedDB() {
  // Aici vei adăuga logica pentru deschiderea bazei de date IndexedDB și salvarea datelor
    // Codul este similar cu cel de mai sus, dar pus în această funcție
    const request = indexedDB.open('ComenziDB', 1);

    
    request.onupgradeneeded = function(event) {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('comenzi')) {
          db.createObjectStore('comenzi', { keyPath: 'id' });
      }
  };
    request.onsuccess = function(event) {
        // Logica de adăugare a datelor în baza de date
        const db = event.target.result;
        const transaction = db.transaction('comenzi', 'readwrite');
        const store = transaction.objectStore('comenzi');

        comenziFictive.forEach(comanda => {
            store.add(comanda);
        });

        transaction.oncomplete = function() {
            console.log('Comenzile au fost salvate în IndexedDB.');
            db.close();
        };
    };

    request.onerror = function(event) {
        console.error('Eroare la deschiderea IndexedDB:', event.target.errorCode);
    };
}