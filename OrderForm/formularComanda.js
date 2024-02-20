import { actualizaza_lisa } from "../dom/dom_api.js";
import {generateDetaliiComandaHTML } from "../orderForm.js";
import {adaugaEvenimentButonAdaugare} from "../eventLAddProd.js";
import { adaugaComanda } from "../operatiiComenzi.js"; 
import { adaugaComandaIDB } from "../db/idxDbMangager.js";
import { fetchFromApi } from '../db/db_use.js';

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


export function adaugaFormularComanda(listaComenzi) {

  const id_unique = generatePositiveNumericId();
  const comandaNoua = {
    id: id_unique, // ID-ul este bazat pe numărul de comenzi existente
    client: 'Nume Client',
    telefon: 'Telefon Client',
    data: dataCurentaISO, // Data curentă
    termenLivrare: dataLivrareISO, // Data de livrare dorită
    urgenta: false,
    produse: [
        { nume: "Produs 1", cantitate: 1, valoare: 0, etapaFabricatie: "Pregătire" } // Un singur produs cu valoare 0
    ],
    status: 'În așteptare',
    note: 'Note Comandă',
    detalii: 'Detalii Comandă',
    total: 0 // Totalul inițial 0
};

  const container = document.getElementById('detaliiComanda');
  container.innerHTML = '';
  let detaliiHTML = generateDetaliiComandaHTML(comandaNoua);
  container.innerHTML = detaliiHTML;
  adaugaEvenimentButonAdaugare();
  const buttonContainer = document.createElement('div');
  buttonContainer.classList.add('buttonContainer');

  document.getElementById('butonSterge').addEventListener('click', () => {
    //stergeComanda(idComanda, listaComenzi);
    //incarcaComenzi(aplicaFiltru(listaComenzi));
    container.innerHTML = "";
    console.log(listaComenzi);
  })

  container.appendChild(buttonContainer);
  document.getElementById('butonSalveaza').addEventListener('click', function() {
    // Aici poți adăuga codul care trebuie executat atunci când butonul este apăsat
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

    const comandaNoua = {
    id: id_unique, // Presupunând că vrei să folosești lungimea listei pentru a genera un nou ID
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

// Adaugă comanda nouă la lista de comenzi
    adaugaComanda(comandaNoua,listaComenzi);
    adaugaComandaIDB(comandaNoua).then(id => {
      console.log('Comanda adăugată cu succes in indexDB. ID comandă:', id);
  }).catch(error => {
      console.error('Eroare la adăugarea comenzii in indexDB:', error);
  });
        console.log({
          comandaUrgenta,
          client,
          telefon,
          data,
          termenLivrare,
          statusComanda,
          detaliiComanda,
          noteComanda,
          produse
      });

      //update mysql DB
      fetchFromApi('addNewOrder', { comandaNouaParam: comandaNoua })
      .then(response => {
        console.log("Response:", response); // Afișează răspunsul primit de la server
    })
      .then(data => {
          console.log("Am salvat datele în MySQL:", data);
          console.log('Datele primite de la server:', data); // Afișează datele primite de la server în consolă
      })
      .catch(error => {
          console.error("Eroare la adăugarea comenzii:", error);
      });

    //actualizaza_lisa(listaComenzi)

    console.log('Butonul "Adaugă Comandă" a fost apăsat.',listaComenzi);
    
  });



  const link = document.createElement('link');
  link.href = './OrderForm/OrderFormStyle.css';
  link.rel = 'stylesheet';
  link.type = 'text/css';


}
