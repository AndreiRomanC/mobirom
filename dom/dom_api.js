import * as operatiiComenzi from '../operatiiComenzi.js';
import { adaugaFormularComanda } from '../OrderForm/formularComanda.js';
import * as db from '../db/db_use.js';

const sortButton = document.getElementById('sortButton');
export var selectElement = document.getElementById("filterByStatus");

let listaComenzi = []; // Initialize an empty array to hold the latest data
export function actualizaza_lisa(nouaLista){
  listaComenzi = nouaLista;
  console.log(listaComenzi);
}
export function initialize(initialData) {
  // Initialize the latest data with the initialData
  listaComenzi = initialData;
  //console.log(listaComenzi);
  operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));
  sortButton.addEventListener('click', function() {
    let filtrate = operatiiComenzi.aplicaFiltru(listaComenzi);
    operatiiComenzi.setAscendent(!operatiiComenzi.ascendent); // Schimbăm starea sortării
    operatiiComenzi.incarcaComenzi(filtrate);
  });
  const comenziContainer = document.getElementById('comenziContainer');
  document.addEventListener('DOMContentLoaded', function() {


    document.getElementById('dataStart').value = '2023-12-02';
    document.getElementById('dataEnd').value = '2024-02-28';

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
      adaugaFormularComanda(listaComenzi);
      operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));

    });

    document.getElementById('butonMeniu2').addEventListener('click', function() {
      //db.updatePageWithData("test", 'comenziContainer');
      db.fetchFromApi('listAll', { userId: 14594 })
        .then(data => {
           console.log(data);
          db.updatePageWithData(data, 'detaliiComanda');
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
