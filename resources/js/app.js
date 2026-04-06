import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * Page entrance animation
 * Triggers on every Blade page load (not SPA navigation).
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('main')?.classList.add('page-enter');
});

/**
 * Global confirm-delete helper used by Blade templates.
 * Usage: <button onclick="confirmDelete('form-id')">Delete</button>
 */
window.confirmDelete = (formId) => {
    if (confirm('Are you sure? This action cannot be undone.')) {
        document.getElementById(formId)?.submit();
    }
};

/**
 * Flash message auto-dismiss after 4 seconds.
 */
document.addEventListener('DOMContentLoaded', () => {
    const flashes = document.querySelectorAll('[data-flash]');
    flashes.forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 4000);
    });
});
