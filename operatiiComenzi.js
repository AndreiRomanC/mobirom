import { actualizaza_lisa } from "./dom/dom_api.js";
import {selectElement} from "./dom/dom_api.js";
import {generateDetaliiComandaHTML} from "./OrderForm/orderForm.js";
import {adaugaEvenimentButonAdaugare} from "./eventLAddProd.js";
import { fetchFromApi } from './db/db_use.js';
import { incarcaCSS, eliminaCSS } from './utils/dynamicStyles.js';

import * as IdxDbManager from "./db/idxDbMangager.js";

export function cautaComenziDupaNume(nameToSearch,listaComenzi){  
  let comenziGasite = listaComenzi.filter(comanda => comanda.client.toLowerCase().includes(nameToSearch.toLowerCase()));
  //aplicaFiltru(comenziGasite);
  return comenziGasite;
}

let username = '';
document.addEventListener("DOMContentLoaded", function() {
  // Acest cod se execută după ce se încarcă DOM-ul
  username = document.getElementById('userInfo').getAttribute('data-username');
  console.log('usernamul-ul este'.username); // Acum poți manipula valoarea în JavaScript
});

export function salveazaModificari(idComanda, listaComenzi) {

  const comandaUrgenta = document.getElementById('comandaUrgentaInput').checked;
  const client = document.getElementById('clientInput').value;
  const telefon = document.getElementById('telefonInput').value;
  const email = document.getElementById('emailInput').value;
  const data = document.getElementById('dataInput').value;
  const termenLivrare = document.getElementById('termenLivrareInput').value;
  const statusComanda = document.getElementById('statusSelect_comanda').value;
  const detaliiComanda = document.getElementById('detaliiInput').value;
  const noteComanda = document.getElementById('noteInput').value;

  const mod_user = username;
  const mod_timestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');

  const produse = [];
      document.querySelectorAll('.linieProdusTemplate').forEach(function(produsElement) {
          const numeProdus = produsElement.querySelector('.produsInput').value;
          const cantitateProdus = produsElement.querySelector('.cantitateInput').value;
          const valoareProdus = produsElement.querySelector('.totalInput').value;
          const etapaFabricatie = produsElement.querySelector('.etapaFabricatieSelect').value;
          produse.push({nume: numeProdus, cantitate: cantitateProdus, valoare: valoareProdus, etapaFabricatie: etapaFabricatie});
      });
  const comandaModificata = {
        id: idComanda, // Presupunând că vrei să folosești lungimea listei pentru a genera un nou ID
        client: client,
        telefon: telefon,
        email: email,
        data: data,
        termenLivrare: termenLivrare,
        urgenta: comandaUrgenta,
        produse: produse, // Acesta este array-ul de produse colectat din formular
        status: statusComanda,
        note: noteComanda,
        detalii: detaliiComanda,
        mod_user: mod_user,
        mod_timestamp: mod_timestamp,
        // Calculul totalului ar trebui să fie efectuat pe baza produselor; exemplu simplu de calcul al totalului
        total: produse.reduce((total, produs) => total + (produs.cantitate * produs.valoare), 0)
    };
   
  
    const index = listaComenzi.findIndex(c => c.id === idComanda);
    console.log("comanda modificata", comandaModificata);
    const comMOd = comandaModificata;
    if (index !== -1) {
        IdxDbManager.actualizeazaComandaDtbIdx(comandaModificata)
          .then(() => {
              // Prima actualizare realizată cu succes
              console.log("!!!!!!!!!!!!!", comMOd);
              console.log("comanda modificata ", comandaModificata);
              listaComenzi[index] = comMOd;
              console.log("listaComenzi[index] :", listaComenzi[index]);


              return fetchFromApi('updateOrder', { comandaMod: JSON.stringify(comandaModificata) });
          })
          .then(data => {
              // A doua actualizare realizată cu succes
              console.log("Comanda actualizată cu succes în MySQL și în lista locală:", comandaModificata, index);
              actualizaza_lisa(listaComenzi);
              alert('Modificările au fost salvate!');
              console.log("listaComenzi[index] :", listaComenzi[index]);
              afiseazaDetaliiComanda(idComanda, listaComenzi);
              incarcaComenzi(listaComenzi);
          })
          .catch(error => {
              console.error("Eroare la actualizarea comenzii:", error);
              // Opțional, gestionează rollback-ul aici
          });
    } else {
        console.log(`Comanda cu ID-ul ${idComanda} nu a fost găsită în listaComenzi.`);
        // Logica pentru adăugarea comenzii, dacă este necesar
    }


}

export function incarcaComenzi(listaComenzi) {
  const dataStart = document.getElementById('dataStart').value;
  const dataEnd = document.getElementById('dataEnd').value;
  const comenziContainer = document.getElementById('comenziContainer');
  comenziContainer.innerHTML = "";

  // Afișarea comenzilor filtrate
  listaComenzi.forEach(comanda => {
      const elementComanda = document.createElement('div');
      elementComanda.classList.add('comandaItem');
      elementComanda.innerHTML = `
          <h4>Comanda ${comanda.id}</h4>
          <p>Client: ${comanda.client}</p>
          <p>Data: ${comanda.data}</p>
      `;
      elementComanda.onclick = function() {
        // eliminaCSS('ordersForm.css');
         afiseazaDetaliiComanda(comanda.id, listaComenzi);
      };
      comenziContainer.appendChild(elementComanda);
  });
}
 
