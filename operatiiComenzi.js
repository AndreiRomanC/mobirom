import { actualizaza_lisa } from "./dom/dom_api.js";
import {selectElement} from "./dom/dom_api.js";
import {generateDetaliiComandaHTML} from "./orderForm.js";

export function cautaComenziDupaNume(nameToSearch,listaComenzi){  
  let comenziGasite = listaComenzi.filter(comanda => comanda.client.toLowerCase().includes(nameToSearch.toLowerCase()));
  //aplicaFiltru(comenziGasite);
  return comenziGasite;
}


export function salveazaModificari(idComanda, listaComenzi) {
  const comanda = listaComenzi.find(comanda => comanda.id === idComanda);
  actualizaza_lisa(listaComenzi)

  if (!comanda) {
      alert('Comanda nu a fost găsită.');
      return;
  }

  // Obținerea valorilor modificate
  comanda.client = document.getElementById('clientInput').value;
  comanda.data = document.getElementById('dataInput').value;
  comanda.status = document.getElementById('statusSelect_comanda').value;
  comanda.total = parseFloat(document.getElementById('totalInput').value);

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
      actualizaza_lisa(listaComenzi)
      incarcaComenzi(listaComenzi);
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
  console.log(statusSelect_comanda);
  const updateStatusColor = () => {
    statusSelect_comanda.className = 'input-field status-' + statusSelect_comanda.value.replace(/\s+/g, '-');
  };
  document.getElementById('butonAdaugare').addEventListener('click', function() {
    var container = document.getElementById('liniiAdaugate');
    var template = document.getElementById('linieProdusTemplate').cloneNode(true);
    template.id = ""; // Eliminăm ID-ul pentru a evita duplicarea
       // Eliminăm butonul "+" din linia clonată și adăugăm un buton de ștergere dacă este necesar
       var butonAdaugare = template.querySelector('#butonAdaugare');
       butonAdaugare.remove();
       template.querySelectorAll('.input-field').forEach(function(input) {
        if (input.type === 'text' || input.type === 'number') {
            input.value = '';
        } else if (input.tagName.toLowerCase() === 'select') {
            input.selectedIndex = 0; // sau orice alt index relevant
        }
    });
    // Adăugăm un buton de ștergere la linia clonată
    var butonSterge = document.createElement('button');
    butonSterge.textContent = '-';
    butonSterge.className = 'btn-small btn-sterge';
    butonSterge.title = 'Șterge element';
    butonSterge.addEventListener('click', function() {
        // Șterge linia
        container.removeChild(template);
    });

    // Adăugăm butonul de ștergere la template
    template.appendChild(butonSterge);

    // Adăugăm linia clonată la container
    container.appendChild(template);
});

  statusSelect_comanda.addEventListener('change', updateStatusColor);
  updateStatusColor(); // Aplicați culoarea inițială bazată pe valoarea preselectată
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


