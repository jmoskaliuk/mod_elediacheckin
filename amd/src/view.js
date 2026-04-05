// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * View-page interactions for mod_elediacheckin.
 *
 * Currently handles the "Another question" button as a full page reload.
 * Will be replaced with an AJAX call once the web service is implemented.
 *
 * @module     mod_elediacheckin/view
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = (rootSelector) => {
    const root = document.querySelector(rootSelector);
    if (!root) {
        return;
    }

    root.addEventListener('click', (e) => {
        const target = e.target.closest('[data-action="new-question"]');
        if (!target) {
            return;
        }
        // MVP: simple reload. Replace with Ajax.call([...]) once the external function is ready.
        window.location.reload();
    });
};
