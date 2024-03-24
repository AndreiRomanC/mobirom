import { actualizaza_lisa } from "../dom/dom_api.js";
import {generateDetaliiComandaHTML } from "./orderForm.js";
import {adaugaEvenimentButonAdaugare} from "../eventLAddProd.js";
import { adaugaComanda, stergeComanda } from "../operatiiComenzi.js"; 
import { PromiseAdaugaComanda } from "../operatiiComenzi.js"; 
import { adaugaComandaIDB } from "../db/idxDbMangager.js";
import {stergeComandaDtbIdx} from "../db/idxDbMangager.js";
import { fetchFromApi } from '../db/db_use.js';
import { listaComenzi } from "../dom/dom_api.js";
// Crearea unei instanțe Date pentru data curentă
function generatePositiveNumericId() {
    // Definim minimul și maximul pentru a asigura că ID-ul are întotdeauna 8 cifre
    const min = 10000000; // Minimul pentru un număr de 8 cifre
    const max = 99999999; // Maximul pentru un număr de 8 cifre
    // Generăm un număr aleatoriu între min și max
    const id = Math.floor(Math.random() * (max - min + 1)) + min;
    return id.toString();
}
const dataCurenta = new Date();
// Obținerea șirului ISO8601 pentru data curentă (de exemplu, "2024-02-04")
const dataCurentaISO = dataCurenta.toISOString().split('T')[0];
const dataLivrare = new Date(dataCurenta); // Inițializăm cu data curentă
dataLivrare.setDate(dataLivrare.getDate() + 42);
const dataLivrareISO = dataLivrare.toISOString().split('T')[0];

// Creați o comandă nouă cu datele dorite
let username = 'testUser';
document.addEventListener("DOMContentLoaded", function() {
  // Acest cod se execută după ce se încarcă DOM-ul
  username = document.getElementById('userInfo').getAttribute('data-username');
  console.log('usernamul-ul este'.username); // Acum poți manipula valoarea în JavaScript
});

export function adaugaFormularComanda(listaComenzi, comanda) {
console.log('Adaugă formularul comenzii pentru:', listaComenzi);
  const id_unique = generatePositiveNumericId();
  let comandaNouaForm = {
    id: id_unique,
    client: comanda && comanda.nume ? comanda.nume : 'Nume Client',
    telefon: comanda && comanda.tel ? comanda.tel : 'Telefon Client',
    email: comanda && comanda.email ? comanda.email : 'Email Client',
    data: dataCurentaISO,
    termenLivrare: dataLivrareISO,
    urgenta: comanda ? comanda.urgenta : false,
    produse: comanda && comanda.produs ? [{ nume: comanda.produs, cantitate: 1, valoare: 0, etapaFabricatie: "Pregătire" }]: [{ nume: "Produs 1", cantitate: 1, valoare: 0, etapaFabricatie: "Pregătire" }],
    status: 'În așteptare',
    note: comanda && comanda.note ? comanda.note : 'Note Comandă',
    detalii: comanda && comanda.detalii ? comanda.detalii : 'Detalii Comandă',
    total: comanda && comanda.total ? comanda.total : 0,
    mod_user: comanda && comanda.mod_user ? username: 'unknown',
    timestamp: comanda && comanda.timestamp ? comanda.timestamp : dataCurentaISO
};



  const container = document.getElementById('detaliiComanda');
  container.innerHTML = '';
  let detaliiHTML = generateDetaliiComandaHTML(comandaNouaForm);
  container.innerHTML = detaliiHTML;
  adaugaEvenimentButonAdaugare();
  const buttonContainer = document.createElement('div');
  buttonContainer.classList.add('buttonContainer');

  container.appendChild(buttonContainer);
  document.getElementById('butonSalveaza').addEventListener('click', function() {

    // Aici poți adăuga codul care trebuie executat atunci când butonul este apăsat
    const comandaUrgenta = document.getElementById('comandaUrgentaInput').checked;
    const client = document.getElementById('clientInput').value;
    const telefon = document.getElementById('telefonInput').value;
    const email = document.getElementById('emailInput').value;

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

    const comandaNoua = {
    id: id_unique, // Presupunând că vrei să folosești lungimea listei pentru a genera un nou ID
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
    mod_user: username,
    mod_timestamp: new Date().toISOString().slice(0, 19).replace('T', ' '),
    // Calculul totalului ar trebui să fie efectuat pe baza produselor; exemplu simplu de calcul al totalului
    total: produse.reduce((total, produs) => total + (produs.cantitate * produs.valoare), 0)
};
 console.log('Comanda noua:', comandaNoua);
// Adaugă comanda nouă la lista de comenzi


    PromiseAdaugaComanda(comandaNoua, listaComenzi)
      .then(() => {
        console.log("Comanda a fost adăugată cu succes în lista locală.");
        // Continuăm cu adăugarea în MySQL DB
        return fetchFromApi('addNewOrder', { comandaNouaParam: JSON.stringify(comandaNoua) });
      })
      .then(data => {
        console.log("Comanda adăugată cu succes în MySQL DB:", data);
        // După succesul MySQL, încercăm adăugarea în IndexDB
        return adaugaComandaIDB(comandaNoua);
      })
      .then(id => {
        console.log('Comanda adăugată cu succes în indexDB. ID comandă:', id);
        // Aici, toate operațiunile au reușit
        alert("Comanda a fost adaugata cu succes în toate stocările");
      })
      .catch(error => {
        console.error("Eroare la adăugarea comenzii. Inițiază rollback dacă este necesar:", error);
        // Aici poți gestiona rollback-ul sau curățenia, dacă este necesar
        stergeComandaDtbIdx(comandaNoua.id).then(() => {
          console.log('Comanda a fost ștearsă din indexDB din cauza unei erori.');
        } );
        // Șterge comanda din lista locală
        const index = listaComenzi.findIndex(comanda => comanda.id === comandaNoua.id);
        listaComenzi.splice(index, 1);

      });
    //actualizaza_lisa(listaComenzi)

    
  });


}
