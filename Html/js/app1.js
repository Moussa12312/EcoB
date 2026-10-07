document.addEventListener('DOMContentLoaded', function () {
  let currentIndex = 0;
  const items = document.querySelectorAll('.slider .item');
  const totalItems = items.length;
  const prevButton = document.querySelector('.prev');
  const nextButton = document.querySelector('.next');

  function showItem(index) {
      items.forEach((item, i) => {
          if (i === index) {
              item.style.transform = 'translateX(0)';
          } else if (i < index) {
              item.style.transform = 'translateX(-100%)';
          } else {
              item.style.transform = 'translateX(100%)';
          }
      });
  }

  function showNextItem() {
      currentIndex = (currentIndex + 1) % totalItems;
      showItem(currentIndex);
  }

  function showPrevItem() {
      currentIndex = (currentIndex - 1 + totalItems) % totalItems;
      showItem(currentIndex);
  }

  nextButton.addEventListener('click', showNextItem);
  prevButton.addEventListener('click', showPrevItem);

  showItem(currentIndex);
});