export function stergeComanda(idComanda, listaComenzi) {
  // Găsește indexul comenzii în array
  const index = listaComenzi.findIndex(comanda => comanda.id === idComanda);
  const confirmare = confirm("Ești sigur că vrei să ștergi această comandă?");
if(confirmare === true){
  document.getElementById('detaliiComanda').innerHTML = "";

  if (index !== -1) {
      // Șterge comanda din array
      fetchFromApi('deleteOrder', { id: idComanda }) // Presupunând că backend-ul așteaptă 'id'
      .then(data => {
              console.log("Comanda cu ID:", idComanda, "a fost ștearsă.");
              IdxDbManager.stergeComandaDtbIdx(idComanda).then(() => {  
                  console.log("Comanda a fost ștearsă din IndexDB cu succes.");
                  listaComenzi.splice(index, 1);   
                  actualizaza_lisa(listaComenzi)
                  incarcaComenzi(listaComenzi);

              })
              .catch(error => {
                  console.error("Eroare la ștergerea comenzii în IndexDB:", error);
              }); 
      })
      .catch(error => {    
          console.error("Eroare la ștergerea comenzii în MySQL:", error);
      });
  

  } else {
      alert('Comanda nu a fost găsită.');
  } }
else {
    alert('Comanda nu a fost ștearsă.');
  }

}
  // Funcție pentru afișarea detaliilor unei comenzi
 export function afiseazaDetaliiComanda(idComanda, listaComenzi) {
    eliminaCSS('./OrderFormSite/orderForm.css');

    const comanda = listaComenzi.find(comanda => comanda.id === idComanda);

    if (!comanda) {
        document.getElementById('detaliiComanda').innerHTML = 'Comanda nu a fost găsită.';
        return;
    }
    let detaliiHTML = generateDetaliiComandaHTML(comanda);

    document.getElementById('detaliiComanda').innerHTML = detaliiHTML;

    document.getElementById('butonSalveaza').addEventListener('click', () => {
      salveazaModificari(idComanda,listaComenzi);
      incarcaComenzi(aplicaFiltru(listaComenzi));
    });
    document.getElementById('butonSterge').addEventListener('click', () => {
      stergeComanda(idComanda, listaComenzi);
      console.log(listaComenzi);
    })
      const statusSelect_comanda = document.getElementById('statusSelect_comanda');
      const updateStatusColor = () => {
        statusSelect_comanda.className = 'input-field status-' + statusSelect_comanda.value.replace(/\s+/g, '-');
      };

  adaugaEvenimentButonAdaugare();
const buttonsSterge = document.querySelectorAll('.btn-sterge');
// Adăugați un event listener pentru fiecare buton
    buttonsSterge.forEach((button) => {
        button.addEventListener('click', function() {
            // Implementați aici codul pentru acțiunea de ștergere
            // De exemplu, puteți șterge elementul părinte al butonului pentru a elimina întreaga linie de produs
           console.log(button);
            const linieProdus = button.closest('.linieProdusTemplate');
            if (linieProdus) {
                linieProdus.remove();
            }
        });
});
  statusSelect_comanda.addEventListener('change', updateStatusColor);
  updateStatusColor(); // Aplicați culoarea inițială bazată pe valoarea preselectată
}

export function adaugaComanda(comanda, listaComenzi) {
    try {
      if (!comanda.client || !comanda.produse || comanda.produse.length === 0) {
        alert("Comanda trebuie să aibă un client și cel puțin un produs.")
        return; // Încetează execuția funcției dacă validarea eșuează
      }
        listaComenzi.push(comanda);
        incarcaComenzi(aplicaFiltru(listaComenzi));
      return true; // sau orice alt indicator de succes
  } catch (error) {
      return false; // sau detalii despre eroare
  }

    //copieazaComenziInDatabaseIDX(listaComenzi);
}
export function PromiseAdaugaComanda(comanda, listaComenzi) {
  return new Promise((resolve, reject) => {
    try {
      if (!comanda.client || !comanda.produse || comanda.produse.length === 0) {
        alert("Comanda trebuie să aibă un client și cel puțin un produs.");
        reject("Validarea comenzii a eșuat."); // Folosim reject pentru a indica o eroare
        return;
      }
      listaComenzi.push(comanda);
      incarcaComenzi(aplicaFiltru(listaComenzi));
      resolve(true); // Indicăm succesul operațiunii
    } catch (error) {
      reject(error); // În caz de eroare în try block, rejectăm promisiunea cu detalii despre eroare
    }
  });
}
export function setAscendent(newValue) {
  ascendent = newValue;
}

const sortButton = document.getElementById('sortButton');
export let ascendent = true; // Folosim o variabilă pentru a ține evidența sortării

export function aplicaFiltru(listaComenzi) {
  let rezultat = listaComenzi; // Faceți o copie a datelor originale
  const dataStart = document.getElementById('dataStart').value;
  const dataEnd = document.getElementById('dataEnd').value;
  const nameToSearch = document.getElementById('searchInput').value;
  if(selectElement.value !== "all"){
    rezultat = rezultat.filter(function(comanda) {
      return comanda.status === selectElement.value;
    });
  }

  console.log(selectElement.value);
  if (nameToSearch) {
    rezultat = cautaComenziDupaNume(nameToSearch,rezultat);
  }
  rezultat = rezultat.filter(comanda => {
      // Presupunem că `comanda.data` este un string în format 'YYYY-MM-DD'
      return (!dataStart || comanda.data >= dataStart) && (!dataEnd || comanda.data <= dataEnd);
  });
  if (ascendent) {
    rezultat.sort((a, b) => a.data.localeCompare(b.data));
  } else {
    rezultat.sort((a, b) => b.data.localeCompare(a.data));
  }

  // Afișați rezultatul în interfața utilizatorului
  return rezultat;
}



