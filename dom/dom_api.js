import * as operatiiComenzi from '../operatiiComenzi.js';
import { adaugaFormularComanda } from '../OrderForm/formularComanda.js';

const sortButton = document.getElementById('sortButton');
let ascendent = true; // Folosim o variabilă pentru a ține evidența sortării
let listaComenzi = []; // Initialize an empty array to hold the latest data
export function actualizaza_lisa(nouaLista){
  listaComenzi = nouaLista;
}
export function initialize(initialData) {
  // Initialize the latest data with the initialData
  listaComenzi = initialData;
  //console.log(listaComenzi);
  operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));
  sortButton.addEventListener('click', function() {
    let filtrate = operatiiComenzi.aplicaFiltru(listaComenzi);
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
      adaugaFormularComanda();
    });

    document.getElementById('butonMeniu2').addEventListener('click', function() {
      afiseaza_data("test");
      fetchFromApi('listAll', { userId: 1234 })
        .then(data => {
          afiseaza_data(data);
        })
        .catch(error => afiseaza_data(error));
    });

    document.getElementById('searchInput').addEventListener('keydown', function(event) {
      if (event.key === 'Enter') {
        operatiiComenzi.incarcaComenzi(operatiiComenzi.aplicaFiltru(listaComenzi));
      }
    });
  });
}
