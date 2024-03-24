// Funcțiile de încărcare și eliminare CSS
export function incarcaCSS(href) {
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = href;
  document.head.appendChild(link);
}

export function eliminaCSS(href) {
  console.log("Eliminare CSS");
  const links = document.head.querySelectorAll('link');
  for (let link of links) {
      if (link.href.endsWith(href)) {
          document.head.removeChild(link);
      }
  }
}
