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
 * Plugin strings are defined here.
 *
 * @package     mod_vibeyourcourse
 * @category    string
 * @copyright   2025 Alexander Mikasch
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin identification.
$string['pluginname'] = 'Vibe Your Course';
$string['modulename'] = 'Vibe Your Course';
$string['modulenameplural'] = 'Vibe Your Course Activities';
$string['pluginadministration'] = 'Vibe Your Course administration';

// Activity management.
$string['vibeyourcoursename'] = 'Activity name';
$string['vibeyourcoursename_help'] = 'This is the name of your coding activity that students will see.';

// Form sections.
$string['runtimeconfig'] = 'Runtime Configuration';
$string['aiconfig'] = 'AI Configuration';
$string['learningconfig'] = 'Learning Configuration'; 
$string['assessmentconfig'] = 'Assessment Configuration';

// Runtime settings.
$string['allowpython'] = 'Enable Python Runtime';
$string['allowpython_help'] = 'Allow students to write and execute Python code using Pyodide in the browser.';
$string['allowjavascript'] = 'Enable JavaScript Runtime';
$string['allowjavascript_help'] = 'Allow students to create web pages with HTML, CSS, and JavaScript.';
$string['allowwebcontainer'] = 'Enable WebContainer Runtime';
$string['allowwebcontainer_help'] = 'Allow students to create full web applications with Node.js, frameworks, and build tools.';

// AI settings.
$string['aiprovider'] = 'AI Provider';
$string['aiprovider_help'] = 'Choose which AI service to use for code assistance and generation.';
$string['aiassistancelevel'] = 'AI Assistance Level';
$string['aiassistancelevel_help'] = 'Control how much AI assistance students receive while coding.';

$string['aiassistance_full'] = 'Full AI Assistance - Code generation, debugging, and optimization';
$string['aiassistance_hints'] = 'Hints Only - Guidance and suggestions without full solutions';
$string['aiassistance_minimal'] = 'Minimal - Basic syntax help only';
$string['aiassistance_none'] = 'No AI Assistance';

// Learning settings.
$string['difficultylevel'] = 'Difficulty Level';
$string['difficultylevel_help'] = 'Set the challenge level for coding tasks and projects.';

$string['difficulty_beginner'] = 'Beginner - Simple syntax and basic concepts';
$string['difficulty_intermediate'] = 'Intermediate - Complex logic and data structures';
$string['difficulty_advanced'] = 'Advanced - Advanced algorithms and system design';
$string['difficulty_adaptive'] = 'Adaptive - Adjusts based on student performance';

$string['projecttemplates'] = 'Project Templates';
$string['projecttemplates_help'] = 'JSON configuration of available project templates. Students can start with these scaffolds.';

// Assessment settings.
$string['autoassessment'] = 'Enable Automatic Assessment';
$string['autoassessment_help'] = 'Automatically evaluate student code for correctness, style, and best practices.';
$string['peerreview'] = 'Enable Peer Review';
$string['peerreview_help'] = 'Allow students to review and comment on each other\'s code projects.';

// Interface strings.
$string['startcoding'] = 'Start Coding';
$string['newproject'] = 'New Project';
$string['myprojects'] = 'My Projects';
$string['codeeditor'] = 'Code Editor';
$string['output'] = 'Output';
$string['console'] = 'Console';
$string['files'] = 'Files';
$string['preview'] = 'Preview';

// Project management.
$string['projectname'] = 'Project Name';
$string['selecttemplate'] = 'Select Template';
$string['selectruntime'] = 'Select Runtime';
$string['saveproject'] = 'Save Project';
$string['runcode'] = 'Run Code';
$string['submitproject'] = 'Submit for Grading';
$string['shareproject'] = 'Share Project';

// AI interaction.
$string['aiassistant'] = 'AI Assistant';
$string['askai'] = 'Ask AI for Help';
$string['generatecode'] = 'Generate Code';
$string['explainerror'] = 'Explain Error';
$string['optimizecode'] = 'Optimize Code';
$string['addcomments'] = 'Add Comments';

// Runtime environments.
$string['python'] = 'Python';
$string['javascript'] = 'JavaScript/Web';
$string['webcontainer'] = 'Full Web Stack';

