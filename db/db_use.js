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

export function updatePageWithData2(data, divelement, listaComenzi) {
    const resultDiv = document.getElementById(divelement);
    const produse = typeof data === 'string' ? JSON.parse(data) : data;

    let newContent = ''; 

    for (const [index, item] of produse.entries()) {
        let itemContent = `<div class="item-container">`;
        let propertiesContent = `<div class="item-properties">`; // Conținut pentru proprietăți
        for (const key in item) {
            if (item.hasOwnProperty(key)) {
                propertiesContent += `<span class="item-property"><strong>${key}:</strong> ${item[key]}</span>`;
            }
        }
        propertiesContent += `</div>`; // Închide containerul pentru proprietăți
        // Adaugă butonul "Adaugă comandă" la sfârșitul containerului pentru fiecare obiect
        let addButton = `<button class="add-order-btn" data-id="${item.id || ''}">+</button>`; // Folosește "+" pentru un buton compact
        itemContent += propertiesContent + addButton + `</div>`; 
        newContent += itemContent;
    }
    resultDiv.innerHTML = newContent;

    // Adaugă event listeners pentru butoanele "Adaugă comandă"
    document.querySelectorAll('.add-order-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id; // Obține 'id' din data-id al butonului apăsat
            const produs = produse.find(p => p.id == id); // Găsește produsul corespunzător în array
            
            if (produs) {
                console.log(`Adaugă comandă pentru ID-ul: ${id}`, produs);
                adaugaFormularComanda(listaComenzi, produs); // Trimite produsul corespunzător la funcția adaugaFormularComanda
            } else {
                console.log('Produsul nu a fost găsit!');
            }
        });
    });
}

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



