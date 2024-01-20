export function adaugaFormularComanda() {
  const container = document.getElementById('detaliiComanda');

  const formularContainer = document.createElement('div');
  formularContainer.classList.add('formularComanda');

  // Add CSS styles for the formularComanda container
  formularContainer.style.margin = '10px 0';
  formularContainer.style.padding = '10px';
  formularContainer.style.border = '1px solid black';

  const clientInput = document.createElement('input');
  clientInput.type = 'text';
  clientInput.name = 'client';
  clientInput.placeholder = 'Numele clientului';

  // Add CSS styles for the clientInput field
  clientInput.classList.add('input');

  const dataInput = document.createElement('input');
  dataInput.type = 'date';
  dataInput.name = 'data';
  dataInput.placeholder = 'Data comenzii';

  // Add CSS styles for the dataInput field
  dataInput.classList.add('input');

  const statusInput = document.createElement('select');
  statusInput.name = 'status';

  const statusOptions = [
    { value: 'În așteptare', text: 'În așteptare' },
    { value: 'Finalizată', text: 'Finalizată' },
    { value: 'Anulată', text: 'Anulată' },
  ];

  statusOptions.forEach((option) => {
    const optionElement = document.createElement('option');
    optionElement.value = option.value;
    optionElement.text = option.text;
    statusInput.appendChild(optionElement);
  });

  // Add CSS styles for the statusInput field
  statusInput.classList.add('input');

  const totalInput = document.createElement('input');
  totalInput.type = 'number';
  totalInput.name = 'total';
  totalInput.placeholder = 'Totalul comenzii';

  // Add CSS styles for the totalInput field
  totalInput.classList.add('input');

  const buttonContainer = document.createElement('div');
  buttonContainer.classList.add('buttonContainer');

  const adaugaComandaButton = document.createElement('button');
  adaugaComandaButton.textContent = 'Adaugă Comandă';

  buttonContainer.appendChild(adaugaComandaButton);

  formularContainer.appendChild(buttonContainer);

  formularContainer.appendChild(statusInput);
  formularContainer.appendChild(totalInput);
  formularContainer.appendChild(clientInput);

  const link = document.createElement('link');
  link.href = './OrderForm/OrderFormStyle.css';
  link.rel = 'stylesheet';
  link.type = 'text/css';

  container.appendChild(formularContainer);
  container.appendChild(link);
}
