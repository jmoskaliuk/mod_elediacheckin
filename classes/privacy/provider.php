<?php
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
 * Privacy API provider for mod_elediacheckin.
 *
 * The plugin does not store learner answers. It does store the activity
 * configuration entered by a teacher (name, intro, own questions); those
 * are the only fields declared here. The bundle table (elediacheckin_question)
 * holds repository-content synced from an external source — not personal
 * data — and the sync log holds admin-side telemetry, so neither is declared.
 *
 * Per-user navigation state (`$SESSION->elediacheckin_nav`) holds the list
 * of externalids the learner has seen this session — it lives in the session
 * only, is wiped on logout/session expiry, never written to the database, and
 * therefore intentionally not declared via the Privacy API.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\privacy;

use core_privacy\local\metadata\collection;

/**
 * Privacy metadata provider.
 */
class provider implements \core_privacy\local\metadata\provider {
    /**
     * Returns metadata about stored plugin data.
     *
     * @param collection $collection The metadata collection to add to.
     * @return collection Updated metadata collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'elediacheckin',
            [
                'name' => 'privacy:metadata:elediacheckin:name',
                'intro' => 'privacy:metadata:elediacheckin:intro',
                'ownquestions' => 'privacy:metadata:elediacheckin:ownquestions',
            ],
            'privacy:metadata:elediacheckin'
        );

        return $collection;
    }
}
