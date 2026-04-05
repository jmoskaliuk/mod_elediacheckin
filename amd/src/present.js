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

export const init = (rootSelector) => {
    const root = document.querySelector(rootSelector);
    if (!root) {
        return;
    }

    const answerRegion = root.querySelector('[data-region="present-answer"]');

    root.addEventListener('click', (e) => {
        if (e.target.closest('[data-action="close-present"]')) {
            e.preventDefault();
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
            window.close();
        }
    });
};
