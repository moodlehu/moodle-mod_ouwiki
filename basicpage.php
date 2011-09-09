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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Shared initialisation from wiki PHP pages.
 *
 * @copyright &copy; 2007 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ouwiki
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/mod/ouwiki/locallib.php');

if (isset($countasview) && class_exists('ouflags')) {
    $DASHBOARD_COUNTER = DASHBOARD_WIKI_VIEW;
}

$id = required_param('id', PARAM_INT);           // Course Module ID that defines wiki
$pagename = optional_param('page', null, PARAM_RAW);    // Which page to show. Omitted for start page
$groupid = optional_param('group', 0, PARAM_INT); // Group ID. If omitted, uses first appropriate group
$userid  = optional_param('user', 0, PARAM_INT);   // User ID (for individual wikis). If omitted, uses own

// Restrict page name
$tl = textlib_get_instance();
if ($tl->strlen($pagename) > 200) {
    print_error('pagenametoolong', 'ouwiki');
}
if (strtolower(trim($pagename)) == strtolower(get_string('startpage', 'ouwiki'))) {
    print_error('pagenameisstartpage', 'ouwiki');
}

// Get basic information about this wiki
if (!$cm = get_coursemodule_from_id('ouwiki', $id)) {
    print_error("Course module ID was incorrect");
}

$course = $DB->get_record('course', array('id' => $cm->course));
if (!$course) {
    print_error("Course is misconfigured");
}
$ouwiki = $DB->get_record('ouwiki', array('id' => $cm->instance));
if (!$ouwiki) {
    print_error("Wiki ID is incorrect in database");
}
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

global $DISABLESAMS;
if (empty($DISABLESAMS)) {
    // Make sure they're logged in and check they have permission to view
    require_course_login($course, true, $cm);
    require_capability('mod/ouwiki:view', $context);
}

// Get subwiki, creating it if necessary
$subwiki = ouwiki_get_subwiki($course, $ouwiki, $cm, $context, $groupid, $userid, true);
