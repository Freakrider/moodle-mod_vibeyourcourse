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

// Create the main interface.
?>

<div id="vibeyourcourse-app" class="vibeyourcourse-container">
    
    <!-- Header Section -->
    <div class="vibeyourcourse-header">
        <div class="row align-items-center">
            <div class="col-md-2">
                <div class="vibeyourcourse-brand">
                    <?php
                    $logourl = new moodle_url('/mod/vibeyourcourse/pix/monologo.png');
                    echo html_writer::img($logourl, 'VibeCoding Logo', array('class' => 'vibeyourcourse-logo'));
                    ?>
                </div>
            </div>
            <div class="col-md-4">
                <h3><?php echo get_string('myprojects', 'mod_vibeyourcourse'); ?></h3>
            </div>
            <div class="col-md-6 text-right">
                <button id="new-project-btn" class="btn btn-primary">
                    <i class="fa fa-plus"></i> <?php echo get_string('newproject', 'mod_vibeyourcourse'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Projects Overview -->
    <div class="vibeyourcourse-projects">
        <?php if (empty($user_projects)): ?>
            <div class="empty-state text-center p-4">
                <i class="fa fa-code fa-3x text-muted mb-3"></i>
                <h4><?php echo get_string('startcoding', 'mod_vibeyourcourse'); ?></h4>
                <p class="text-muted"><?php echo get_string('help_gettingstarted', 'mod_vibeyourcourse'); ?></p>
                <button class="btn btn-primary btn-lg" id="empty-state-new-project">
                    <?php echo get_string('newproject', 'mod_vibeyourcourse'); ?>
                </button>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($user_projects as $project): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card project-card" data-project-id="<?php echo $project->id; ?>">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($project->project_name); ?></h5>
                                <small class="text-muted"><?php echo ucfirst($project->runtime_type); ?></small>
                            </div>
                            <div class="card-body">
                                <div class="progress mb-2">
                                    <div class="progress-bar" style="width: <?php echo $project->completion_percentage; ?>%"></div>
                                </div>
                                <small class="text-muted">
                                    <?php echo $project->completion_percentage; ?>% <?php echo get_string('progress', 'mod_vibeyourcourse'); ?>
                                </small>
                                <p class="card-text mt-2">
                                    <span class="badge badge-<?php echo $project->status == 'completed' ? 'success' : 'secondary'; ?>">
                                        <?php echo get_string('status_' . $project->status, 'mod_vibeyourcourse'); ?>
                                    </span>
                                </p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-sm btn-outline-primary" data-action="open-project" data-project-id="<?php echo $project->id; ?>">
                                    <?php echo get_string('codeeditor', 'mod_vibeyourcourse'); ?>
                                </button>
                                <?php if ($project->status == 'completed'): ?>
                                    <button class="btn btn-sm btn-success ml-1" data-action="view-project" data-project-id="<?php echo $project->id; ?>">
                                        <?php echo get_string('preview', 'mod_vibeyourcourse'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- IDE Container (Hidden by default) -->
    <div id="ide-container" class="vibeyourcourse-ide d-none">
        <div class="ide-header">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="vibeyourcourse-brand">
                        <?php
                        $logourl = new moodle_url('/mod/vibeyourcourse/pix/monologo.png');
                        echo html_writer::img($logourl, 'VibeCoding Logo', array('class' => 'vibeyourcourse-logo vibeyourcourse-logo-small'));
                        ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <h4 id="current-project-name">Project Name</h4>
                </div>
                <div class="col-md-6 text-right">
                    <button class="btn btn-sm btn-outline-secondary" id="save-project-btn">
                        <?php echo get_string('saveproject', 'mod_vibeyourcourse'); ?>
                    </button>
                    <button class="btn btn-sm btn-primary ml-1" id="run-code-btn">
                        <?php echo get_string('runcode', 'mod_vibeyourcourse'); ?>
                    </button>
                    <button class="btn btn-sm btn-secondary ml-1" id="close-ide-btn">
                        <?php echo get_string('backtocourse', 'mod_vibeyourcourse'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="ide-body">
            <div class="row h-100">
                <!-- File Explorer -->
                <div class="col-md-2 ide-sidebar">
                    <h6><?php echo get_string('files', 'mod_vibeyourcourse'); ?></h6>
                    <div id="file-explorer">
                        <!-- Files will be loaded here -->
                    </div>
                </div>

                <!-- Code Editor -->
                <div class="col-md-6 ide-editor">
                    <div id="code-editor" style="height: 400px; border: 1px solid #ddd;">
                        <!-- CodeMirror will be initialized here -->
                    </div>
                </div>

                <!-- Output/Preview -->
                <div class="col-md-4 ide-output">
                    <ul class="nav nav-tabs" id="output-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" id="console-tab" data-toggle="tab" href="#console-pane">
                                <?php echo get_string('console', 'mod_vibeyourcourse'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="preview-tab" data-toggle="tab" href="#preview-pane">
                                <?php echo get_string('preview', 'mod_vibeyourcourse'); ?>
                            </a>
                        </li>
                        <?php if ($moduleinstance->ai_assistance_level != 'none'): ?>
                        <li class="nav-item">
                            <a class="nav-link" id="ai-tab" data-toggle="tab" href="#ai-pane">
                                <?php echo get_string('aiassistant', 'mod_vibeyourcourse'); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <div class="tab-content" id="output-content">
                        <div class="tab-pane fade show active" id="console-pane">
                            <div id="console-output" class="console"></div>
                        </div>
                        <div class="tab-pane fade" id="preview-pane">
                            <iframe id="preview-frame" class="w-100 h-100"></iframe>
                        </div>
                        <?php if ($moduleinstance->ai_assistance_level != 'none'): ?>
                        <div class="tab-pane fade" id="ai-pane">
                            <div id="ai-chat" class="ai-assistant">
                                <div id="ai-messages"></div>
                                <div class="ai-input-container">
                                    <input type="text" id="ai-input" class="form-control" placeholder="Ask AI for help...">
                                    <button class="btn btn-primary" id="send-ai-message">Send</button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Project Modal -->
<div class="modal fade" id="new-project-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo get_string('newproject', 'mod_vibeyourcourse'); ?></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="new-project-form">
                    <div class="form-group">
                        <label for="project-name"><?php echo get_string('projectname', 'mod_vibeyourcourse'); ?></label>
                        <input type="text" class="form-control" id="project-name" required>
                    </div>
                    <div class="form-group">
                        <label for="project-runtime"><?php echo get_string('selectruntime', 'mod_vibeyourcourse'); ?></label>
                        <select class="form-control" id="project-runtime" required>
                            <?php foreach ($allowed_runtimes as $runtime): ?>
                                <option value="<?php echo $runtime; ?>"><?php echo get_string($runtime, 'mod_vibeyourcourse'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="project-template"><?php echo get_string('selecttemplate', 'mod_vibeyourcourse'); ?></label>
                        <select class="form-control" id="project-template">
                            <option value="">Blank Project</option>
                            <!-- Templates will be loaded via JavaScript -->
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="create-project-btn">Create Project</button>
            </div>
        </div>
    </div>
</div>

<script>
// Configuration passed to JavaScript
window.vibeyourcourseConfig = {
    cmid: <?php echo $cm->id; ?>,
    moduleInstanceId: <?php echo $moduleinstance->id; ?>,
    allowedRuntimes: <?php echo json_encode($allowed_runtimes); ?>,
    aiAssistanceLevel: '<?php echo $moduleinstance->ai_assistance_level; ?>',
    difficultyLevel: '<?php echo $moduleinstance->difficulty_level; ?>',
    projectTemplates: <?php echo $moduleinstance->project_templates ?: '{}'; ?>,
    userId: <?php echo $USER->id; ?>,
    sesskey: '<?php echo sesskey(); ?>'
};

// Basic JavaScript functions (will be expanded)
function createNewProject() {
    // Show the modal using native JavaScript
    var modal = document.getElementById('new-project-modal');
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
    }
}

function createProject() {
    // Implementation will be added in the next phase
    console.log('Creating project...');
}

function openProject(projectId) {
    // Implementation will be added in the next phase
    console.log('Opening project:', projectId);
}

function saveProject() {
    // Implementation will be added in the next phase
    console.log('Saving project...');
}

function runCode() {
    // Implementation will be added in the next phase
    console.log('Running code...');
}

function closeIDE() {
    var ideContainer = document.getElementById('ide-container');
    var projectsContainer = document.querySelector('.vibeyourcourse-projects');
    
    if (ideContainer) {
        ideContainer.classList.add('d-none');
    }
    if (projectsContainer) {
        projectsContainer.classList.remove('d-none');
    }
}

// Close modal function
function closeModal() {
    var modal = document.getElementById('new-project-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Vibe Your Course initialized');
    console.log('Config:', window.vibeyourcourseConfig);
    
    // Add event listener for the new project button
    var newProjectBtn = document.getElementById('new-project-btn');
    if (newProjectBtn) {
        newProjectBtn.addEventListener('click', createNewProject);
    }
    
    // Add event listener for empty state new project button
    var emptyStateBtn = document.getElementById('empty-state-new-project');
    if (emptyStateBtn) {
        emptyStateBtn.addEventListener('click', createNewProject);
    }
    
    // Add event listener for create project button
    var createProjectBtn = document.getElementById('create-project-btn');
    if (createProjectBtn) {
        createProjectBtn.addEventListener('click', createProject);
    }
    
    // Add event listeners for IDE buttons
    var saveBtn = document.getElementById('save-project-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveProject);
    }
    
    var runBtn = document.getElementById('run-code-btn');
    if (runBtn) {
        runBtn.addEventListener('click', runCode);
    }
    
    var closeBtn = document.getElementById('close-ide-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeIDE);
    }
    
    // Add event listener for AI send button
    var aiSendBtn = document.getElementById('send-ai-message');
    if (aiSendBtn) {
        aiSendBtn.addEventListener('click', function() {
            // sendAIMessage function to be implemented
            console.log('Sending AI message...');
        });
    }
    
    // Add event listeners for project action buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-action="open-project"]')) {
            var projectId = e.target.getAttribute('data-project-id');
            openProject(projectId);
        } else if (e.target.matches('[data-action="view-project"]')) {
            var projectId = e.target.getAttribute('data-project-id');
            // viewProject function to be implemented
            console.log('Viewing project:', projectId);
        }
    });
    
    // Add event listener for modal close buttons
    var closeButtons = document.querySelectorAll('[data-dismiss="modal"]');
    closeButtons.forEach(function(button) {
        button.addEventListener('click', closeModal);
    });
});
</script>

