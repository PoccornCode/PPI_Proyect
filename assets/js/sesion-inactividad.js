(function () {
    'use strict';

    const config = window.GAVEL_SESSION_CONFIG;
    if (!config) return;

    const TIMEOUT_MS = Math.max(10, Number(config.timeoutSeconds) || 900) * 1000;
    const WARNING_BEFORE_MS = Math.min(
        TIMEOUT_MS - 5000,
        Math.max(5, Number(config.warningBeforeSeconds) || 120) * 1000
    );

    const STORAGE_KEY_ACTIVITY = 'gavel_last_activity_ts';
    const STORAGE_KEY_LOGOUT = 'gavel_session_logout_ts';
    const ACTIVITY_THROTTLE_MS = 2000;
    const SERVER_PING_INTERVAL_MS = 60000;

    const modalEl = document.getElementById('modalSessionTimeout');
    const countdownEl = document.getElementById('sessionTimeoutCountdown');
    const progressEl = document.getElementById('sessionTimeoutProgress');
    const btnExtender = document.getElementById('btnExtenderSesion');
    const btnCerrar = document.getElementById('btnCerrarSesionInactividad');

    if (!modalEl || !countdownEl || !btnExtender || !btnCerrar) return;

    let bsModal = null;
    let modalVisible = false;
    let isExtending = false;
    let isLoggingOut = false;
    let lastActivityTs = Date.now();
    let lastThrottleTs = 0;
    let lastServerPingTs = Date.now();

    function getModalInstance() {
        if (!bsModal && window.bootstrap && window.bootstrap.Modal) {
            bsModal =
                window.bootstrap.Modal.getInstance(modalEl) ||
                new window.bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
        }
        return bsModal;
    }

    function formatTime(ms) {
        const totalSeconds = Math.max(0, Math.ceil(ms / 1000));
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }

    function setLastActivity(ts, broadcast) {
        lastActivityTs = ts;
        if (broadcast) {
            try {
                localStorage.setItem(STORAGE_KEY_ACTIVITY, String(ts));
            } catch (e) {
            }
        }
    }

    function showWarningModal() {
        if (modalVisible) return;
        const instance = getModalInstance();
        if (instance) {
            instance.show();
            modalVisible = true;
        }
    }

    function hideWarningModal() {
        if (!modalVisible) return;
        const instance = getModalInstance();
        if (instance) {
            instance.hide();
        }
        modalVisible = false;
    }

    function triggerLogout(broadcast) {
        if (isLoggingOut) return;
        isLoggingOut = true;

        if (broadcast) {
            try {
                localStorage.setItem(STORAGE_KEY_LOGOUT, String(Date.now()));
            } catch (e) {
            }
        }

        window.location.href = config.logoutUrl;
    }

    async function pingServer() {
        const response = await fetch(config.keepaliveUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.status === 401) {
            triggerLogout(true);
            return false;
        }

        const data = await response.json().catch(() => ({ ok: false }));
        if (!data.ok) {
            if (data.expired) {
                triggerLogout(true);
            }
            return false;
        }

        lastServerPingTs = Date.now();
        return true;
    }

    function registerUserActivity() {
        if (modalVisible || isLoggingOut) return;

        const now = Date.now();
        if (now - lastThrottleTs < ACTIVITY_THROTTLE_MS) return;
        lastThrottleTs = now;

        setLastActivity(now, true);

        if (now - lastServerPingTs >= SERVER_PING_INTERVAL_MS) {
            lastServerPingTs = now;
            pingServer().catch(() => { });
        }
    }

    function tick() {
        if (isLoggingOut) return;

        const now = Date.now();
        const remaining = TIMEOUT_MS - (now - lastActivityTs);

        if (remaining <= 0) {
            countdownEl.textContent = '00:00';
            if (progressEl) {
                progressEl.style.width = '0%';
                progressEl.setAttribute('aria-valuenow', '0');
            }
            triggerLogout(true);
            return;
        }

        if (remaining <= WARNING_BEFORE_MS) {
            showWarningModal();
            countdownEl.textContent = formatTime(remaining);

            if (progressEl) {
                const pct = Math.max(0, Math.min(100, (remaining / WARNING_BEFORE_MS) * 100));
                progressEl.style.width = pct.toFixed(1) + '%';
                progressEl.setAttribute('aria-valuenow', String(Math.round(pct)));
            }
        } else if (modalVisible) {
            hideWarningModal();
        }
    }

    btnExtender.addEventListener('click', async function () {
        if (isExtending || isLoggingOut) return;
        isExtending = true;

        const originalHtml = btnExtender.innerHTML;
        btnExtender.disabled = true;
        btnExtender.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Extendiendo...';

        try {
            const ok = await pingServer();
            if (ok) {
                setLastActivity(Date.now(), true);
                hideWarningModal();
            }
        } catch (e) {
        } finally {
            btnExtender.disabled = false;
            btnExtender.innerHTML = originalHtml;
            isExtending = false;
        }
    });

    btnCerrar.addEventListener('click', function () {
        triggerLogout(true);
    });

    ['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach(function (evt) {
        window.addEventListener(evt, registerUserActivity, { passive: true });
    });

    // Sincronizar actividad y cierre de sesión entre pestañas abiertas
    window.addEventListener('storage', function (event) {
        if (event.key === STORAGE_KEY_ACTIVITY && event.newValue) {
            const syncedTs = Number(event.newValue);
            if (!Number.isNaN(syncedTs) && syncedTs > lastActivityTs) {
                lastActivityTs = syncedTs;
                if (modalVisible && (TIMEOUT_MS - (Date.now() - lastActivityTs)) > WARNING_BEFORE_MS) {
                    hideWarningModal();
                }
            }
        } else if (event.key === STORAGE_KEY_LOGOUT && event.newValue) {
            triggerLogout(false);
        }
    });

    // Si la pestaña estuvo suspendida, recalculamos al volver
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            tick();
        }
    });

    setLastActivity(Date.now(), true);
    setInterval(tick, 1000);
})();
