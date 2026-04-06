// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Present-page (popup window) interactions for mod_elediacheckin.
 *
 * Handles the close button and the optional answer toggle inside the
 * stand-alone present.php view loaded via window.open.
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
        }
    });

    // Esc closes the popup.
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            reloadOpener();
            window.close();
        }
    });
};
