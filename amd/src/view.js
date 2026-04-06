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
 *  - Bidirectional remote control: view ↔ popup via BroadcastChannel.
 *
 * Navigation sync uses BroadcastChannel so that each window announces
 * its current question externalid after loading. The other window then
 * navigates to ?q=<externalid> to show the exact same card, avoiding
 * independent random draws.
 *
 * @module     mod_elediacheckin/view
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Firefox ≥ 109 and modern WebKit require the explicit popup=yes feature.
const POPUP_FEATURES = 'popup=yes,width=1100,height=720,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes';

export const init = (rootSelector) => {
    const root = document.querySelector(rootSelector);
    if (!root) {
        return;
    }

    const cmid = root.dataset.cmid || '';
    const myExternalId = root.dataset.externalid || '';
    const fullscreen = document.querySelector('[data-region="elediacheckin-fullscreen"]');

    // Read activeziel from the current URL so sync messages carry it.
    let myActiveziel = '';
    try {
        myActiveziel = new URLSearchParams(window.location.search).get('activeziel') || '';
    } catch (err) {
        // Swallow.
    }

    // BroadcastChannel for question sync between view and popup.
    // Each window announces its externalid after loading; the other
    // navigates to ?q=<id> so both show the same card.
    let bc = null;
    try {
        bc = new BroadcastChannel('elediacheckin_sync_' + cmid);
    } catch (err) {
        // BroadcastChannel not supported — sync disabled.
    }

    if (bc && myExternalId) {
        // Announce current question to any open popup.
        bc.postMessage({from: 'view', externalid: myExternalId, activeziel: myActiveziel});

        // Listen for popup question announcements.
        bc.onmessage = (e) => {
            if (e.data && e.data.from === 'present' && e.data.externalid
                    && e.data.externalid !== myExternalId) {
                // Popup has a different question — navigate to match it.
                const u = new URL(window.location.href);
                u.searchParams.set('q', e.data.externalid);
                if (e.data.activeziel) {
                    u.searchParams.set('activeziel', e.data.activeziel);
                }
                u.searchParams.delete('next');
                u.searchParams.delete('prev');
                u.searchParams.delete('r');
                if (fullscreen && fullscreen.classList.contains('is-open')) {
                    u.searchParams.set('fs', '1');
                }
                window.location.href = u.toString();
            }
        };
    }

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

    // Launchers on the card.
    root.addEventListener('click', (e) => {
        const popupBtn = e.target.closest('[data-action="open-popup"]');
        if (popupBtn) {
            e.preventDefault();
            const url = popupBtn.dataset.url;
            if (url) {
                window.open(url, 'elediacheckin_present', POPUP_FEATURES);
            }
            return;
        }

        const fsBtn = e.target.closest('[data-action="open-fullscreen"]');
        if (fsBtn) {
            e.preventDefault();
            openFullscreen();
        }
        // Navigation links (Weiter, Zurück, Ziel-Picker) proceed as normal
        // <a> links. After the new page loads, BroadcastChannel sync will
        // update the popup automatically.
    });

    // Helper: stamp fs=1 onto a URL for fullscreen persistence.
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
            // Any <a> inside the fullscreen overlay: stamp fs=1.
            const nav = e.target.closest('a[href]');
            if (nav && !nav.dataset.fsStamped) {
                nav.dataset.fsStamped = '1';
                nav.href = addFsFlag(nav.href);
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
};
