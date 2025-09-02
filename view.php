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
 * Prints an instance of mod_vibeyourcourse.
 *
 * @package     mod_vibeyourcourse
 * @copyright   2025 Alexander Mikasch
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->libdir.'/completionlib.php');

// Course_module ID.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$v = optional_param('v', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('vibeyourcourse', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('vibeyourcourse', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($v) {
    $moduleinstance = $DB->get_record('vibeyourcourse', array('id' => $v), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('vibeyourcourse', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('missingidandcmid', 'mod_vibeyourcourse');
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

// Check capability.
require_capability('mod/vibeyourcourse:view', $modulecontext);

// Trigger course_module_viewed event.
$event = \mod_vibeyourcourse\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot($cm->modname, $moduleinstance);
$event->trigger();

// Complete the activity.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/vibeyourcourse/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

// Set Cross-Origin-Isolation headers for WebContainer
header('Cross-Origin-Embedder-Policy: require-corp');
header('Cross-Origin-Opener-Policy: same-origin');

// Load jQuery
$PAGE->requires->jquery();

echo $OUTPUT->header();

// Display the activity name and introduction.
echo $OUTPUT->heading($moduleinstance->name);

if ($moduleinstance->intro) {
    echo $OUTPUT->box(format_module_intro('vibeyourcourse', $moduleinstance, $cm->id), 'generalbox mod_introbox', 'vibeyourcourseintro');
}

// Get user's existing projects.
$user_projects = $DB->get_records('vibeyourcourse_projects', 
    array('vibeyourcourse_id' => $moduleinstance->id, 'userid' => $USER->id), 
    'timemodified DESC'
);

// Decode allowed runtimes.
$allowed_runtimes = json_decode($moduleinstance->allowed_runtimes, true);
if (!$allowed_runtimes) {
    $allowed_runtimes = ['python', 'javascript']; // Default fallback.
}

// Pass data to React app
$projects_data = [];
foreach ($user_projects as $project) {
    $projects_data[] = [
        'id' => $project->id,
        'project_name' => $project->project_name,
        'runtime_type' => $project->runtime_type,
        'completion_percentage' => $project->completion_percentage,
        'status' => $project->status
    ];
}

// Include React bundle CSS
echo '<link rel="stylesheet" href="' . new moodle_url('/mod/vibeyourcourse/build/bundle.css') . '">';

// React app container
echo '<div id="vibeyourcourse-react-app"></div>';
?>

<script>
// Configuration passed to React app
window.vibeyourcourseConfig = {
    cmid: <?php echo $cm->id; ?>,
    moduleInstanceId: <?php echo $moduleinstance->id; ?>,
    allowedRuntimes: <?php echo json_encode($allowed_runtimes); ?>,
    aiAssistanceLevel: '<?php echo $moduleinstance->ai_assistance_level ?: 'hints'; ?>',
    difficultyLevel: '<?php echo $moduleinstance->difficulty_level ?: 'adaptive'; ?>',
    projectTemplates: <?php echo $moduleinstance->project_templates ?: '{}'; ?>,
    userId: <?php echo $USER->id; ?>,
    sesskey: '<?php echo sesskey(); ?>',
    projects: <?php echo json_encode($projects_data); ?>
};

console.log('Vibe Your Course React Configuration:', window.vibeyourcourseConfig);
</script>

<!-- Load React bundle -->
<script type="module" src="<?php echo new moodle_url('/mod/vibeyourcourse/build/bundle.js'); ?>"></script>


<?php

echo $OUTPUT->footer();
