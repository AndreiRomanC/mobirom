
import { adaugaFormularComanda } from './OrderForm/formularComanda.js';

const comenziFictive = [
  { id: 1, client: 'Ion Popescu', data: '2024-01-01', status: 'În așteptare', total: 150.0 },
  { id: 2, client: 'Maria Ionescu', data: '2024-01-05', status: 'Finalizată', total: 200.5 },
  { id: 3, client: 'George Vasile', data: '2024-01-10', status: 'Anulată', total: 0.0 },
  { id: 4, client: 'Ana Maria', data: '2024-01-15', status: 'În așteptare', total: 100.0 },
  { id: 5, client: 'Mihai Popa', data: '2024-01-20', status: 'Finalizată', total: 250.0 },
  { id: 6, client: 'Alexandru Ionescu', data: '2024-01-05', status: 'În așteptare', total: 180.0 },
  { id: 7, client: 'Elena Stan', data: '2024-01-10', status: 'Finalizată', total: 300.0 },
  { id: 8, client: 'Andreea Popescu', data: '2024-01-15', status: 'În așteptare', total: 120.0 },
];


document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('dataStart').value = '2023-12-01';
  document.getElementById('dataEnd').value = '2024-02-28';

  document.getElementById('dataStart').addEventListener('change', function() { 
    incarcaComenzi(comenziFictive);
  })
  document.getElementById('dataEnd').addEventListener('change', function() { 
    incarcaComenzi(comenziFictive);
  })

  document.getElementById('searchForm').addEventListener('submit', function(event) {
    event.preventDefault();
  });

  document.getElementById('searchButton').addEventListener('click', function() { 
    const nameToSearch = document.getElementById('searchInput').value;
    incarcaComenzi(cautaComenziDupaNume(nameToSearch));
  })
  document.getElementById('butonMeniu1').addEventListener('click', function() { 
    adaugaFormularComanda();
  })
  document.getElementById('butonAdaugaComanda').addEventListener('click', function() { 
    adaugaFormularComanda();
  })

  document.getElementById('searchInput').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
      const nameToSearch = document.getElementById('searchInput').value;
      incarcaComenzi(comenziFictive);
      alert(nameToSearch);
    }
    
  })

  incarcaComenzi(comenziFictive);

});



const sortButton = document.getElementById('sortButton');
let ascendent = true; // Folosim o variabilă pentru a ține evidența sortării


sortButton.addEventListener('click', function() {
  aplicaFiltru()
});
// Funcție pentru aplicarea filtrului
function aplicaFiltru() {
  let rezultat = comenziFictive; // Faceți o copie a datelor originale
  const dataStart = document.getElementById('dataStart').value;
  const dataEnd = document.getElementById('dataEnd').value;
  rezultat = rezultat.filter(comanda => {
      // Presupunem că `comanda.data` este un string în format 'YYYY-MM-DD'
      return (!dataStart || comanda.data >= dataStart) && (!dataEnd || comanda.data <= dataEnd);
  });
  if (ascendent) {
    rezultat.sort((a, b) => a.data.localeCompare(b.data));
  } else {
    rezultat.sort((a, b) => b.data.localeCompare(a.data));
  }
  ascendent = !ascendent; // Schimbăm starea sortării

  // Afișați rezultatul în interfața utilizatorului
  incarcaComenzi(rezultat);
}

function cautaComenziDupaNume(nameToSearch){  
  const comenziGasite = comenziFictive.filter(comanda => comanda.client.toLowerCase().includes(nameToSearch.toLowerCase()));
  return comenziGasite;
}

