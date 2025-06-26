document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.querySelector(".mobile-toggle");
  const middleSection = document.querySelector(".middle-section");
  const rightSection = document.querySelector(".right-section");
  const navLinks = document.querySelectorAll(".nav-buttons a");

  function toggleMenu() {
    middleSection.classList.toggle("active");
    rightSection.classList.toggle("active");

    const icon = toggle.querySelector("i");
    if (middleSection.classList.contains("active")) {
      icon.classList.replace("fa-bars", "fa-xmark");
    } else {
      icon.classList.replace("fa-xmark", "fa-bars");
    }
  }

  function closeMenuOnLinkClick() {
    if (window.innerWidth <= 768) {
      middleSection.classList.remove("active");
      rightSection.classList.remove("active");
      const icon = toggle.querySelector("i");
      icon.classList.replace("fa-xmark", "fa-bars");
    }
  }

  toggle.addEventListener("click", toggleMenu);

  navLinks.forEach((link) => {
    link.addEventListener("click", closeMenuOnLinkClick);
  });

  window.addEventListener("resize", function () {
    if (window.innerWidth > 768) {
      middleSection.classList.remove("active");
      rightSection.classList.remove("active");
      const icon = toggle.querySelector("i");
      if (icon) icon.classList.replace("fa-xmark", "fa-bars");
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const slides = document.querySelectorAll(".slide");
  const indicators = document.querySelectorAll(".slide-indicators span");
  let currentSlide = 0;

  function initSlider() {
    slides[0].classList.add("active");
    createIndicators();
    updateIndicators();
    slides.forEach((slide, index) => {
      slide.style.transform = `translateX(${index * 100}%)`;
    });
  }

  function createIndicators() {
    const indicatorsContainer = document.querySelector(".slide-indicators");
    indicatorsContainer.innerHTML = "";

    slides.forEach((slide, index) => {
      const indicator = document.createElement("span");
      indicator.dataset.index = index;
      indicator.addEventListener("click", () => goToSlide(index));
      indicatorsContainer.appendChild(indicator);
    });
  }

  function updateIndicators() {
    document
      .querySelectorAll(".slide-indicators span")
      .forEach((indicator, index) => {
        indicator.classList.toggle("active", index === currentSlide);
      });
  }

  function goToSlide(index) {
    slides[currentSlide].classList.remove("active");
    currentSlide = (index + slides.length) % slides.length;
    slides[currentSlide].classList.add("active");
    updateIndicators();
  }

  function nextSlide() {
    goToSlide(currentSlide + 1);
  }

  function prevSlide() {
    goToSlide(currentSlide - 1);
  }

  document.querySelector(".next-slide").addEventListener("click", nextSlide);
  document.querySelector(".prev-slide").addEventListener("click", prevSlide);

  let slideInterval = setInterval(nextSlide, 5000);

  document.querySelector(".hero-slider").addEventListener("mouseenter", () => {
    clearInterval(slideInterval);
  });

  document.querySelector(".hero-slider").addEventListener("mouseleave", () => {
    slideInterval = setInterval(nextSlide, 5000);
  });

  initSlider();
});
