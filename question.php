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
 * Multiple choice question definition classes.
 *
 * @package    qtype
 * @subpackage multichoiceset
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multichoice/question.php');

/**
 * Represents an all or nothing multiple response question.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multichoiceset_question extends qtype_multichoice_multi_question {

    /**
     * @author Philipp Steingrebe <psteingrebe@vds.de>
     *
     * @param  array  $response
     *    The question response
     *
     * @return array
     *    The value fraction and the state
     */
    
    public function grade_response(array $response) {
        // Get number of right answers and total answer possibilities
        list($numRight, $numTotal) = $this->get_num_parts_right($response);
        
        // Get number of wrong answers, which is the difference between
        // number of given ansers and number of right answers
        $numWrong   = $this->get_num_selected_choices($response) - $numRight;
        
        // Get number of correct answer possibilities
        $numCorrect = $this->get_num_correct_choices();

        switch (true) {
            // No wrong and all correct answers selected -> full points
            case $numwrong == 0 && $numcorrect == $numright:
                $fraction = 1;
                break;
            // No wrong but not all correct answers selected -> half points
            case $numwrong == 0 && $numcorrect > 0:
                $fraction = 0.5;
                break;
            // Otherwise -> zero points
            default:
                $fraction = 0;
        }
        
        $state = question_state::graded_state_for_fraction($fraction);
        return array($fraction, $state);
    }

    protected function disable_hint_settings_when_too_many_selected(
            question_hint_with_parts $hint) {
        parent::disable_hint_settings_when_too_many_selected($hint);
        $hint->showchoicefeedback = false;
    }
}
