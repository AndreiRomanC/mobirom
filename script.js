
import { adaugaFormularComanda } from './OrderForm/formularComanda.js';
import { fetchFromApi } from './db/db_use.js';
import { initialize } from './dom/dom_api.js';

export let comenziFictive = [
  { id: 1, client: 'Ion Popescu', data: '2024-01-01', status: 'În așteptare', total: 150.0 },
  { id: 2, client: 'Maria Ionescu', data: '2024-01-05', status: 'Finalizată', total: 200.5 },
  { id: 3, client: 'George Vasile', data: '2024-01-10', status: 'Anulată', total: 0.0 },
  { id: 4, client: 'Ana Maria', data: '2024-01-15', status: 'În așteptare', total: 100.0 },
  { id: 5, client: 'Mihai Popa', data: '2024-01-20', status: 'Finalizată', total: 250.0 },
  { id: 6, client: 'Alexandru Ionescu', data: '2024-01-05', status: 'În așteptare', total: 180.0 },
  { id: 7, client: 'Elena Stan', data: '2024-01-10', status: 'Finalizată', total: 300.0 },
  { id: 8, client: 'Andreea Popescu', data: '2024-01-15', status: 'În așteptare', total: 120.0 },
];
console.log(comenziFictive);
initialize(comenziFictive);



// Funcție pentru aplicarea filtrului

function incarcaAltceva() {
  salveazaDateInIndexedDB()
}