<style>
.vibeyourcourse-container {
    margin-top: 20px;
}

.vibeyourcourse-brand {
    display: flex;
    align-items: center;
}

.vibeyourcourse-logo {
    max-height: 40px;
    width: auto;
    transition: transform 0.2s ease;
}

.vibeyourcourse-logo:hover {
    transform: scale(1.05);
}

.vibeyourcourse-logo-small {
    max-height: 32px;
}

.project-card {
    cursor: pointer;
    transition: transform 0.2s;
}

.project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.vibeyourcourse-ide {
    height: 80vh;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.ide-header {
    padding: 10px 15px;
    border-bottom: 1px solid #ddd;
    background-color: #f8f9fa;
}

.ide-body {
    height: calc(100% - 60px);
    padding: 0;
}

.ide-sidebar {
    border-right: 1px solid #ddd;
    padding: 10px;
    background-color: #f8f9fa;
}

.ide-editor {
    padding: 0;
}

.ide-output {
    border-left: 1px solid #ddd;
}

.console {
    height: 300px;
    background-color: #000;
    color: #00ff00;
    font-family: monospace;
    padding: 10px;
    overflow-y: auto;
}

.ai-assistant {
    height: 300px;
    padding: 10px;
}

.ai-input-container {
    display: flex;
    margin-top: 10px;
}

.ai-input-container input {
    flex: 1;
    margin-right: 10px;
}

.empty-state {
    margin-top: 50px;
}

/* Modal styles for proper display */
.modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background-color: rgba(0,0,0,0.5);
}

.modal.show {
    display: block;
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 1.75rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    margin-top: 10%;
}

.modal-content {
    position: relative;
    background-color: #fff;
    border: 1px solid rgba(0,0,0,.2);
    border-radius: 0.3rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,.5);
}
</style>

<?php

echo $OUTPUT->footer();
