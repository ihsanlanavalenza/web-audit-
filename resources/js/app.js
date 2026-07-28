import './bootstrap';

const themeKey = 'webaudit-theme-mode';
const themeButtons = ['theme-toggle', 'theme-toggle-mobile'];
const getStoredTheme = () => localStorage.getItem(themeKey);
const getSystemTheme = () => window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
const applyTheme = (theme) => {
    const isDark = theme === 'dark';
    document.body.classList.toggle('dark-mode', isDark);
    document.body.classList.toggle('light-mode', !isDark);
    themeButtons.forEach((id) => {
        const btn = document.getElementById(id);
        if (!btn) return;
        btn.setAttribute('aria-pressed', String(isDark));
        btn.textContent = isDark ? '🌙' : '☀️';
    });
};

const initTheme = () => {
    const theme = getStoredTheme() || getSystemTheme();
    applyTheme(theme);
};

const setupThemeButtons = () => {
    themeButtons.forEach((id) => {
        const btn = document.getElementById(id);
        if (!btn) return;
        btn.addEventListener('click', () => {
            const nextTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            localStorage.setItem(themeKey, nextTheme);
            applyTheme(nextTheme);
        });
    });
};

window.addEventListener('DOMContentLoaded', () => {
    initTheme();
    setupThemeButtons();
});
