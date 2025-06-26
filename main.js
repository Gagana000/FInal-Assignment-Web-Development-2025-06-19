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

// Logging Page
function switchTab(tab) {
  document.querySelectorAll(".tab-content").forEach((content) => {
    content.classList.remove("active");
  });

  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.classList.remove("active");
  });

  document.getElementById(tab + "-tab").classList.add("active");
  document
    .querySelector(`.tab-btn:nth-child(${tab === "customer" ? 1 : 2})`)
    .classList.add("active");
}

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

// CRUD - add product
const imageUploadArea = document.getElementById("imageUploadArea");
const fileInput = document.getElementById("image");
const imagePreview = document.getElementById("imagePreview");

imageUploadArea.addEventListener("click", () => fileInput.click());

imageUploadArea.addEventListener("dragover", (e) => {
  e.preventDefault();
  imageUploadArea.style.borderColor = "var(--primary-accent)";
  imageUploadArea.style.backgroundColor = "rgba(31, 122, 188, 0.1)";
});

imageUploadArea.addEventListener("dragleave", () => {
  imageUploadArea.style.borderColor = "rgba(31, 122, 188, 0.3)";
  imageUploadArea.style.backgroundColor = "rgba(225, 229, 242, 0.1)";
});

imageUploadArea.addEventListener("drop", (e) => {
  e.preventDefault();
  imageUploadArea.style.borderColor = "rgba(31, 122, 188, 0.3)";
  imageUploadArea.style.backgroundColor = "rgba(225, 229, 242, 0.1)";

  if (e.dataTransfer.files.length) {
    fileInput.files = e.dataTransfer.files;
    updatePreview();
  }
});

fileInput.addEventListener("change", updatePreview);

function updatePreview() {
  if (fileInput.files && fileInput.files[0]) {
    const reader = new FileReader();

    reader.onload = (e) => {
      imagePreview.src = e.target.result;
      imagePreview.style.display = "block";
      imageUploadArea.innerHTML = "";
      imageUploadArea.appendChild(imagePreview);

      const fileName = document.createElement("p");
      fileName.textContent = fileInput.files[0].name;
      fileName.style.marginTop = "15px";
      fileName.style.color = "var(--primary-dark)";
      imageUploadArea.appendChild(fileName);

      const changeText = document.createElement("p");
      changeText.textContent = "Click to change image";
      changeText.style.color = "var(--primary-accent)";
      changeText.style.fontSize = "0.9rem";
      imageUploadArea.appendChild(changeText);
    };

    reader.readAsDataURL(fileInput.files[0]);
  }
}

const description = document.getElementById("description");
if (description) {
  const charCounter = document.createElement("div");
  charCounter.style.textAlign = "right";
  charCounter.style.fontSize = "0.8rem";
  charCounter.style.color = "var(--primary-dark)";
  charCounter.style.opacity = "0.7";
  charCounter.style.marginTop = "5px";
  description.parentNode.appendChild(charCounter);

  description.addEventListener("input", () => {
    charCounter.textContent = `${description.value.length}/1000 characters`;
  });
}
