
import { adaugaFormularComanda } from './OrderForm/formularComanda.js';
import { fetchFromApi } from './db/db_use.js';
import { initialize } from './dom/dom_api.js';

import { comenziFictive } from './comenzi.js';  
console.log(comenziFictive);
initialize(comenziFictive);



// Funcție pentru aplicarea filtrului

function incarcaAltceva() {
  salveazaDateInIndexedDB()
}