export function adaugaEvenimentButonAdaugare() {
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
}
