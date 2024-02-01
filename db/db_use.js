   export async function fetchFromApi(action, params = {}) {
    params.action = action;
    const url = "api.php"; 
    try {
          const response = await fetch(url + '?' + new URLSearchParams(params), {
              method: 'GET'
          });
          if (!response.ok) {
              throw new Error('Eroare rețea: ' + response.statusText);
          }
          return await response.json();
      } catch (error) {
          return console.error('Eroare: ' + error);
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

