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
 * @module     mod_elediacheckin/category_filter
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    'use strict';

    return {
        /**
         * @param {String} zieleId      id of the underlying ziele <select>
         * @param {String} catsId       id of the underlying categories <select>
         * @param {Object} catZielMap   { categoryId: [ziel1, ziel2, ...], ... }
         */
        init: function(zieleId, catsId, catZielMap) {
            var ziele = document.getElementById(zieleId);
            var cats = document.getElementById(catsId);
            if (!ziele || !cats || !catZielMap) {
                return;
            }

            var applying = false;

            function apply() {
                if (applying) {
                    return;
                }
                applying = true;

                var selectedZiele = [];
                for (var i = 0; i < ziele.options.length; i++) {
                    if (ziele.options[i].selected) {
                        selectedZiele.push(ziele.options[i].value);
                    }
                }
                var showAll = selectedZiele.length === 0;
                var changed = false;

                for (var j = 0; j < cats.options.length; j++) {
                    var opt = cats.options[j];
                    var mapped = catZielMap[opt.value] || [];
                    var visible = showAll;
                    if (!visible) {
                        for (var k = 0; k < mapped.length; k++) {
                            if (selectedZiele.indexOf(mapped[k]) !== -1) {
                                visible = true;
                                break;
                            }
                        }
                    }
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
                }

                applying = false;

                if (changed) {
                    // Notify Moodle's form-autocomplete to re-read the source
                    // select and update its pill list.
                    cats.dispatchEvent(new Event('change', {bubbles: true}));
                }
            }

            ziele.addEventListener('change', apply);
            // Initial pass, deferred so form-autocomplete has finished enhancing.
            setTimeout(apply, 150);
        }
    };
});
