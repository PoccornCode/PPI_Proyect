(function () {
    'use strict';

    var STORAGE_KEY = 'gavel_theme';
    var DEFAULT_THEME = 'light';

    function getStoredTheme() {
        try {
            var saved = localStorage.getItem(STORAGE_KEY);
            if (saved === 'light' || saved === 'dark') {
                return saved;
            }
        } catch (e) {
            // Ignorar si localStorage no está disponible
        }
        return DEFAULT_THEME;
    }

    function applyTheme(theme) {
        var resolved = theme === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', resolved);

        var buttons = document.querySelectorAll('[data-theme-toggle]');
        buttons.forEach(function (btn) {
            var isLight = resolved === 'light';
            btn.setAttribute('aria-pressed', String(isLight));
            btn.setAttribute('title', isLight ? 'Cambiar a tema oscuro' : 'Cambiar a tema claro');
            btn.setAttribute('aria-label', isLight ? 'Cambiar a tema oscuro' : 'Cambiar a tema claro');

            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = isLight ? 'fas fa-moon' : 'fas fa-sun';
            }
        });
    }

    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-theme') || getStoredTheme();
        var next = current === 'light' ? 'dark' : 'light';
        try {
            localStorage.setItem(STORAGE_KEY, next);
        } catch (e) {
            // Ignorar
        }
        applyTheme(next);
    }

    // Aplicar inmediatamente
    applyTheme(getStoredTheme());

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getStoredTheme());

        var buttons = document.querySelectorAll('[data-theme-toggle]');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', toggleTheme);
        });
    });

    window.gavelTheme = {
        get: getStoredTheme,
        set: function (theme) {
            try {
                localStorage.setItem(STORAGE_KEY, theme === 'dark' ? 'dark' : 'light');
            } catch (e) { }
            applyTheme(theme);
        },
        toggle: toggleTheme
    };
})();
