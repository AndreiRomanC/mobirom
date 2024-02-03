import { actualizaza_lisa } from "./dom/dom_api.js";
import {selectElement} from "./dom/dom_api.js";
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

    let detaliiHTML = `
    <div class="flex-container justify-space-between align-items-center">
    <h3>Comandă #${comanda.id}</h3>
    <div>
        <label for="comandaUrgentaInput"><strong>Comandă Urgentă:</strong></label>
        <input type="checkbox" id="comandaUrgentaInput" ${comanda.urgenta ? 'checked' : ''}>
    </div>
</div>
<div class="flex-container align-items-center">
    <div class="flex-item medium"><strong>Client:</strong> <input type="text" value="${comanda.client}" id="clientInput" class="input-field"></div>
    <div class="flex-item medium"><strong>Telefon:</strong> <input type="tel" value="${comanda.telefon}" id="telefonInput" class="input-field"></div>
    <div class="flex-item medium"><strong>Data:</strong> <input type="date" value="${comanda.data}" id="dataInput" class="input-field"></div>
    <div class="flex-item medium"><strong>Termen Livrare:</strong> <input type="date" value="${comanda.termenLivrare}" id="termenLivrareInput" class="input-field"></div>
</div>
<div class="flex-container justify-content-center align-items-center">
    <div class="flex-item medium"><strong>Produs:</strong> <input type="text" value="${comanda.produs}" id="produsInput" class="input-field"></div>
    <div class="flex-item small"><strong>Cantitate:</strong> <input type="number" value="${comanda.cantitate}" id="cantitateInput" class="input-field"></div>
    <div class="flex-item medium"><strong>Total Valoare:</strong> <input type="text" value="${comanda.total}" id="totalInput" class="input-field"></div>
    <div class="flex-item large"><strong>Etapa Fabricație:</strong> 
        <select id="etapaFabricatieSelect" class="input-field">
            <option value="Pregătire" ${comanda.etapaFabricatie === 'Pregătire' ? 'selected' : ''}>Pregătire</option>
            <option value="În producție" ${comanda.etapaFabricatie === 'În producție' ? 'selected' : ''}>În producție</option>
            <option value="Finalizat" ${comanda.etapaFabricatie === 'Finalizat' ? 'selected' : ''}>Finalizat</option>
        </select>
    </div>
    <!-- Butonul "+" mic și plasat central pe linie -->
    <div>
        <button id="butonAdaugare" class="btn-small" title="Adaugă element">+</button>
    </div>
</div>
<div class="flex-container">
    <div class="flex-item medium"><strong>Status:</strong> 
        <select id="statusSelect_comanda" class="input-field status">
            <option value="În așteptare" ${comanda.status === 'În așteptare' ? 'selected' : ''}>În așteptare</option>
            <option value="Finalizată" ${comanda.status === 'Finalizată' ? 'selected' : ''}>Finalizată</option>
            <option value="Anulată" ${comanda.status === 'Anulată' ? 'selected' : ''}>Anulată</option>
        </select>
    </div>
</div>
<div class="flex-container full-width">
    <strong>Detalii Comandă:</strong><br>
    <textarea id="detaliiInput" class="input-field full-width">${comanda.note}</textarea>
</div>
<div class="flex-container full-width">
    <strong>Note Comandă:</strong><br>
    <textarea id="noteInput" class="input-field full-width">${comanda.note}</textarea>
</div>
<div class="flex-container button-container">
    <button id="butonSalveaza">Salvează Modificările</button>
    <button id="butonSterge">Șterge Comandă</button>
</div>




    
    

    `;
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


