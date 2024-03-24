import { adaugaFormularComanda } from '../OrderForm/formularComanda.js';

export function updatePageWithData(data, divelement) {
    const resultDiv = document.getElementById('detaliiComanda');
    //const produse = JSON.parse(data);
    const produse = typeof data === 'string' ? JSON.parse(data) : data;

    console.log("sunt in updatePageWithData :", produse);
    let newContent = ''; // Inițializează newContent ca un șir gol

    for (const item of produse) {
        // Construiește șirul folosind valorile din obiectul item
        newContent += `<p>Client: ${item.client}, Telefon: ${item.telefon}, Data: ${item.data}, Termen Livrare: ${item.termenLivrare}, Urgență: ${item.urgenta ? 'Da' : 'Nu'}, Status: ${item.status}, Note: ${item.note}, Detalii: ${item.detalii}, Total: ${item.total}</p>`;
    }
resultDiv.innerHTML = newContent;

} 

// function attachEventListeners(data) {
//     // Atașează event listenerii
//     const sortButtonData = document.getElementById('sortButtonData');
//     const filterInput = document.getElementById('filterInput');
//     let sortAsc = false;

//     if (sortButtonData) {
//         sortButtonData.onclick = () => {
//             console.log('Sortează după Dată');
//             let datajs = JSON.parse(data);
    
//             // Sortează datele în ordine ascendentă sau descendentă bazată pe flag-ul sortAsc
//             datajs.sort((a, b) => {
//                 return sortAsc 
//                     ? new Date(a.data) - new Date(b.data) 
//                     : new Date(b.data) - new Date(a.data);
//             });
    
//             // Inversează flag-ul pentru următoarea sortare
//             sortAsc = !sortAsc;
    
//             let updatedDataJSON = JSON.stringify(datajs);
//             updatePageWithData2(updatedDataJSON, 'divelement', listaComenzi);
//         };
//     }
    
//     if (filterInput) {
//         filterInput.oninput = (e) => {
//             console.log('Filtreaza:', filterInput.value);
//             const textFiltru = e.target.value.toLowerCase();
//             let datajs = JSON.parse(data);
//             const dataFiltrata = datajs.filter(item => {
//                 // Combina valorile tuturor proprietăților de interes într-un singur șir
//                 let valoriCombinat = `${item.nume} ${item.produs} ${item.idprodus} ${item.detalii} ${item.tel} ${item.email} ${item.data_comanda}`.toLowerCase();
//                 // Verifică dacă șirul combinat conține textul de filtru
//                 return valoriCombinat.includes(textFiltru);
//             });
//             let updatedDataJSON = JSON.stringify(dataFiltrata);

//             updatePageWithData2(updatedDataJSON, 'divelement', listaComenzi); // Reafisează lista filtrată
//         };
//     }
// }
// export function updatePageWithOrdersFromSite2(data, divelement, listaComenzi) {
//     const resultDiv = document.getElementById(divelement);


//     let controlPanel = document.getElementById('controlPanel');
//     if (!controlPanel) {
//         resultDiv.innerHTML = '';
//         console.log(resultDiv); // Ar trebui să afișeze elementul, nu `null` sau `undefined`
//         console.log('Adaugă controlPanel');
//         controlPanel = document.createElement('div');
//         controlPanel.id = 'controlPanel';
//         controlPanel.innerHTML = `
//             <button id="sortButtonData">Sortează după Dată</button>
//             <input type="text" id="filterInput" placeholder="Filtrează după Nume...">
//         `;
//         resultDiv.insertBefore(controlPanel, resultDiv.firstChild); // Adaugă controlPanel la începutul resultDiv dacă nu există
//         attachEventListeners(data);

//     } 
//     let contentContainer = document.getElementById('contentContainer');
//     if (!contentContainer) {
//         contentContainer = document.createElement('div');
//         contentContainer.id = 'contentContainer';
//         resultDiv.appendChild(contentContainer);
//     }


//     let newContent = ''; // Inițializează newContent ca un șir gol

//     const produse = typeof data === 'string' ? JSON.parse(data) : data;



//     for (const [index, item] of produse.entries()) {
//         let itemContent = `<div class="item-container">`;
//         let propertiesContent = `<div class="item-properties">`; // Conținut pentru proprietăți
//         for (const key in item) {
//             if (item.hasOwnProperty(key)) {
//                 propertiesContent += `<span class="item-property"><strong>${key}:</strong> ${item[key]}</span>`;
//             }
//         }
//         propertiesContent += `</div>`; // Închide containerul pentru proprietăți
//         // Adaugă butonul "Adaugă comandă" la sfârșitul containerului pentru fiecare obiect
//         let addButton = `<button class="add-order-btn" data-id="${item.id || ''}">+</button>`; // Folosește "+" pentru un buton compact
//         itemContent += propertiesContent + addButton + `</div>`; 
//         newContent += itemContent;
//     }
//     contentContainer.innerHTML = newContent;

//     // Adaugă event listeners pentru butoanele "Adaugă comandă"
//     document.querySelectorAll('.add-order-btn').forEach(button => {
//         button.addEventListener('click', function() {
//             const id = this.dataset.id; // Obține 'id' din data-id al butonului apăsat
//             const produs = produse.find(p => p.id == id); // Găsește produsul corespunzător în array
            
//             if (produs) {
//                 console.log(`Adaugă comandă pentru ID-ul: ${id}`, produs);
//                 adaugaFormularComanda(listaComenzi, produs); // Trimite produsul corespunzător la funcția adaugaFormularComanda
//             } else {
//                 console.log('Produsul nu a fost găsit!');
//             }
//         });
//     });
// }

export async function fetchFromApi(action, params) {
    params.action = action;
    
    const url = "db/api.php"; 
    try {
        const response = await fetch(url + '?' + new URLSearchParams(params), {
            method: 'GET'
        });
        if (!response.ok) {
            throw new Error('Eroare rețea fetchFromApi: ' + response.statusText);
        }
        
        // Extrageți conținutul text al răspunsului
        const responseBody = await response.text();

        // Afișați conținutul text în consolă
        console.log('fetchFromApi response:', responseBody);

        // Returnați conținutul text al răspunsului
        return responseBody;
    } catch (error) {
        // Tratați cazurile de eroare
        console.error('Error:', error.message);
        throw error; // Tratați eroarea în mod corespunzător sau relansați-o pentru a fi tratată mai sus
    }


}



