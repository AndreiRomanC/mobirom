export function generateDetaliiComandaHTML(comanda) {
    console.log(comanda);
let detaliiHTML = `
<link rel="stylesheet" href="./OrderForm/OrderFormStyle.css">
<div class="comanda-container">
    <div class="comanda-header">
        <h3>Comandă #${comanda.id}</h3>
        <div class="comanda-urgenta">
            <label for="comandaUrgentaInput">Comandă Urgentă:</label>
            <input type="checkbox" id="comandaUrgentaInput" ${comanda.urgenta ? 'checked' : ''}>
        </div>
    </div>
    <div class="ultima-modificare">
        <span class="modificare-info"><strong>Ultima modificare:</strong> ${comanda.mod_user ? comanda.mod_user : 'N/A'}, ${comanda.mod_timestamp ? comanda.mod_timestamp : 'N/A'}</span>
    </div>
</div>



</div>
<div class="flex-container align-items-center">
    <div class="flex-item medium"><strong>Client:</strong> <input type="text" value="${comanda.client}" id="clientInput" class="input-field"></div>
    <div class="flex-item medium"><strong>Telefon:</strong> <input type="tel" value="${comanda.telefon}" id="telefonInput" class="input-field"></div>
    <div class="flex-item medium"><strong>Email:</strong> <input type="tel" value="${comanda.email}" id="emailInput" class="input-field"></div>
    <div class="flex-item medium"><strong>Data:</strong> <input type="date" value="${comanda.data}" id="dataInput" class="input-field"></div>
    <div class="flex-item medium"><strong>Termen Livrare:</strong> <input type="date" value="${comanda.termenLivrare}" id="termenLivrareInput" class="input-field"></div>
</div>
<div id="liniiAdaugate">`;
let isFirstProduct = true;

// Aici generăm HTML pentru fiecare produs
comanda.produse.forEach((produs, index) => {
    detaliiHTML += `
    <div id = "linieProdusTemplate" class="linieProdusTemplate flex-container justify-content-center align-items-center">
        <div class="flex-item medium"><strong>Produs:</strong> <input type="text" value="${produs.nume}" class="input-field produsInput"></div>
        <div class="flex-item small"><strong>Cantitate:</strong> <input type="number" value="${produs.cantitate}" class="input-field cantitateInput"></div>
        <div class="flex-item medium"><strong>Total Valoare:</strong> <input type="text" value="${produs.valoare}" class="input-field totalInput"></div>
        <div class="flex-item large"><strong>Etapa Fabricație:</strong>
            <select class="input-field etapaFabricatieSelect">` +
                `<option value="Pregătire" ${produs.etapaFabricatie === "Pregătire" ? "selected" : ""}>Pregătire</option>` +
                `<option value="În producție" ${produs.etapaFabricatie === "În producție" ? "selected" : ""}>În producție</option>` +
                `<option value="Finalizat" ${produs.etapaFabricatie === "Finalizat" ? "selected" : ""}>Finalizat</option>
            </select>
            </select>
        </div>`;
    
    // Adăugăm butonul "+" doar la primul produs
    if (isFirstProduct) {
        detaliiHTML += `
        <div>
            <button id="butonAdaugare" class="btn-small" title="Adaugă element">+</button>
        </div>`;
        isFirstProduct = false;
    } else {
        // Pentru celelalte produse adăugăm butonul "-" și gestionăm evenimentul de ștergere
        detaliiHTML += `
        <div>
            <button id = "btn-sterge-produs" class="btn-small" title="Șterge element">-</button>
        </div>`;
    }
    detaliiHTML += `
    </div>`;
});
// Continuăm string-ul după bucla forEach
detaliiHTML += `</div>
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
    <textarea id="detaliiInput" class="input-field full-width">${comanda.detalii}</textarea>
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

return detaliiHTML;
}

