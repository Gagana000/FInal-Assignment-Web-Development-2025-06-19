// ----------------------------- RESPONSIVE NAVIGATION BAR ---------------------------------------------------
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

// Register page
function togglePassword(fieldId) {
  const field = document.getElementById(fieldId);
  const icon = field.nextElementSibling.querySelector("i");

  if (field.type === "password") {
    field.type = "text";
    icon.classList.replace("fa-eye", "fa-eye-slash");
  } else {
    field.type = "password";
    icon.classList.replace("fa-eye-slash", "fa-eye");
  }
}
