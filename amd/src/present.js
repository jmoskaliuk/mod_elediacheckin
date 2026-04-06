// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Present-page (popup window) interactions for mod_elediacheckin.
 *
 * Handles the close button, the optional answer toggle, and
 * bidirectional remote control with the opener (view.js) via
 * postMessage. When the teacher navigates in either window
 * (Weiter, Zurück, Ziel), the other window follows automatically.
 *
 * @module     mod_elediacheckin/present
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** @type {string} PostMessage type for navigation sync. */
const MSG_NAVIGATE = 'elediacheckin:navigate';

/**
 * Reload the opener window so it picks up the current session question.
 * Called before closing the popup so the embedded view does not show a
 * stale card.
 */
const reloadOpener = () => {
    try {
        if (window.opener && !window.opener.closed) {
            window.opener.location.reload();
        }
    } catch (err) {
        // Cross-origin — nothing we can do.
    }
};

/**
 * Send a navigation message to the opener window.
 *
 * @param {string} presentUrl The present.php URL to navigate to.
 */
const notifyOpener = (presentUrl) => {
    try {
        if (window.opener && !window.opener.closed) {
            window.opener.postMessage({type: MSG_NAVIGATE, url: presentUrl}, '*');
        }
    } catch (err) {
        // Cross-origin or closed — ignore.
    }
};

/** @type {boolean} Guard to prevent message-loop when navigating via postMessage. */
let navigatingFromRemote = false;

export const init = (rootSelector) => {
    const root = document.querySelector(rootSelector);
    if (!root) {
        return;
    }

    const answerRegion = root.querySelector('[data-region="present-answer"]');

    root.addEventListener('click', (e) => {
        if (e.target.closest('[data-action="close-present"]')) {
            e.preventDefault();
            reloadOpener();
            window.close();
            return;
        }
        const toggle = e.target.closest('[data-action="toggle-present-answer"]');
        if (toggle && answerRegion) {
            e.preventDefault();
            answerRegion.hidden = !answerRegion.hidden;
            return;
        }

        // Navigation links (Weiter, Zurück, Ziel-Picker). Tell the
        // opener to navigate to the same card (bidirectional sync).
        const navLink = e.target.closest('a[href]');
        if (navLink && navLink.href) {
            notifyOpener(navLink.href);
        }
    });

    // Esc closes the popup.
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            reloadOpener();
            window.close();
        }
    });

    // Listen for navigation messages FROM the opener (view.js).
    // When the teacher navigates on the view page, the popup follows.
    window.addEventListener('message', (e) => {
        try {
            if (e.data && e.data.type === MSG_NAVIGATE && typeof e.data.url === 'string') {
                navigatingFromRemote = true;
                window.location.href = e.data.url;
            }
        } catch (err) {
            // Malformed message — ignore.
        }
    });
};
