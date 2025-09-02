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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_vibeyourcourse
 * @copyright   2025 Alexander Mikasch
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_vibeyourcourse upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_vibeyourcourse_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Clean up vibeyourcourse_ai_interactions table - remove unnecessary fields.
    if ($oldversion < 2025090201.13) {
        
        $table = new xmldb_table('vibeyourcourse_ai_interactions');
        
        // FIRST: Remove all indexes that depend on fields we want to delete
        // This is critical - indexes must be dropped before fields
        
        // Drop the problematic index that references interaction_type
        $index = new xmldb_index('project_type', XMLDB_INDEX_NOTUNIQUE, ['project_id', 'interaction_type']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        // Also try the actual Moodle-generated index name (truncated)
        $index = new xmldb_index('m_vibeaiinte_proint_ix');
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        // Drop provider index if it exists
        $index = new xmldb_index('provider', XMLDB_INDEX_NOTUNIQUE, ['ai_provider']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        // Try the Moodle-generated provider index name
        $index = new xmldb_index('m_vibeaiinte_aiprov_ix');
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        // SECOND: Now we can safely remove the fields
        $fields_to_remove = [
            'interaction_type',
            'context_code', 
            'ai_provider',
            'ai_model',
            'tokens_used',
            'user_rating',
            'was_helpful'
        ];
        
        foreach ($fields_to_remove as $fieldname) {
            $field = new xmldb_field($fieldname);
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }
        
        // THIRD: Add new optimized index
        $index = new xmldb_index('project_time', XMLDB_INDEX_NOTUNIQUE, ['project_id', 'timecreated']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Vibeyourcourse savepoint reached.
        upgrade_mod_savepoint(true, 2025090201.13, 'vibeyourcourse');
    }

    return true;
}
