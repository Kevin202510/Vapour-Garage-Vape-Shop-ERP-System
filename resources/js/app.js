import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.confirmDelete = (formId) => {
    if (confirm('Are you sure? This action cannot be undone.')) {
        document.getElementById(formId)?.submit();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('main')?.classList.add('page-enter');
});

document.addEventListener('DOMContentLoaded', () => {
    const flashes = document.querySelectorAll('[data-flash]');
    flashes.forEach((el) => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 4000);
    });
});

Alpine.start();