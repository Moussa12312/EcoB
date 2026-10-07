// Initialiser AOS une fois que le DOM est complètement chargé
document.addEventListener("DOMContentLoaded", function() {
	// Initialisation d'AOS avec des options si nécessaire
	AOS.init({
			duration: 1000,  // Durée de l'animation en millisecondes
			easing: 'ease-in-out',  // Fonction de timing de l'animation
			once: true,  // Les animations se produiront une seule fois
			offset: 200,  // Définir l'offset pour les animations (quand elles apparaissent)
	});
});
