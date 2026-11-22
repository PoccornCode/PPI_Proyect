/*Gestor de Consentimiento de Cookies - Gavel.net 
almacena la elección en localStorage para evitar reaparición molesta
y permite volver a abrir el banner si el usuario desea cambiar su preferencia.*/
(function () {
    'use strict';

    const STORAGE_KEY = 'gavel_cookie_consent';
    const banner = document.getElementById('gavel-cookie-banner');
    const btnAcceptAll = document.getElementById('btn-cookie-accept-all');
    const btnEssential = document.getElementById('btn-cookie-essential');

    if (!banner) return;

    function getConsent() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function setConsent(choice) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                status: choice,
                timestamp: new Date().toISOString()
            }));
        } catch (e) {
        }
        hideBanner();
    }

    function showBanner() {
        banner.style.display = 'block';
        setTimeout(() => {
            banner.classList.add('gavel-cookie-banner--visible');
        }, 150);
    }

    function hideBanner() {
        banner.classList.remove('gavel-cookie-banner--visible');
        setTimeout(() => {
            banner.style.display = 'none';
        }, 300);
    }

    const existingConsent = getConsent();
    if (!existingConsent) {
        setTimeout(showBanner, 600);
    }

    if (btnAcceptAll) {
        btnAcceptAll.addEventListener('click', function () {
            setConsent('all');
        });
    }

    if (btnEssential) {
        btnEssential.addEventListener('click', function () {
            setConsent('essential');
        });
    }

    window.resetCookieConsent = function () {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (e) { }
        showBanner();
    };
})();
