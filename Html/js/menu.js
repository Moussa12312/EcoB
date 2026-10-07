// Menu hamburger responsive
document.addEventListener('DOMContentLoaded', function() {
  const icon = document.querySelector('.icon');
  const menu = document.querySelector('.menu');
  
  if (icon && menu) {
    icon.addEventListener('click', () => {
      menu.classList.toggle('active');
    });
    
    // Fermer le menu quand on clique sur un lien (mobile)
    menu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 900) {
          menu.classList.remove('active');
        }
      });
    });
    
    // Fermer le menu si on redimensionne la fenêtre
    window.addEventListener('resize', () => {
      if (window.innerWidth > 900) {
        menu.classList.remove('active');
      }
    });
  }
});