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
 * Course module block.
 *
 * @package    block_course_module
 */
include_once($CFG->dirroot . '/course/lib.php');
include_once($CFG->libdir . '/coursecatlib.php');

class block_course_module extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'block_course_module');
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if ($records = $this->get_course_module()) {

            foreach ($records as $record) {
                $coursecontext = context_course::instance($record->course);
                $linkcss = $record->visible ? "" : " class=\"dimmed\" ";
                $this->content->items[] = "<a $linkcss title=\"" . format_string($record->name, true, array('context' => $coursecontext)) . "\" " .
                        "href=\"$CFG->wwwroot/mod/$record->type/view.php?id=$record->cmid\">" . format_string($record->name) . "</a>";
            }
            $this->title = get_string('displayname', 'block_course_module');
        }

        if ($this->content->items) { // make sure we don't return an empty list
            return $this->content;
        }

        return $this->content;
    }

    function get_course_module() {
        global $DB;

        $sql = "SELECT cm.id,cm.course,cm.module,cm.instance,
               m.name AS module_name FROM mdl_course_modules cm JOIN
               mdl_modules m ON m.id = cm.module WHERE cm.visible = 1";
        $records = $DB->get_records_sql($sql);

        $modulearray = array();

        foreach ($records as $record) {
            $modulenamesql = "select id, name, course from mdl_$record->module_name where id = $record->instance";
            $modulename = $DB->get_record_sql($modulenamesql);
            $modulename->type = $record->module_name;
            $modulename->cmid = $record->id;
            array_push($modulearray, $modulename);
        }
        return $modulearray;
    }

}
