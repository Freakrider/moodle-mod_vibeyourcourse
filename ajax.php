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
 * AJAX endpoints for vibeyourcourse module.
 *
 * @package     mod_vibeyourcourse
 * @copyright   2025 Alexander Mikasch
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Allow CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get parameters
$action = optional_param('action', '', PARAM_ALPHA);
$cmid = optional_param('cmid', 0, PARAM_INT);

// Debug output
error_log("AJAX Request - Action: $action, CMID: $cmid");
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));

if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing action parameter']);
    exit();
}

if (empty($cmid)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing cmid parameter']);
    exit();
}

// Get course module and context
try {
    $cm = get_coursemodule_from_id('vibeyourcourse', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('vibeyourcourse', array('id' => $cm->instance), '*', MUST_EXIST);
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode(['error' => 'Course module not found: ' . $e->getMessage()]);
    exit();
}

// Verify login and capabilities
try {
    require_login($course, true, $cm);
    $modulecontext = context_module::instance($cm->id);
    require_capability('mod/vibeyourcourse:view', $modulecontext);
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied: ' . $e->getMessage()]);
    exit();
}

// Validate sesskey for write operations
if (in_array($action, ['create_project', 'update_project', 'delete_project'])) {
    $sesskey = optional_param('sesskey', '', PARAM_RAW);
    if (!confirm_sesskey($sesskey)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid session key']);
        exit();
    }
}

// Set JSON response headers
header('Content-Type: application/json');

try {
    switch ($action) {
        case 'get_projects':
            echo json_encode(get_user_projects($moduleinstance->id, $USER->id));
            break;
            
        case 'create_project':
            $project_data = [
                'name' => required_param('name', PARAM_TEXT),
                'runtime' => required_param('runtime', PARAM_ALPHA),
                'template' => optional_param('template', '', PARAM_TEXT)
            ];
            echo json_encode(create_project($moduleinstance->id, $USER->id, $project_data));
            break;
            
        case 'get_project':
            $project_id = required_param('project_id', PARAM_INT);
            echo json_encode(get_project_details($project_id, $USER->id));
            break;
            
        case 'update_project':
            $project_id = required_param('project_id', PARAM_INT);
            $project_data = [
                'name' => optional_param('name', null, PARAM_TEXT),
                'files' => optional_param('files', null, PARAM_RAW),
                'status' => optional_param('status', null, PARAM_ALPHA),
                'completion_percentage' => optional_param('completion_percentage', null, PARAM_INT)
            ];
            echo json_encode(update_project($project_id, $USER->id, $project_data));
            break;
            
        case 'delete_project':
            $project_id = required_param('project_id', PARAM_INT);
            echo json_encode(delete_project($project_id, $USER->id));
            break;
            
        default:
            throw new moodle_exception('invalidaction', 'mod_vibeyourcourse');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Get all projects for a user in this activity
 */
function get_user_projects($vibeyourcourse_id, $userid) {
    global $DB;
    
    $projects = $DB->get_records('vibeyourcourse_projects', 
        array('vibeyourcourse_id' => $vibeyourcourse_id, 'userid' => $userid), 
        'timemodified DESC'
    );
    
    $result = [];
    foreach ($projects as $project) {
        $result[] = [
            'id' => $project->id,
            'project_name' => $project->project_name,
            'runtime_type' => $project->runtime_type,
            'status' => $project->status,
            'completion_percentage' => $project->completion_percentage,
            'timecreated' => $project->timecreated,
            'timemodified' => $project->timemodified
        ];
    }
    
    return ['success' => true, 'projects' => $result];
}

/**
 * Create a new project
 */
function create_project($vibeyourcourse_id, $userid, $project_data) {
    global $DB;
    
    // Validate runtime against allowed runtimes
    $moduleinstance = $DB->get_record('vibeyourcourse', array('id' => $vibeyourcourse_id), '*', MUST_EXIST);
    $allowed_runtimes = json_decode($moduleinstance->allowed_runtimes, true);
    
    if (!in_array($project_data['runtime'], $allowed_runtimes)) {
        throw new moodle_exception('invalidruntime', 'mod_vibeyourcourse');
    }
    
    // Create initial project files based on runtime
    $initial_files = get_initial_project_files($project_data['runtime'], $project_data['template']);
    
    $project = new stdClass();
    $project->vibeyourcourse_id = $vibeyourcourse_id;
    $project->userid = $userid;
    $project->project_name = $project_data['name'];
    $project->runtime_type = $project_data['runtime'];
    $project->template_id = $project_data['template'];
    $project->project_files = json_encode($initial_files);
    $project->status = 'draft';
    $project->completion_percentage = 0;
    $project->ai_hints_used = 0;
    $project->ai_generations_used = 0;
    $project->grade = 0;
    $project->timecreated = time();
    $project->timemodified = time();
    
    $project_id = $DB->insert_record('vibeyourcourse_projects', $project);
    
    return [
        'success' => true, 
        'project_id' => $project_id,
        'project' => [
            'id' => $project_id,
            'project_name' => $project->project_name,
            'runtime_type' => $project->runtime_type,
            'status' => $project->status,
            'completion_percentage' => $project->completion_percentage,
            'timecreated' => $project->timecreated,
            'timemodified' => $project->timemodified
        ]
    ];
}

/**
 * Get detailed project information including files
 */
function get_project_details($project_id, $userid) {
    global $DB;
    
    $project = $DB->get_record('vibeyourcourse_projects', 
        array('id' => $project_id, 'userid' => $userid), 
        '*', MUST_EXIST
    );
    
    return [
        'success' => true,
        'project' => [
            'id' => $project->id,
            'project_name' => $project->project_name,
            'runtime_type' => $project->runtime_type,
            'template_id' => $project->template_id,
            'project_files' => json_decode($project->project_files, true),
            'status' => $project->status,
            'completion_percentage' => $project->completion_percentage,
            'ai_hints_used' => $project->ai_hints_used,
            'ai_generations_used' => $project->ai_generations_used,
            'grade' => $project->grade,
            'feedback' => $project->feedback,
            'timecreated' => $project->timecreated,
            'timemodified' => $project->timemodified
        ]
    ];
}

/**
 * Update an existing project
 */
function update_project($project_id, $userid, $project_data) {
    global $DB;
    
    $project = $DB->get_record('vibeyourcourse_projects', 
        array('id' => $project_id, 'userid' => $userid), 
        '*', MUST_EXIST
    );
    
    $updated = false;
    
    if ($project_data['name'] !== null) {
        $project->project_name = $project_data['name'];
        $updated = true;
    }
    
    if ($project_data['files'] !== null) {
        $project->project_files = $project_data['files'];
        $updated = true;
    }
    
    if ($project_data['status'] !== null) {
        $project->status = $project_data['status'];
        $updated = true;
    }
    
    if ($project_data['completion_percentage'] !== null) {
        $project->completion_percentage = $project_data['completion_percentage'];
        $updated = true;
    }
    
    if ($updated) {
        $project->timemodified = time();
        $DB->update_record('vibeyourcourse_projects', $project);
    }
    
    return ['success' => true, 'updated' => $updated];
}

/**
 * Delete a project
 */
function delete_project($project_id, $userid) {
    global $DB;
    
    // Verify ownership
    $project = $DB->get_record('vibeyourcourse_projects', 
        array('id' => $project_id, 'userid' => $userid), 
        '*', MUST_EXIST
    );
    
    // Delete related executions
    $DB->delete_records('vibeyourcourse_executions', array('project_id' => $project_id));
    
    // Delete related AI interactions
    $DB->delete_records('vibeyourcourse_ai_interactions', array('project_id' => $project_id));
    
    // Delete related analytics
    $DB->delete_records('vibeyourcourse_analytics', array('project_id' => $project_id));
    
    // Delete the project itself
    $DB->delete_records('vibeyourcourse_projects', array('id' => $project_id));
    
    return ['success' => true];
}

/**
 * Get initial project files based on runtime and template
 */
function get_initial_project_files($runtime, $template = '') {
    $files = [];
    
    switch ($runtime) {
        case 'python':
            $files['main.py'] = "# Welcome to your Python project!\nprint('Hello, World!')\n";
            if ($template) {
                // Add template-specific files
                $files = array_merge($files, get_template_files($runtime, $template));
            }
            break;
            
        case 'javascript':
            $files['index.html'] = "<!DOCTYPE html>\n<html>\n<head>\n    <title>My JavaScript Project</title>\n</head>\n<body>\n    <h1>Hello, World!</h1>\n    <script src=\"script.js\"></script>\n</body>\n</html>";
            $files['script.js'] = "// Welcome to your JavaScript project!\nconsole.log('Hello, World!');\n";
            $files['style.css'] = "/* Add your styles here */\nbody {\n    font-family: Arial, sans-serif;\n    margin: 0;\n    padding: 20px;\n}\n";
            break;
            
        case 'webcontainer':
            $files['package.json'] = json_encode([
                "name" => "my-project",
                "version" => "1.0.0",
                "scripts" => [
                    "start" => "node index.js"
                ]
            ], JSON_PRETTY_PRINT);
            $files['index.js'] = "// Welcome to your Node.js project!\nconsole.log('Hello, World!');\n";
            break;
    }
    
    return $files;
}

/**
 * Get template-specific files
 */
function get_template_files($runtime, $template) {
    // This would be expanded to load actual templates
    return [];
}
