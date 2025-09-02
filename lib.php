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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/\>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_vibeyourcourse
 * @copyright   2025 Alexander Mikasch
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function vibeyourcourse_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the vibeyourcourse into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param stdClass $moduleinstance An object from the form.
 * @param mod_vibeyourcourse_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function vibeyourcourse_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();
    $moduleinstance->timemodified = time();

    // Process AI configuration if provided.
    if (isset($moduleinstance->ai_config)) {
        $moduleinstance->ai_config = json_encode($moduleinstance->ai_config);
    }

    // Process allowed runtimes.
    $runtimes = [];
    if (!empty($moduleinstance->allow_python)) {
        $runtimes[] = 'python';
    }
    if (!empty($moduleinstance->allow_javascript)) {
        $runtimes[] = 'javascript';
    }
    if (!empty($moduleinstance->allow_webcontainer)) {
        $runtimes[] = 'webcontainer';
    }
    $moduleinstance->allowed_runtimes = json_encode($runtimes);

    $id = $DB->insert_record('vibeyourcourse', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the vibeyourcourse in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param stdClass $moduleinstance An object from the form in mod_form.php.
 * @param mod_vibeyourcourse_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function vibeyourcourse_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    // Process AI configuration if provided.
    if (isset($moduleinstance->ai_config)) {
        $moduleinstance->ai_config = json_encode($moduleinstance->ai_config);
    }

    // Process allowed runtimes.
    $runtimes = [];
    if (!empty($moduleinstance->allow_python)) {
        $runtimes[] = 'python';
    }
    if (!empty($moduleinstance->allow_javascript)) {
        $runtimes[] = 'javascript';
    }
    if (!empty($moduleinstance->allow_webcontainer)) {
        $runtimes[] = 'webcontainer';
    }
    $moduleinstance->allowed_runtimes = json_encode($runtimes);

    return $DB->update_record('vibeyourcourse', $moduleinstance);
}

/**
 * Removes an instance of the vibeyourcourse from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function vibeyourcourse_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('vibeyourcourse', array('id' => $id));
    if (!$exists) {
        return false;
    }

    // Delete all related projects.
    $DB->delete_records('vibeyourcourse_projects', array('vibeyourcourse_id' => $id));
    
    // Delete all related executions.
    $projects = $DB->get_records('vibeyourcourse_projects', array('vibeyourcourse_id' => $id), '', 'id');
    foreach ($projects as $project) {
        $DB->delete_records('vibeyourcourse_executions', array('project_id' => $project->id));
    }

    // Delete the main instance.
    $DB->delete_records('vibeyourcourse', array('id' => $id));

    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in vibeyourcourse activities and print it out.
 *
 * @param stdClass $course The course record.
 * @param bool $viewfullnames Should we display full names.
 * @param int $timestart Print activity since this timestamp.
 * @return bool True if anything was printed, otherwise false.
 */
function vibeyourcourse_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property.
 * @param int $index the index in the $activities to use for the next record.
 * @param int $timestart append activity since this time.
 * @param int $courseid the id of the course we produce the report for.
 * @param int $cmid course module id.
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users).
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups).
 */
function vibeyourcourse_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@link vibeyourcourse_get_recent_mod_activity()}.
 *
 * @param stdClass $activity activity record with added 'cmid' property.
 * @param int $courseid the id of the course we produce the report for.
 * @param bool $detail print detailed report.
 * @param array $modnames as returned by {@link get_module_types_names()}.
 * @param bool $viewfullnames display users' full names.
 */
function vibeyourcourse_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron.
 *
 * @return bool True if successful.
 */
function vibeyourcourse_cron() {
    return true;
}

/**
 * Returns all other caps used in the module.
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array Array of capability strings.
 */
function vibeyourcourse_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of vibeyourcourse?
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given vibeyourcourse instance.
 */
function vibeyourcourse_scale_used($moduleinstanceid, $scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('vibeyourcourse', array('id' => $moduleinstanceid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of vibeyourcourse.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any vibeyourcourse instance.
 */
function vibeyourcourse_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('vibeyourcourse', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given vibeyourcourse instance.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function vibeyourcourse_grade_item_update($moduleinstance, $reset = false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $moduleinstance->grade;
        $item['grademin']  = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/vibeyourcourse', $moduleinstance->course, 'mod', 'vibeyourcourse',
            $moduleinstance->id, 0, null, $item);
}

/**
 * Delete grade item for given vibeyourcourse instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function vibeyourcourse_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/vibeyourcourse', $moduleinstance->course, 'mod', 'vibeyourcourse',
            $moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Update vibeyourcourse grades in the gradebook.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function vibeyourcourse_update_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/vibeyourcourse', $moduleinstance->course, 'mod', 'vibeyourcourse', $moduleinstance->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return array of [(string)filearea] => (string)description
 */
function vibeyourcourse_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for vibeyourcourse file areas.
 *
 * @package mod_vibeyourcourse
 * @category files
 *
 * @param file_browser $browser file browser object
 * @param array $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function vibeyourcourse_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the vibeyourcourse file areas.
 *
 * @package mod_vibeyourcourse
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the vibeyourcourse's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function vibeyourcourse_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding vibeyourcourse nodes if there is a relevant content.
 *
 * @param navigation_node $navref An object representing the navigation node.
 * @param stdClass $course Course object.
 * @param stdClass $module Module object.
 * @param cm_info $cm Course module info object.
 */
function vibeyourcourse_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the vibeyourcourse settings.
 *
 * @param settings_navigation $settingsnav The settings navigation object.
 * @param navigation_node $modulenode The node to add module settings to.
 */
function vibeyourcourse_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $modulenode = null) {
}
