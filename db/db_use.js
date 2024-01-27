 export function fetchFromApi(action, params = {}) {
    params.action = action;
    const url = "./db/api.php"; 
    return fetch(url + '?' + new URLSearchParams(params), {
        method: 'GET'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Eroare rețea: ' + response.statusText);
        }
        return response.json();
    })
    .catch(error => console.error('Eroare: ' + error));
}

// fetchFromApi('api.php', 'listAll', { userId: 1234 })
//     .then(data => {
//         updatePageWithData(data);
//     });

