// Fonction pour détecter le scroll et révéler les sections
document.addEventListener("scroll", () => {
  const sections = document.querySelectorAll(".section");
  const scrollPosition = window.scrollY + window.innerHeight;

  sections.forEach((section) => {
      if (scrollPosition > section.offsetTop + 100) {
          section.setAttribute("data-scroll", "true");
      }
  });
});