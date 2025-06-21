document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const toggle = document.querySelector('.mobile-toggle');
    const middleSection = document.querySelector('.middle-section');
    const rightSection = document.querySelector('.right-section');
    const navLinks = document.querySelectorAll('.nav-buttons a');
    
    // Toggle mobile menu
    function toggleMenu() {
        // Toggle active class on sections
        middleSection.classList.toggle('active');
        rightSection.classList.toggle('active');
        
        // Change icon
        const icon = toggle.querySelector('i');
        if (middleSection.classList.contains('active')) {
            icon.classList.replace('fa-bars', 'fa-xmark');
        } else {
            icon.classList.replace('fa-xmark', 'fa-bars');
        }
    }
    
    // Close menu when clicking on a link (mobile only)
    function closeMenuOnLinkClick() {
        if (window.innerWidth <= 768) {
            middleSection.classList.remove('active');
            rightSection.classList.remove('active');
            const icon = toggle.querySelector('i');
            icon.classList.replace('fa-xmark', 'fa-bars');
        }
    }
    
    // Event Listeners
    toggle.addEventListener('click', toggleMenu);
    
    navLinks.forEach(link => {
        link.addEventListener('click', closeMenuOnLinkClick);
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Ensure menu is visible on desktop
            middleSection.classList.remove('active');
            rightSection.classList.remove('active');
            const icon = toggle.querySelector('i');
            if (icon) icon.classList.replace('fa-xmark', 'fa-bars');
        }
    });
});