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
 *
 * "Nächste Frage" is a plain <a> link and needs no JS.
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

export const init = (rootSelector) => {
    const root = document.querySelector(rootSelector);
    if (!root) {
        return;
    }

    const fullscreen = document.querySelector('[data-region="elediacheckin-fullscreen"]');

    const openFullscreen = () => {
        if (!fullscreen) {
            return;
        }
        fullscreen.classList.add('is-open');
        fullscreen.setAttribute('aria-hidden', 'false');
        document.body.classList.add('elediacheckin-fs-locked');
    };

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
    });

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
            }
        });
    }

    // Esc closes fullscreen.
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && fullscreen && fullscreen.classList.contains('is-open')) {
            closeFullscreen();
        }
    });
};
