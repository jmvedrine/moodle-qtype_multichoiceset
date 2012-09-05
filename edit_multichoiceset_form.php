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
 * Defines the editing form for the multichoiceset question type.
 *
 * @package    qtype
 * @subpackage multichoiceset
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Multiple choice all or nothing editing form definition.
 *
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multichoiceset_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        $mform->addElement('advcheckbox', 'shuffleanswers',
                get_string('shuffleanswers', 'qtype_multichoice'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_multichoice');
        $mform->setDefault('shuffleanswers', 1);

        $mform->addElement('select', 'answernumbering',
                get_string('answernumbering', 'qtype_multichoice'),
                qtype_multichoice::get_numbering_styles());
        $mform->setDefault('answernumbering', 'abc');

        $this->add_per_answer_fields($mform, get_string('choiceno', 'qtype_multichoice', '{no}'),
                null, max(5, QUESTION_NUMANS_START));

        $mform->addElement('header', 'overallfeedbackhdr', get_string('overallfeedback', 'qtype_multichoice'));

        foreach (array('correctfeedback', 'incorrectfeedback') as $feedbackname) {
            $mform->addElement('editor', $feedbackname, get_string($feedbackname, 'qtype_multichoice'),
                                array('rows' => 10), $this->editoroptions);
            $mform->setType($feedbackname, PARAM_RAW);
            if ($feedbackname == 'incorrectfeedback') {
                $mform->addElement('advcheckbox', 'shownumcorrect',
                        get_string('options', 'question'),
                        get_string('shownumpartscorrect', 'question'));
            }
        }
		
        $this->add_interactive_settings(true, true);
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
            &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $repeated[] = $mform->createElement('header', 'choicehdr',
                get_string('choiceno', 'qtype_multichoice', '{no}'));
        $repeated[] = $mform->createElement('editor', 'answer',
                get_string('answer', 'question'), array('rows' => 1), $this->editoroptions);
        $repeated[] = $mform->createElement('checkbox', 'correctanswer',
                get_string('correctanswer', 'qtype_multichoiceset'));
        $repeated[] = $mform->createElement('editor', 'feedback',
                get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);

        // These are returned by arguments passed by reference.
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $answersoption = 'answers';

        return $repeated;
    }

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) =
                parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
        $repeated[] = $this->_form->createElement('advcheckbox', 'hintshowchoicefeedback', '',
                get_string('showeachanswerfeedback', 'qtype_multichoiceset'));
        return array($repeated, $repeatedoptions);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options->answers)) {
            $key = 0;
            foreach ($question->options->answers as $answer) {
                $question->correctanswer[$key] = $answer->fraction > 0;
                $key++;
            }
        }

        if (!empty($question->hints)) {
            $key = 0;
            foreach ($question->hints as $hint) {
                $question->hintshowchoicefeedback[$key] = !empty($hint->options);
                $key += 1;
            }
        }

        if (!empty($question->options)) {
            $question->shuffleanswers =  $question->options->shuffleanswers;
            $question->answernumbering =  $question->options->answernumbering;
			$question->shownumcorrect = $question->options->shownumcorrect;
            // prepare feedback editor to display files in draft area
            foreach (array('correctfeedback', 'incorrectfeedback') as $feedbackname) {
                $draftid = file_get_submitted_draft_itemid($feedbackname);
                $text = $question->options->$feedbackname;
                $feedbackformat = $feedbackname . 'format';
                $format = $question->options->$feedbackformat;
                $default_values[$feedbackname] = array();
                $default_values[$feedbackname]['text'] = file_prepare_draft_area(
                    $draftid,       // draftid
                    $this->context->id,    // context
                    'qtype_multichoiceset',   // component
                    $feedbackname,         // filarea
                    !empty($question->id)?(int)$question->id:null, // itemid
                    $this->fileoptions,    // options
                    $text      // text
                );
                $default_values[$feedbackname]['format'] = $format;
                $default_values[$feedbackname]['itemid'] = $draftid;
            }
	        // prepare files code block ends

            $question = (object)((array)$question + $default_values);
        }
        return $question;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $answers = $data['answer'];
        $answercount = 0;
        $numberofcorrectanswers = 0;
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer['text']);
            if (empty($trimmedanswer)) {
                continue;
            }

            $answercount++;
            if (!empty($data['correctanswer'][$key])) {
                $numberofcorrectanswers++;
            }
        }

        // Perform sanity checks on number of correct answers
        if ($numberofcorrectanswers == 0) {
            $errors['answer[0]'] = get_string('errnocorrect', 'qtype_multichoiceset');
        }

        // Perform sanity checks on number of answers
        if ($answercount == 0) {
            $errors['answer[0]'] = get_string('notenoughanswers', 'qtype_multichoice', 2);
            $errors['answer[1]'] = get_string('notenoughanswers', 'qtype_multichoice', 2);
        } else if ($answercount == 1) {
            $errors['answer[1]'] = get_string('notenoughanswers', 'qtype_multichoice', 2);
        }

        return $errors;
    }

    public function qtype() {
        return 'multichoiceset';
    }
}
