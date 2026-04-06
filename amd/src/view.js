// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * View-page interactions for mod_elediacheckin.
 *
 * Handles:
 *  - Popup launcher  → window.open('present.php?layout=popup', …)
 *  - Fullscreen launcher → show overlay + lock scroll
 *  - Fullscreen close button and Esc key
 *  - Fullscreen answer toggle
 *  - Bidirectional remote control: view ↔ popup via postMessage.
 *
 * "Nächste Frage" is a plain <a> link and needs no JS — but when a
 * popup is open, the click is intercepted so we can tell the popup
 * to navigate to the same card.
 *
 * @module     mod_elediacheckin/view
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Firefox ≥ 109 and modern WebKit require the explicit `popup=yes` feature to
// honour `width`/`height` and actually open a chrome-less popup window instead
// of a normal tab. Without it, Firefox silently upgrades the request to a new
// tab, which breaks the "present mode on a second screen" workflow. Chromium
// tolerates both forms, so we always send `popup=yes`.
const POPUP_FEATURES = 'popup=yes,width=1100,height=720,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes';

/** @type {string} PostMessage type for navigation sync. */
const MSG_NAVIGATE = 'elediacheckin:navigate';

export const init = (rootSelector) => {
    const root = document.querySelector(rootSelector);
    if (!root) {
        return;
    }

    /** @type {Window|null} Reference to the popup window (if open). */
    let popupWin = null;

    const fullscreen = document.querySelector('[data-region="elediacheckin-fullscreen"]');

    /** Open the fullscreen overlay. */
    const openFullscreen = () => {
        if (!fullscreen) {
            return;
        }
        fullscreen.classList.add('is-open');
        fullscreen.setAttribute('aria-hidden', 'false');
        document.body.classList.add('elediacheckin-fs-locked');
    };

    /** Close the fullscreen overlay. */
    const closeFullscreen = () => {
        if (!fullscreen) {
            return;
        }
        fullscreen.classList.remove('is-open');
        fullscreen.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('elediacheckin-fs-locked');
    };

    /**
     * Convert a view.php URL to the equivalent present.php URL so the
     * popup navigates to the matching card.
     *
     * @param {string} viewHref A view.php URL.
     * @return {string} The equivalent present.php URL with layout=popup.
     */
    const viewUrlToPresent = (viewHref) => {
        try {
            const u = new URL(viewHref, window.location.href);
            u.pathname = u.pathname.replace(/\/view\.php$/, '/present.php');
            u.searchParams.set('layout', 'popup');
            u.searchParams.delete('fs');
            return u.toString();
        } catch (err) {
            return viewHref;
        }
    };

    /**
     * Send a navigation message to the popup window (if open).
     *
     * @param {string} presentUrl The present.php URL the popup should navigate to.
     */
    const notifyPopup = (presentUrl) => {
        try {
            if (popupWin && !popupWin.closed) {
                popupWin.postMessage({type: MSG_NAVIGATE, url: presentUrl}, '*');
            }
        } catch (err) {
            // Cross-origin or closed — ignore.
        }
    };

    // Launchers on the card.
    root.addEventListener('click', (e) => {
        const popupBtn = e.target.closest('[data-action="open-popup"]');
        if (popupBtn) {
            e.preventDefault();
            const url = popupBtn.dataset.url;
            if (url) {
                popupWin = window.open(url, 'elediacheckin_present', POPUP_FEATURES);
            }
            return;
        }

        const fsBtn = e.target.closest('[data-action="open-fullscreen"]');
        if (fsBtn) {
            e.preventDefault();
            openFullscreen();
            return;
        }

        // Navigation links (Weiter, Zurück, Ziel-Picker) on the embedded
        // card (outside fullscreen). If a popup is open, also tell it to
        // navigate to the same card.
        const navLink = e.target.closest('a[href]');
        if (navLink && navLink.href && popupWin && !popupWin.closed) {
            notifyPopup(viewUrlToPresent(navLink.href));
        }
    });

    // Helper: stamp `fs=1` onto a URL so the next page load re-opens the
    // fullscreen overlay.
    const addFsFlag = (href) => {
        try {
            const u = new URL(href, window.location.href);
            u.searchParams.set('fs', '1');
            return u.toString();
        } catch (err) {
            return href + (href.indexOf('?') >= 0 ? '&' : '?') + 'fs=1';
        }
    };

    // Fullscreen overlay interactions.
    if (fullscreen) {
        fullscreen.addEventListener('click', (e) => {
            if (e.target.closest('[data-action="close-fullscreen"]')) {
                e.preventDefault();
                closeFullscreen();
                return;
            }
            const toggle = e.target.closest('[data-action="toggle-fs-answer"]');
            if (toggle) {
                e.preventDefault();
                const ans = fullscreen.querySelector('[data-region="fs-answer-text"]');
                if (ans) {
                    ans.hidden = !ans.hidden;
                }
                return;
            }
            // Any <a> inside the fullscreen overlay: stamp fs=1 AND sync
            // to popup if open.
            const nav = e.target.closest('a[href]');
            if (nav) {
                if (!nav.dataset.fsStamped) {
                    nav.dataset.fsStamped = '1';
                    nav.href = addFsFlag(nav.href);
                }
                if (popupWin && !popupWin.closed) {
                    notifyPopup(viewUrlToPresent(nav.href));
                }
            }
        });
    }

    // If the current URL carries fs=1, auto-open the overlay immediately.
    try {
        const params = new URLSearchParams(window.location.search);
        if (params.get('fs') === '1') {
            openFullscreen();
        }
    } catch (err) {
        // Swallow defensively.
    }

    // Esc closes fullscreen.
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && fullscreen && fullscreen.classList.contains('is-open')) {
            closeFullscreen();
        }
    });

    // Listen for messages FROM the popup (bidirectional sync).
    // - 'elediacheckin:popup-ready' → popup just loaded, store its reference.
    // - MSG_NAVIGATE → popup navigated, view should follow.
    window.addEventListener('message', (e) => {
        try {
            // Re-acquire popup reference from any popup message.
            // After the view reloads (e.g. Weiter click), popupWin is null.
            // The popup announces itself on load so we can reconnect.
            if (e.data && e.source && e.source !== window) {
                if (e.data.type === 'elediacheckin:popup-ready' || e.data.type === MSG_NAVIGATE) {
                    popupWin = e.source;
                }
            }

            if (e.data && e.data.type === MSG_NAVIGATE && typeof e.data.url === 'string') {
                // Convert present.php URL → view.php URL.
                const u = new URL(e.data.url, window.location.href);
                u.pathname = u.pathname.replace(/\/present\.php$/, '/view.php');
                u.searchParams.delete('layout');
                // Preserve fullscreen state if currently open.
                if (fullscreen && fullscreen.classList.contains('is-open')) {
                    u.searchParams.set('fs', '1');
                }
                window.location.href = u.toString();
            }
        } catch (err) {
            // Malformed message — ignore.
        }
    });
};