// Project status.
$string['status_draft'] = 'Draft';
$string['status_active'] = 'Active';
$string['status_completed'] = 'Completed';
$string['status_submitted'] = 'Submitted';
$string['status_graded'] = 'Graded';

// Error messages.
$string['error_noruntimesenabled'] = 'At least one runtime environment must be enabled.';
$string['error_invalidjson'] = 'Invalid JSON format in project templates.';
$string['error_executionfailed'] = 'Code execution failed. Check your syntax and try again.';
$string['error_aiunavailable'] = 'AI assistance is temporarily unavailable.';
$string['error_projectnotfound'] = 'Project not found or access denied.';
$string['error_savefailed'] = 'Failed to save project. Please try again.';

// Success messages.
$string['success_projectsaved'] = 'Project saved successfully.';
$string['success_projectsubmitted'] = 'Project submitted for grading.';
$string['success_codeexecuted'] = 'Code executed successfully.';

// Help and guidance.
$string['help_gettingstarted'] = 'Getting Started';
$string['help_pythonbasics'] = 'Python Basics';
$string['help_webdevelopment'] = 'Web Development';
$string['help_debugging'] = 'Debugging Tips';
$string['help_bestpractices'] = 'Coding Best Practices';

// Analytics and progress.
$string['progress'] = 'Progress';
$string['timeSpent'] = 'Time Spent';
$string['errorsFixed'] = 'Errors Fixed';
$string['conceptsMastered'] = 'Concepts Mastered';
$string['skillLevel'] = 'Skill Level';
$string['achievements'] = 'Achievements';

// Achievements.
$string['achievement_firstcode'] = 'First Code';
$string['achievement_firstproject'] = 'First Project';
$string['achievement_debugmaster'] = 'Debug Master';
$string['achievement_aicollab'] = 'AI Collaborator';
$string['achievement_helpothers'] = 'Helper';
$string['achievement_codecraftr'] = 'Code Crafter';

// Settings and configuration.
$string['disabled'] = 'Disabled';
$string['enabled'] = 'Enabled';
$string['advanced'] = 'Advanced';
$string['basic'] = 'Basic';

// Navigation.
$string['backtocourse'] = 'Back to Course';
$string['backtoproject'] = 'Back to Project';
$string['projectgallery'] = 'Project Gallery';
$string['leaderboard'] = 'Leaderboard';

// File operations.
$string['newfile'] = 'New File';
$string['deletefile'] = 'Delete File';
$string['renamefile'] = 'Rename File';
$string['uploadfile'] = 'Upload File';
$string['downloadproject'] = 'Download Project';

// Collaboration.
$string['sharecode'] = 'Share Code';
$string['viewshared'] = 'View Shared Projects';
$string['collaborate'] = 'Collaborate';
$string['leavefeedback'] = 'Leave Feedback';
$string['requesthelp'] = 'Request Help';

// Privacy.
$string['privacy:metadata'] = 'The Vibe Your Course plugin stores user coding projects, execution history, and AI interactions for educational purposes.';
$string['privacy:metadata:vibeyourcourse_projects'] = 'Information about user coding projects';
$string['privacy:metadata:vibeyourcourse_projects:userid'] = 'The ID of the user who created the project';
$string['privacy:metadata:vibeyourcourse_projects:project_name'] = 'Name of the coding project';
$string['privacy:metadata:vibeyourcourse_projects:project_files'] = 'Code files and project content';
$string['privacy:metadata:vibeyourcourse_executions'] = 'History of code executions and outputs';
$string['privacy:metadata:vibeyourcourse_ai_interactions'] = 'Interactions with AI assistance features';

// Capabilities.
$string['vibeyourcourse:addinstance'] = 'Add a new Vibe Your Course activity';
$string['vibeyourcourse:view'] = 'View Vibe Your Course activity';
$string['vibeyourcourse:submit'] = 'Submit projects in Vibe Your Course';
$string['vibeyourcourse:grade'] = 'Grade Vibe Your Course submissions';
$string['vibeyourcourse:viewall'] = 'View all student projects';
$string['vibeyourcourse:manage'] = 'Manage Vibe Your Course activity settings';

// Events
$string['eventcoursemoduleviewed'] = 'Course module viewed';