function salveazaModificari(idComanda) {
  const comanda = comenziFictive.find(comanda => comanda.id === idComanda);
  if (!comanda) {
      alert('Comanda nu a fost găsită.');
      return;
  }

  // Obținerea valorilor modificate
  comanda.client = document.getElementById('clientInput').value;
  comanda.data = document.getElementById('dataInput').value;
  comanda.status = document.getElementById('statusSelect').value;
  comanda.total = parseFloat(document.getElementById('totalInput').value);

  alert('Modificările au fost salvate.');

  // Re-afișarea detaliilor comenzi actualizate
  afiseazaDetaliiComanda(idComanda);
}


function incarcaComenzi(listaComenzi) {
    const dataStart = document.getElementById('dataStart').value;
    const dataEnd = document.getElementById('dataEnd').value;
    const comenziContainer = document.getElementById('comenziContainer');
    
    comenziContainer.innerHTML = ''; // Curățarea listei existente

    // Filtrarea listei de comenzi în funcție de intervalul de date selectat
    const comenziFiltrate = listaComenzi.filter(comanda => {
        // Presupunem că `comanda.data` este un string în format 'YYYY-MM-DD'
        return (!dataStart || comanda.data >= dataStart) && (!dataEnd || comanda.data <= dataEnd);
    });

    // Afișarea comenzilor filtrate
    comenziFiltrate.forEach(comanda => {
        const elementComanda = document.createElement('div');
        elementComanda.classList.add('comandaItem');
        elementComanda.innerHTML = `
            <h4>Comanda ${comanda.id}</h4>
            <p>Client: ${comanda.client}</p>
            <p>Data: ${comanda.data}</p>
        `;
        elementComanda.onclick = function() {
            afiseazaDetaliiComanda(comanda.id);
        };
        comenziContainer.appendChild(elementComanda);
    });
}

function incarcaAltceva() {
  salveazaDateInIndexedDB()
}
 
function stergeComanda(idComanda) {
  document.getElementById('detaliiComanda').innerHTML = "";
  // Găsește indexul comenzii în array
  const index = comenziFictive.findIndex(comanda => comanda.id === idComanda);
  if (index !== -1) {
      // Șterge comanda din array
      comenziFictive.splice(index, 1);
      
      // Actualizează lista de comenzi și UI-ul
      incarcaComenzi(comenziFictive);
  } else {
      alert('Comanda nu a fost găsită.');
  }
}
  // Funcție pentru afișarea detaliilor unei comenzi
  function afiseazaDetaliiComanda(idComanda) {
    const comanda = comenziFictive.find(comanda => comanda.id === idComanda);

    if (!comanda) {
        document.getElementById('detaliiComanda').innerHTML = 'Comanda nu a fost găsită.';
        return;
    }

    let detaliiHTML = `
        <h3>Detalii Comandă</h3>
        <p><strong>ID:</strong> ${comanda.id}</p>
        <p><strong>Client:</strong> <input type="text" value="${comanda.client}" id="clientInput"></p>
        <p><strong>Data:</strong> <input type="date" value="${comanda.data}" id="dataInput"></p>
        <p><strong>Status:</strong> 
            <select id="statusSelect">
                <option value="În așteptare" ${comanda.status === 'În așteptare' ? 'selected' : ''}>În așteptare</option>
                <option value="Finalizată" ${comanda.status === 'Finalizată' ? 'selected' : ''}>Finalizată</option>
                <option value="Anulată" ${comanda.status === 'Anulată' ? 'selected' : ''}>Anulată</option>
            </select>
        </p>
        <p><strong>Total:</strong> <input type="number" value="${comanda.total}" id="totalInput"></p>
        <div id="containerButoane">
          <button id="butonSalveaza">Salvează Modificările</button>
          <button id="butonSterge">Șterge Comandă</button>
        </div>

    `;
    document.getElementById('detaliiComanda').innerHTML = detaliiHTML;
    document.getElementById('butonSalveaza').addEventListener('click', () => {
      salveazaModificari(idComanda);
      incarcaComenzi(comenziFictive);
    });
    document.getElementById('butonSterge').addEventListener('click', () => {
      stergeComanda(idComanda);
    })
}
