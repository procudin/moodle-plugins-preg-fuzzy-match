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
 * Short answer question type version information.
 *
 * @package    qtype
 * @subpackage shortanswer
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'qtype_writeregex';
$plugin->version   = 2014022400;

$plugin->requires  = 2013050100;

$plugin->maturity  = MATURITY_STABLE;

$plugin->dependencies = array(
    'qtype_shortanswer' => 2013050100,
    'qtype_preg' => 2013011800,
    'qtype_poasquestion' => 2013011800,
    'qbehaviour_adaptivehints' => 2013052500,
    'qbehaviour_adaptivehintsnopenalties' => 2013052500,
    'qbehaviour_interactivehints' => 2013060200
);
