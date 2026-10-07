/**
 * ECOB UNIVERSITY — JAVASCRIPT GLOBAL & MOBILE DRAWER
 * Navigation tactile, slider avec dots, backdrop et alertes auto-dismiss
 */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  // 1. Menu Mobile Drawer & Backdrop
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const drawerCloseBtn = document.getElementById('drawerCloseBtn');
  const mainNav = document.getElementById('mainNav');
  const navBackdrop = document.getElementById('navBackdrop');

  function openDrawer() {
    if (mainNav && navBackdrop) {
      mainNav.classList.add('active');
      navBackdrop.classList.add('active');
      document.body.style.overflow = 'hidden'; // Empêcher le scroll sous le menu
    }
  }

  function closeDrawer() {
    if (mainNav && navBackdrop) {
      mainNav.classList.remove('active');
      navBackdrop.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', openDrawer);
  }

  if (drawerCloseBtn) {
    drawerCloseBtn.addEventListener('click', closeDrawer);
  }

  if (navBackdrop) {
    navBackdrop.addEventListener('click', closeDrawer);
  }

  // Gestion des dropdowns (Desktop & Mobile) - Aucun saut vers l'entête
  const dropdownParents = document.querySelectorAll('.dropdown-parent');
  dropdownParents.forEach(dp => {
    const toggleLink = dp.querySelector('.dropdown-toggle') || dp.querySelector('a');
    if (toggleLink) {
      toggleLink.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = dp.classList.contains('open');

        // Fermer tous les autres menus déroulants
        dropdownParents.forEach(other => {
          if (other !== dp) {
            other.classList.remove('open');
            const otherToggle = other.querySelector('.dropdown-toggle') || other.querySelector('a');
            if (otherToggle) otherToggle.setAttribute('aria-expanded', 'false');
          }
        });

        // Basculer l'état
        if (isOpen) {
          dp.classList.remove('open');
          toggleLink.setAttribute('aria-expanded', 'false');
        } else {
          dp.classList.add('open');
          toggleLink.setAttribute('aria-expanded', 'true');
        }
      });
    }

    // Clic sur un lien à l'intérieur du menu déroulant
    const subItems = dp.querySelectorAll('.dropdown-item');
    subItems.forEach(item => {
      item.addEventListener('click', function () {
        closeDrawer();
      });
    });
  });

  // Fermer les dropdowns lors d'un clic ailleurs dans la page
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.dropdown-parent')) {
      dropdownParents.forEach(dp => {
        dp.classList.remove('open');
        const toggleLink = dp.querySelector('.dropdown-toggle') || dp.querySelector('a');
        if (toggleLink) toggleLink.setAttribute('aria-expanded', 'false');
      });
    }
  });

  // Fermer le drawer lors d'un clic sur un lien normal
  if (mainNav) {
    mainNav.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', function () {
        if (!this.classList.contains('dropdown-toggle') && !this.parentElement.classList.contains('dropdown-parent')) {
          closeDrawer();
        }
      });
    });
  }

  // Interception universelle des ancres pour défilement fluide sans rafraîchir
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#' || targetId === '') {
        e.preventDefault();
        return;
      }
      try {
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          e.preventDefault();
          closeDrawer();
          targetElement.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      } catch (err) {
        // En cas de sélecteur invalide, éviter tout saut vers le haut
        e.preventDefault();
      }
    });
  });

  // 2. Slider Accueil avec Dots & Support Tactile
  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.slider-dot');
  const prevBtn = document.querySelector('.slider-arrow.prev');
  const nextBtn = document.querySelector('.slider-arrow.next');

  if (slides.length > 0) {
    let currentSlide = 0;
    let slideTimer = null;

    function goToSlide(index) {
      currentSlide = (index + slides.length) % slides.length;
      slides.forEach((s, i) => {
        s.classList.toggle('active', i === currentSlide);
      });
      dots.forEach((d, i) => {
        d.classList.toggle('active', i === currentSlide);
      });
    }

    function nextSlide() {
      goToSlide(currentSlide + 1);
    }

    function prevSlide() {
      goToSlide(currentSlide - 1);
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        nextSlide();
        resetTimer();
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        prevSlide();
        resetTimer();
      });
    }

    dots.forEach(dot => {
      dot.addEventListener('click', function () {
        const slideIdx = parseInt(this.getAttribute('data-slide'), 10);
        goToSlide(slideIdx);
        resetTimer();
      });
    });

    function startTimer() {
      slideTimer = setInterval(nextSlide, 7000);
    }

    function resetTimer() {
      clearInterval(slideTimer);
      startTimer();
    }

    startTimer();

    // Support du Swipe Tactile sur Mobile
    let touchStartX = 0;
    let touchEndX = 0;
    const sliderWrap = document.querySelector('.hero-slider-wrapper');

    if (sliderWrap) {
      sliderWrap.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
      }, { passive: true });

      sliderWrap.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
      }, { passive: true });
    }

    function handleSwipe() {
      const diff = touchEndX - touchStartX;
      if (Math.abs(diff) > 40) {
        if (diff < 0) {
          nextSlide();
        } else {
          prevSlide();
        }
        resetTimer();
      }
    }
  }

  // 3. Header Scrolled Shadow Effect
  const header = document.querySelector('.site-header');
  if (header) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 40) {
        header.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.3)';
      } else {
        header.style.boxShadow = '';
      }
    }, { passive: true });
  }

  // 4. Alertes auto-dismiss
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 6000);
  });

  // 5. Soumission des Formulaires en Direct Sans Rechargement (Sur Place)
  const ajaxForms = document.querySelectorAll('form[action="process-admission.php"], form[action="process-contact.php"], form[action="process-comment.php"]');
  ajaxForms.forEach(form => {
    form.addEventListener('submit', function (e) {
      if (!this.checkValidity()) {
        return; // Laisser les validations HTML5 natives
      }

      e.preventDefault();
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
      }

      // Supprimer les alertes existantes dans ce formulaire
      const parentCard = this.closest('.form-card') || this.parentElement;
      const oldAlerts = parentCard.querySelectorAll('.alert, .ajax-alert');
      oldAlerts.forEach(a => a.remove());

      const formData = new FormData(this);
      formData.append('ajax', '1');

      fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }

        const alertBox = document.createElement('div');
        alertBox.className = data.status === 'success' ? 'alert alert-success ajax-alert' : 'alert alert-danger ajax-alert';
        alertBox.style.marginTop = '1rem';
        alertBox.style.marginBottom = '1.25rem';
        const icon = document.createElement('i');
        icon.className = data.status === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        icon.style.fontSize = '1.2rem';
        const msgDiv = document.createElement('div');
        msgDiv.textContent = data.message || '';
        alertBox.appendChild(icon);
        alertBox.appendChild(msgDiv);

        // Insérer avant le formulaire ou au début
        form.parentNode.insertBefore(alertBox, form);

        if (data.status === 'success') {
          form.reset();
        }

        // Auto-dismiss après 8s
        setTimeout(() => {
          alertBox.style.transition = 'opacity 0.5s ease';
          alertBox.style.opacity = '0';
          setTimeout(() => alertBox.remove(), 500);
        }, 8000);
      })
      .catch(err => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnHtml;
        }
        // En cas de problème réseau, soumettre de manière classique
        form.submit();
      });
    });
  });

  // 6. Détection et animations de défilement (Scroll Reveal & Rebondissement)
  const reveals = document.querySelectorAll('.reveal, .feature-card, .event-card, .chapitre');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -40px 0px'
    });

    reveals.forEach(el => {
      if (!el.classList.contains('visible')) {
        el.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
        observer.observe(el);
      }
    });
  } else {
    reveals.forEach(el => el.classList.add('visible'));
  }
});
