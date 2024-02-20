export function updatePageWithData(data, divelement) {
    const resultDiv = document.getElementById('detaliiComanda');

    console.log("sunt in updatePageWithData", data);
    let newContent = '<h2>Date primite de la server:</h2>';
    for (const item of data) {
        newContent += `<p>${item.nume}: ${item.produs}: ${item.detalii}: ${item.data}</p>`;
}
resultDiv.innerHTML = newContent;

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
        return await response.json();
    } catch (error) {
        console.error('Eroare:', error);
        throw error; // Tratați eroarea în mod corespunzător sau relansați-o pentru a fi tratată mai sus
    }
}

//  async function fetchFromApi(action) {
//     params.action = action;
//     const url = "api.php"; 
//     try {
//           const response = await fetch(url + '?' + new URLSearchParams(params), {
//               method: 'GET'
//           });
//           if (!response.ok) {
//               throw new Error('Eroare rețea: ' + response.statusText);
//           }
//           return await response.json();
//       } catch (error) {
//           return console.error('Eroare: ' + error);
//       }
// }
// fetchFromApi('api.php', 'listAll', { userId: 1234 })
//     .then(data => {
//         updatePageWithData(data);
//     });

