import { actualizaza_lisa } from "../dom/dom_api.js";
import {generateDetaliiComandaHTML } from "../orderForm.js";
import {adaugaEvenimentButonAdaugare} from "../eventLAddProd.js";

// Crearea unei instanțe Date pentru data curentă
const dataCurenta = new Date();
// Obținerea șirului ISO8601 pentru data curentă (de exemplu, "2024-02-04")
const dataCurentaISO = dataCurenta.toISOString().split('T')[0];
const dataLivrare = new Date(dataCurenta); // Inițializăm cu data curentă
dataLivrare.setDate(dataLivrare.getDate() + 42);
const dataLivrareISO = dataLivrare.toISOString().split('T')[0];

// Creați o comandă nouă cu datele dorite


export function adaugaFormularComanda(listaComenzi) {
  const comandaNoua = {
    id: listaComenzi.length + 1, // ID-ul este bazat pe numărul de comenzi existente
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
  const adaugaComandaButton = document.createElement('button');
  adaugaComandaButton.textContent = 'Adaugă Comandă';
  buttonContainer.appendChild(adaugaComandaButton);
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
 


    //actualizaza_lisa(listaComenzi)

    console.log('Butonul "Adaugă Comandă" a fost apăsat.',listaComenzi);
    
  });



  // formularContainer.appendChild(statusInput);
  // formularContainer.appendChild(dataInput);

  // formularContainer.appendChild(totalInput);
  // formularContainer.appendChild(clientInput);

  // formularContainer.appendChild(buttonContainer);
  // buttonContainer.appendChild(adaugaComandaButton);


  const link = document.createElement('link');
  link.href = './OrderForm/OrderFormStyle.css';
  link.rel = 'stylesheet';
  link.type = 'text/css';

  // container.appendChild(formularContainer);
  // container.appendChild(link);
}
