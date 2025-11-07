// Mobile Menu Toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Close menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    });
});

// Smooth Scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Navbar Scroll Effect
let lastScroll = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    
    if (currentScroll > 100) {
        if (isDark) {
            navbar.style.background = 'rgba(10, 10, 10, 0.98)';
            navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.5)';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
        }
    } else {
        // CSS değişkenlerini kullan
        navbar.style.background = '';
        navbar.style.boxShadow = '';
    }
    
    lastScroll = currentScroll;
});

// Scroll Animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', () => {
    const animateElements = document.querySelectorAll('.service-card, .schedule-day, .stat-item, .info-item, .about-text, .about-image');
    
    animateElements.forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });
});

// Counter Animation
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const updateCounter = () => {
        current += increment;
        if (current < target) {
            element.textContent = Math.floor(current);
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target;
        }
    };
    
    updateCounter();
}

// Observe stat numbers for counter animation
const statObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const statNumber = entry.target;
            if (!statNumber.classList.contains('counted')) {
                statNumber.classList.add('counted');
                animateCounter(statNumber);
            }
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('.stat-number').forEach(stat => {
    statObserver.observe(stat);
});

// Form Submission
const contactForm = document.querySelector('.contact-form');
if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Get form values
        const formData = new FormData(contactForm);
        const name = contactForm.querySelector('input[name="name"]').value.trim();
        const email = contactForm.querySelector('input[name="email"]').value.trim();
        const phone = contactForm.querySelector('input[name="phone"]').value.trim();
        const message = contactForm.querySelector('textarea[name="message"]').value.trim();
        
        // Simple validation
        if (!name || !email || !message) {
            showFormMessage('Lütfen tüm zorunlu alanları doldurun.', 'error');
            return;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showFormMessage('Geçerli bir e-posta adresi giriniz.', 'error');
            return;
        }
        
        // Submit form
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Gönderiliyor...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('submit_contact.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showFormMessage(result.message, 'success');
                contactForm.reset();
            } else {
                showFormMessage(result.message, 'error');
            }
        } catch (error) {
            showFormMessage('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
            console.error('Form submission error:', error);
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Form message display
function showFormMessage(message, type) {
    const formMessage = document.getElementById('formMessage');
    if (formMessage) {
        formMessage.textContent = message;
        formMessage.className = 'form-message ' + (type === 'success' ? 'success' : 'error');
        formMessage.style.display = 'block';
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            formMessage.style.opacity = '0';
            setTimeout(() => {
                formMessage.style.display = 'none';
                formMessage.style.opacity = '1';
            }, 500);
        }, 5000);
    } else {
        alert(message);
    }
}

// Parallax Effect for Hero Section
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');
    if (hero) {
        const heroContent = hero.querySelector('.hero-content');
        if (scrolled < window.innerHeight) {
            heroContent.style.transform = `translateY(${scrolled * 0.5}px)`;
            heroContent.style.opacity = 1 - (scrolled / window.innerHeight);
        }
    }
});

// Floating Elements Animation Enhancement
const floatingBoxes = document.querySelectorAll('.float-box');
floatingBoxes.forEach((box, index) => {
    box.style.animationDelay = `${index * 2}s`;
});

// Service Cards Hover Effect Enhancement
const serviceCards = document.querySelectorAll('.service-card');
serviceCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Schedule Day Hover Effect
const scheduleDays = document.querySelectorAll('.schedule-day');
scheduleDays.forEach(day => {
    day.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px) scale(1.02)';
    });
    
    day.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Active Navigation Link
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-link');

function highlightNavLink() {
    let current = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if (window.pageYOffset >= sectionTop - 200) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
            link.classList.add('active');
        }
    });
}

window.addEventListener('scroll', highlightNavLink);

// Add active class styling
const style = document.createElement('style');
style.textContent = `
    .nav-link.active {
        color: var(--primary-color);
    }
    .nav-link.active::after {
        width: 100%;
    }
`;
document.head.appendChild(style);

// Typing Effect for Hero Title (Optional Enhancement)
function typeWriter(element, text, speed = 100) {
    let i = 0;
    element.textContent = '';
    
    function type() {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }
    
    type();
}

// Initialize on page load
window.addEventListener('load', () => {
    // Add loaded class for any additional animations
    document.body.classList.add('loaded');
    
    // Smooth fade in for page
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s ease';
        document.body.style.opacity = '1';
    }, 100);
});

// Performance optimization: Debounce scroll events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply debounce to scroll handlers
const debouncedScroll = debounce(() => {
    highlightNavLink();
}, 10);

window.addEventListener('scroll', debouncedScroll);

// Dark Mode Toggle
(function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle?.querySelector('.theme-icon');
    const html = document.documentElement;
    
    // Kullanıcının tercihini kontrol et
    const savedTheme = localStorage.getItem('theme') || 'light';
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Tercih yoksa sistem tercihini kullan
    const currentTheme = savedTheme === 'light' || savedTheme === 'dark' 
        ? savedTheme 
        : (prefersDark ? 'dark' : 'light');
    
    // Tema uygula
    function setTheme(theme) {
        if (theme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            if (themeIcon) themeIcon.textContent = '☀️';
            localStorage.setItem('theme', 'dark');
        } else {
            html.removeAttribute('data-theme');
            if (themeIcon) themeIcon.textContent = '🌙';
            localStorage.setItem('theme', 'light');
        }
    }
    
    // İlk yüklemede tema uygula
    setTheme(currentTheme);
    
    // Toggle butonuna tıklama
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    }
    
    // Sistem tema değişikliğini dinle (sadece kullanıcı tercihi yoksa)
    if (!localStorage.getItem('theme')) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            setTheme(e.matches ? 'dark' : 'light');
        });
    }
})();

