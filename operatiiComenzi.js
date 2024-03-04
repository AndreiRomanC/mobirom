import { actualizaza_lisa } from "./dom/dom_api.js";
import {selectElement} from "./dom/dom_api.js";
import {generateDetaliiComandaHTML} from "./orderForm.js";
import {adaugaEvenimentButonAdaugare} from "./eventLAddProd.js";
import { fetchFromApi } from './db/db_use.js';

import * as IdxDbManager from "./db/idxDbMangager.js";

export function cautaComenziDupaNume(nameToSearch,listaComenzi){  
  let comenziGasite = listaComenzi.filter(comanda => comanda.client.toLowerCase().includes(nameToSearch.toLowerCase()));
  //aplicaFiltru(comenziGasite);
  return comenziGasite;
}

export function salveazaModificari(idComanda, listaComenzi) {

  const comandaUrgenta = document.getElementById('comandaUrgentaInput').checked;
  const client = document.getElementById('clientInput').value;
  const telefon = document.getElementById('telefonInput').value;
  const data = document.getElementById('dataInput').value;
  const termenLivrare = document.getElementById('termenLivrareInput').value;
  const statusComanda = document.getElementById('statusSelect_comanda').value;
  const detaliiComanda = document.getElementById('detaliiInput').value;
  const noteComanda = document.getElementById('noteInput').value;

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
        data: data,
        termenLivrare: termenLivrare,
        urgenta: comandaUrgenta,
        produse: produse, // Acesta este array-ul de produse colectat din formular
        status: statusComanda,
        note: noteComanda,
        detalii: detaliiComanda,
        // Calculul totalului ar trebui să fie efectuat pe baza produselor; exemplu simplu de calcul al totalului
        total: produse.reduce((total, produs) => total + (produs.cantitate * produs.valoare), 0)
    };
   
  
  const index = listaComenzi.findIndex(c => c.id === idComanda);

  // Verifică dacă comanda a fost găsită
  if (index !== -1) {
      // Actualizează comanda în listaComenzi
      listaComenzi[index] = comandaModificata;
      IdxDbManager.actualizeazaComandaDtbIdx(comandaModificata);
      const comandaModJSON = JSON.stringify(comandaModificata);

      fetchFromApi('updateOrder', { comandaMod: comandaModJSON })
      .then(data => {
          console.log("am facut update la :", data);
      })
      .catch(error => {
          console.error("Eroare la modificarea comenzii in mysql:", error);
      });

      console.log(`Comanda cu ID-ul ${idComanda} a fost actualizată în listaComenzi.`);
  } else {
      // Dacă comanda nu există în listaComenzi, opțional o poți adăuga
      listaComenzi.push(comandaModificata);
      console.log(`Comanda cu ID-ul ${idComanda} a fost adăugată în listaComenzi.`);
  }
  actualizaza_lisa(listaComenzi);
  alert('Modificările au fost salvate.');

  // Re-afișarea detaliilor comenzi actualizate
  afiseazaDetaliiComanda(idComanda, listaComenzi);
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
          afiseazaDetaliiComanda(comanda.id, listaComenzi);
      };
      comenziContainer.appendChild(elementComanda);
  });
}
 
export function stergeComanda(idComanda, listaComenzi) {
  document.getElementById('detaliiComanda').innerHTML = "";
  // Găsește indexul comenzii în array
  const index = listaComenzi.findIndex(comanda => comanda.id === idComanda);
  if (index !== -1) {
      // Șterge comanda din array
      listaComenzi.splice(index, 1);
      IdxDbManager.stergeComandaDtbIdx(idComanda);
      actualizaza_lisa(listaComenzi)
      incarcaComenzi(listaComenzi);

      fetchFromApi('deleteOrder', { id: idComanda }) // Presupunând că backend-ul așteaptă 'id'
      .then(data => {
              console.log("Comanda cu ID:", idComanda, "a fost ștearsă.");
      })
      .catch(error => {    
          console.error("Eroare la ștergerea comenzii în MySQL:", error);
      });
  

  } else {
      alert('Comanda nu a fost găsită.');
  }
}
  // Funcție pentru afișarea detaliilor unei comenzi
 export function afiseazaDetaliiComanda(idComanda, listaComenzi) {
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
      incarcaComenzi(aplicaFiltru(listaComenzi));
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
  if (!comanda.client || !comanda.produse || comanda.produse.length === 0) {
    alert("Comanda trebuie să aibă un client și cel puțin un produs.")
    return; // Încetează execuția funcției dacă validarea eșuează
  }
    listaComenzi.push(comanda);
    incarcaComenzi(aplicaFiltru(listaComenzi));
    //copieazaComenziInDatabaseIDX(listaComenzi);
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



