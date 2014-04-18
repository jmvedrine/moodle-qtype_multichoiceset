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
 * Contains the helper class for the select missing words question type tests.
 *
 * @package    qtype_multichoiceset
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Test helper class for the all or nothing multichoice question type.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multichoiceset_test_helper {
    /**
     * Makes a multichoice all or nothing question with choices 'A', 'B', 'C' and 'D' shuffled.
     * 'A' and 'C' is correct, defaultmark 1.
     * @return qtype_multichoiceset_question
     */
    public static function make_a_multichoiceset_question() {
        question_bank::load_question_definition_classes('multichoiceset');
        $mc = new qtype_multichoiceset_question();
        test_question_maker::initialise_a_question($mc);
        $mc->name = 'Multi-choice all or nothing question';
        $mc->questiontext = 'The answer is A and C.';
        $mc->generalfeedback = 'You should have selected A and C.';
        $mc->qtype = question_bank::get_qtype('multichoiceset');

        $mc->shuffleanswers = 1;
        $mc->answernumbering = 'abc';

        test_question_maker::set_standard_combined_feedback_fields($mc);

        $mc->answers = array(
            13 => new question_answer(13, 'A', 0.5, 'A is part of the right answer', FORMAT_HTML),
            14 => new question_answer(14, 'B', 0, 'B is wrong', FORMAT_HTML),
            15 => new question_answer(15, 'C', 0.5, 'C is part of the right answer', FORMAT_HTML),
            16 => new question_answer(16, 'D', 0, 'D is wrong', FORMAT_HTML),
        );

        return $mc;
    }
}
