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
 * View Flipbook instance
 *
 * @package    mod_flipbook
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);
// Activity instance id.
$f = optional_param('f', 0, PARAM_INT);
$cm = get_coursemodule_from_id('flipbook', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$context = context_module::instance($cm->id);


if ($id) {
    $cm = get_coursemodule_from_id('flipbook', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('flipbook', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('flipbook', ['id' => $f], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('flipbook', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

\mod_flipbook\event\course_module_viewed::create_from_record($moduleinstance, $cm, $course)->trigger();

$PAGE->set_url('/mod/flipbook/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
// $PAGE->requires->js_call_amd('mod_flipbook/flipbook', 'init');
$PAGE->set_heading(format_string($course->fullname));

$flipbook = $DB->get_record('flipbook', array('id' => $cm->instance), '*', MUST_EXIST);
$pdfurl = moodle_url::make_pluginfile_url($context->id, 'mod_flipbook', 'content', $flipbook->id, '/', $flipbook->pdf);
//print_object($pdfurl);

echo $OUTPUT->header();

echo '<div id="flipbook-container" style="width: 100%; height: 600px; background: #eee;"></div>';
// Call the JS module and pass the PDF URL.
$PAGE->requires->js_call_amd('mod_flipbook/flipbook', 'init', array($pdfurl->out(true)));

echo $OUTPUT->footer();
