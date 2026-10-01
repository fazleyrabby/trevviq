import Alpine from 'alpinejs';
import maplibreAlpine from './maplibre-alpine';

window.Alpine = Alpine;
Alpine.data('maplibre', maplibreAlpine);

// Scroll reveal observer
document.addEventListener('DOMContentLoaded', () => {
    const reveals = document.querySelectorAll('.reveal-on-scroll');
    if ('IntersectionObserver' in window && reveals.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        reveals.forEach((el) => observer.observe(el));
    } else {
        reveals.forEach((el) => el.classList.add('is-revealed'));
    }
});

Alpine.start();
