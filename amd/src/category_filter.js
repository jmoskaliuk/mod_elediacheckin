// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Dynamic category filter for the mod_elediacheckin activity form.
 *
 * Watches the "Ziel" autocomplete and hides/disables options in the
 * "Kategorien" autocomplete whose category does not belong to any of the
 * currently selected ziele. Empty ziele selection = show all categories.
 *
 * The category→ziel map is read from a hidden <script type="application/json">
 * element in the DOM rather than passed as an AMD argument, because Moodle
 * warns when js_call_amd arguments exceed 1024 characters.
 *
 * @module     mod_elediacheckin/category_filter
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialise the category filter on the activity form.
 *
 * @param {String} zieleId  id of the underlying ziele <select>
 * @param {String} catsId   id of the underlying categories <select>
 * @param {String} mapId    id of the <script type="application/json"> element
 *                          holding the category→ziel map
 */
export const init = (zieleId, catsId, mapId) => {
    const ziele = document.getElementById(zieleId);
    const cats = document.getElementById(catsId);
    const mapNode = document.getElementById(mapId);
    if (!ziele || !cats || !mapNode) {
        return;
    }

    let catZielMap;
    try {
        catZielMap = JSON.parse(mapNode.textContent || '{}');
    } catch (e) {
        return;
    }

    let applying = false;

    /**
     * Re-evaluate which category options are visible/disabled based on the
     * currently selected ziele.
     */
    const apply = () => {
        if (applying) {
            return;
        }
        applying = true;

        const selectedZiele = Array.from(ziele.options)
            .filter(o => o.selected)
            .map(o => o.value);
        const showAll = selectedZiele.length === 0;
        let changed = false;

        Array.from(cats.options).forEach(opt => {
            const mapped = catZielMap[opt.value] || [];
            const visible = showAll || mapped.some(z => selectedZiele.includes(z));

            if (opt.disabled === visible) {
                opt.disabled = !visible;
                changed = true;
            }
            if (opt.hidden !== !visible) {
                opt.hidden = !visible;
                changed = true;
            }
            if (!visible && opt.selected) {
                opt.selected = false;
                changed = true;
            }
        });

        applying = false;

        if (changed) {
            // Notify Moodle's form-autocomplete to re-read the source
            // select and update its pill list.
            cats.dispatchEvent(new Event('change', {bubbles: true}));
        }
    };

    ziele.addEventListener('change', apply);
    setTimeout(apply, 150);
};
