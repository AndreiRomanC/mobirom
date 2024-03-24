
import { adaugaFormularComanda } from './OrderForm/formularComanda.js';
import { fetchFromApi } from './db/db_use.js';
import { initialize } from './dom/dom_api.js';

//import { comenziFictive } from './comenzi.js';  
//console.log(comenziFictive);
let comenziFictive = "";
initialize(comenziFictive);



// Funcție pentru aplicarea filtrului

function incarcaAltceva() {
  salveazaDateInIndexedDB()
}

var acc = document.getElementsByClassName("accordion-button");
var i;

for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.maxHeight){
            panel.style.maxHeight = null;
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
        } 
    });
}
