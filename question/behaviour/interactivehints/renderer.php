<?php
// This file is part of POAS question and related behaviours - https://bitbucket.org/oasychev/moodle-plugins/overview
//
// POAS question is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// POAS question is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Question behaviour where the student can submit questions one at a
 * time for immediate feedback with qtype specific hints support.
 *
 * @package    qbehaviour
 * @subpackage interactivehints
 * @copyright  2013 Oleg Sychev, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/behaviour/interactive/renderer.php');

class qbehaviour_interactivehints_renderer extends qbehaviour_interactive_renderer {
    public function feedback(question_attempt $qa, question_display_options $options) {
        $parentfeedback = parent::feedback($qa, $options);
        
        // replace old css classes with new ones
        return preg_replace('/class\s*\=\s*([\'\"])(.*?)\1/', 'class=$1$2 btn-secondary$1', $parentfeedback);        
    }
}
