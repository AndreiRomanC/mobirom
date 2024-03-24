import * as operatiiComenzi from '../operatiiComenzi.js';
import { adaugaFormularComanda } from '../OrderForm/formularComanda.js';
import * as db from '../db/db_use.js';
import * as idxDbManager from '../db/idxDbMangager.js';
import * as ordersFromSite from '../OrdersFromSite/ordersFromSite.js';
const sortButton = document.getElementById('sortButton');
export var selectElement = document.getElementById("filterByStatus");

export let listaComenzi = []; // Initialize an empty array to hold the latest data
export function actualizaza_lisa(nouaLista){
  listaComenzi = nouaLista;
  console.log(listaComenzi);
}
export function initialize(initialData) {
  // Initialize the latest data with the initialData
  listaComenzi = initialData;
  //console.log(listaComenzi);

  const comenziContainer = document.getElementById('comenziContainer');
 
 
 // *** DOMContentLoaded ***

  document.addEventListener('DOMContentLoaded', function() {
    listaComenzi = "";
    idxDbManager.incarcaListaComenziDtbIdx().then(comenziIncarcate => {
      listaComenzi = comenziIncarcate; // Actualizează listaComenzi cu comenzile încărcate
      console.log("Comenzi încărcate cu succes:", listaComenzi);
      operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));

      }).catch(error => {
          console.error(error);

      });
      console.log("Comenzi încărcate cu succes 2:", listaComenzi);


      sortButton.addEventListener('click', function() {
        let filtrate = operatiiComenzi.aplicaFiltru(listaComenzi);
        operatiiComenzi.setAscendent(!operatiiComenzi.ascendent); // Schimbăm starea sortării
        operatiiComenzi.incarcaComenzi(filtrate);
      });
      // Obține data curentă
      let azi = new Date();

      // Setează data de început la începutul anului curent
      let anulCurent = azi.getFullYear();
      let primaZi = new Date(anulCurent, 0, 1); // Lunile sunt de la 0 la 11, deci 0 este Ianuarie
      document.getElementById('dataStart').value = primaZi.toISOString().split('T')[0];

      // Setează data de sfârșit la o lună peste data de azi
      let oLunaMaiTazi = new Date(new Date(azi).setMonth(azi.getMonth() + 1));
      document.getElementById('dataEnd').value = oLunaMaiTazi.toISOString().split('T')[0];

    document.getElementById('dataStart').addEventListener('change', function() {
      operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));
    });

    document.getElementById('dataEnd').addEventListener('change', function() {
      operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));
    });

    document.getElementById('searchForm').addEventListener('submit', function(event) {
      event.preventDefault();
    });

    document.getElementById('searchButton').addEventListener('click', function() {
      operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));
    });

    document.getElementById('butonMeniu1').addEventListener('click', function() {
      adaugaFormularComanda(listaComenzi, null);
      operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));

    });

    document.getElementById('butonMeniu2').addEventListener('click', function() {
      db.fetchFromApi('fetchFromMysql', {})
        .then(data => {
           console.log("datele primite de la server: ",data);
           listaComenzi = "";
           listaComenzi = JSON.parse(data);
           actualizaza_lisa(listaComenzi);
           operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));
           idxDbManager.copieazaComenziInDatabaseIDX(listaComenzi).then(comenziIncarcate => {
            console.log("comenzi copiate in IDX DB cu succes:", comenziIncarcate);    
            }).catch(error => {
                console.error(error);
      
            });
          db.updatePageWithData(data, 'detaliiComanda');
        })
        .catch(error => console.log(error));


    });
    document.getElementById('butonMeniu3').addEventListener('click', function() {
      //db.updatePageWithData("test", 'comenziContainer');
      db.fetchFromApi('newOrdersFromSite', {})
        .then(data => {
           console.log("datele primite de la server: ",data);
           //const cleanedResponseBody = JSON.parse(data);
          ordersFromSite.updatePageWithOrdersFromSite(data, 'detaliiComanda', listaComenzi);
        })
        .catch(error => console.log(error));


    });

    document.getElementById('searchInput').addEventListener('keydown', function(event) {
      if (event.key === 'Enter') {
        operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));
      }
    });

    selectElement.addEventListener("change", function() {
      operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));      
    });


  });
}
