export let comenziFictive = [
  {
    id: 1,
    client: 'Ion Popescu',
    telefon: '12345',
    data: '2024-01-01',
    termenLivrare: '2024-02-01',
    urgenta: false,
    produse: [
      { nume: "masă", cantitate: 1, valoare: 200, etapaFabricatie: "Pregătire" },
      { nume: "scaun", cantitate: 4, valoare: 400, etapaFabricatie: "În producție" }
    ],
    status: 'În așteptare',
    note: 'Preferă lemn de nuc.',
    detalii: 'Verificați dimensiunile pentru livrare.',
    total: 600
  },
  {
    id: 2,
    client: 'Maria Ionescu',
    telefon: '56789',
    data: '2024-01-15',
    termenLivrare: '2024-03-01',
    urgenta: true,
    produse: [
      { nume: "canapea", cantitate: 1, valoare: 300, etapaFabricatie: "Finalizat" },
      { nume: "fotoliu", cantitate: 2, valoare: 150, etapaFabricatie: "În producție" }
    ],
    status: 'Finalizată',
    note: 'Asigurați ambalarea corespunzătoare.',
    detalii: 'Culoare personalizată: albastru marin.',
    total: 450
  },
  {
    id: 3,
    client: 'Elena Nitu',
    telefon: '23456',
    data: '2024-01-20',
    termenLivrare: '2024-03-05',
    urgenta: false,
    produse: [
      { nume: "bibliotecă", cantitate: 1, valoare: 500, etapaFabricatie: "Pregătire" },
      { nume: "birou", cantitate: 1, valoare: 250, etapaFabricatie: "Finalizat" }
    ],
    status: 'În așteptare',
    note: 'Livrare la etajul 2.',
    detalii: 'Clientul dorește confirmare telefonică înainte de livrare.',
    total: 750
  },
  {
    id: 4,
    client: 'Adrian Baciu',
    telefon: '34567',
    data: '2024-02-01',
    termenLivrare: '2024-04-01',
    urgenta: false,
    produse: [
      { nume: "paturi suprapuse", cantitate: 2, valoare: 800, etapaFabricatie: "În producție" },
      { nume: "noptieră", cantitate: 2, valoare: 100, etapaFabricatie: "Pregătire" }
    ],
    status: 'În curs de procesare',
    note: 'Verificați rezistența structurii.',
    detalii: 'Se dorește o vopsea ecologică pentru lemn.',
    total: 900
  },
  {
    id: 5,
    client: 'Cristina Soare',
    telefon: '45678',
    data: '2024-02-15',
    termenLivrare: '2024-04-15',
    urgenta: true,
    produse: [
      { nume: "comodă", cantitate: 1, valoare: 300, etapaFabricatie: "Finalizat" },
      { nume: "oglindă", cantitate: 1, valoare: 150, etapaFabricatie: "În producție" }
    ],
    status: 'Expediată',
    note: 'Ambalaj anti-șoc necesar pentru oglindă.',
    detalii: 'Informații suplimentare vor fi furnizate de designerul interior.',
    total: 450
  }
];
