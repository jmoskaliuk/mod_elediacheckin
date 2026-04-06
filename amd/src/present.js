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
 * bidirectional remote control with the view page via
 * BroadcastChannel. When the teacher navigates in either window
 * (Weiter, Zurück, Ziel), the other window follows automatically
 * by syncing the current question externalid.
 *
 * @module     mod_elediacheckin/present
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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

export const init = (rootSelector) => {
    const root = document.querySelector(rootSelector);
    if (!root) {
        return;
    }

    const cmid = root.dataset.cmid || '';
    const myExternalId = root.dataset.externalid || '';
    const answerRegion = root.querySelector('[data-region="present-answer"]');

    // BroadcastChannel for question sync between popup and view.
    let bc = null;
    try {
        bc = new BroadcastChannel('elediacheckin_sync_' + cmid);
    } catch (err) {
        // BroadcastChannel not supported — sync disabled.
    }

    if (bc && myExternalId) {
        // Announce current question to the view page.
        bc.postMessage({from: 'present', externalid: myExternalId});

        // Listen for view question announcements.
        bc.onmessage = (e) => {
            if (e.data && e.data.from === 'view' && e.data.externalid
                    && e.data.externalid !== myExternalId) {
                // View has a different question — navigate to match it.
                const u = new URL(window.location.href);
                u.searchParams.set('q', e.data.externalid);
                if (e.data.activeziel) {
                    u.searchParams.set('activeziel', e.data.activeziel);
                }
                u.searchParams.delete('next');
                u.searchParams.delete('prev');
                u.searchParams.delete('r');
                window.location.href = u.toString();
            }
        };
    }

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
        }
        // Navigation links (Weiter, Zurück, Ziel-Picker) proceed as normal
        // <a> links. After the new page loads, BroadcastChannel sync will
        // update the view automatically.
    });

    // Esc closes the popup.
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            reloadOpener();
            window.close();
        }
    });
};
