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
 * Scheduled task that synchronises content from the external Git-based repository.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\task;

use mod_elediacheckin\local\service\sync_service;

defined('MOODLE_INTERNAL') || die();

/**
 * Pulls the latest question set from the configured content repository.
 */
class sync_content extends \core\task\scheduled_task {

    /**
     * Returns the human-readable name of the task.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_sync_content', 'elediacheckin');
    }

    /**
     * Executes the synchronisation via the sync_service.
     */
    public function execute(): void {
        $service = new sync_service();
        $service->run('scheduled');
    }
}
