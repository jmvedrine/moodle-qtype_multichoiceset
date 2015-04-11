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
 * The questiontype class for the multiple choice question type.
 *
 * @package    qtype_multichoiceset
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/multichoice/questiontype.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');

/**
 * The multiple choice all or nothing question type.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multichoiceset extends question_type {
    public function has_html_answers() {
        return true;
    }

    public function get_question_options($question) {
        global $DB, $OUTPUT;
        $question->options = $DB->get_record('qtype_multichoiceset_options',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;
        $result = new stdClass();

        $oldanswers = $DB->get_records('question_answers',
                array('question' => $question->id), 'id ASC');

        // Following hack to check at least two answers exist.
        $answercount = 0;
        foreach ($question->answer as $key => $answer) {
            if ($answer != '') {
                $answercount++;
            }
        }
        if ($answercount < 2) { // Check there are at lest 2 answers for multiple choice.
            $result->notice = get_string('notenoughanswers', 'qtype_multichoice', '2');
            return $result;
        }

        // Insert all the new answers.
        $totalfraction = 0;
        $maxfraction = -1;
        foreach ($question->answer as $key => $answerdata) {
            if (trim($answerdata['text']) == '') {
                continue;
            }

            // Update an existing answer if possible.
            $answer = array_shift($oldanswers);
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }

            if (is_array($answerdata)) {
                // Doing an import.
                $answer->answer = $this->import_or_save_files($answerdata,
                        $context, 'question', 'answer', $answer->id);
                $answer->answerformat = $answerdata['format'];
            } else {
                // Saving the form.
                $answer->answer = $answerdata;
                $answer->answerformat = FORMAT_HTML;
            }
            $answer->fraction = !empty($question->correctanswer[$key]);
            $answer->feedback = $this->import_or_save_files($question->feedback[$key],
                    $context, 'question', 'answerfeedback', $answer->id);
            $answer->feedbackformat = $question->feedback[$key]['format'];

            $DB->update_record('question_answers', $answer);
        }

        // Delete any left over old answer records.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }

        $options = $DB->get_record('qtype_multichoiceset_options', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->correctfeedback = '';
            $options->incorrectfeedback = '';
            $options->id = $DB->insert_record('qtype_multichoiceset_options', $options);
        }

        if (isset($question->layout)) {
            $options->layout = $question->layout;
        }
        $options->answernumbering = $question->answernumbering;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->correctfeedback = $this->import_or_save_files($question->correctfeedback,
                $context, 'question', 'correctfeedback', $question->id);
        $options->correctfeedbackformat = $question->correctfeedback['format'];
        $options->incorrectfeedback = $this->import_or_save_files($question->incorrectfeedback,
                $context, 'question', 'incorrectfeedback', $question->id);
        $options->incorrectfeedbackformat = $question->incorrectfeedback['format'];
        $options->shownumcorrect = !empty($question->shownumcorrect);

        $DB->update_record('qtype_multichoiceset_options', $options);
        $this->save_hints($question, true);
    }

    public function save_hints($formdata, $withparts = false) {
        global $DB;
        $context = $formdata->context;

        $oldhints = $DB->get_records('question_hints',
                array('questionid' => $formdata->id), 'id ASC');

        if (!empty($formdata->hint)) {
            $numhints = max(array_keys($formdata->hint)) + 1;
        } else {
            $numhints = 0;
        }

        if ($withparts) {
            if (!empty($formdata->hintclearwrong)) {
                $numclears = max(array_keys($formdata->hintclearwrong)) + 1;
            } else {
                $numclears = 0;
            }
            if (!empty($formdata->hintshownumcorrect)) {
                $numshows = max(array_keys($formdata->hintshownumcorrect)) + 1;
            } else {
                $numshows = 0;
            }
            $numhints = max($numhints, $numclears, $numshows);
        }

        if (!empty($formdata->hintshowchoicefeedback)) {
            $numshowfeedbacks = max(array_keys($formdata->hintshowchoicefeedback)) + 1;
        } else {
            $numshowfeedbacks = 0;
        }
        $numhints = max($numhints, $numshowfeedbacks);

        for ($i = 0; $i < $numhints; $i += 1) {
            if (html_is_blank($formdata->hint[$i]['text'])) {
                $formdata->hint[$i]['text'] = '';
            }

            if ($withparts) {
                $clearwrong = !empty($formdata->hintclearwrong[$i]);
                $shownumcorrect = !empty($formdata->hintshownumcorrect[$i]);
            }

            $showchoicefeedback = !empty($formdata->hintshowchoicefeedback[$i]);

            if (empty($formdata->hint[$i]['text']) && empty($clearwrong) &&
                    empty($shownumcorrect) && empty($showchoicefeedback)) {
                continue;
            }

            // Update an existing hint if possible.
            $hint = array_shift($oldhints);
            if (!$hint) {
                $hint = new stdClass();
                $hint->questionid = $formdata->id;
                $hint->hint = '';
                $hint->id = $DB->insert_record('question_hints', $hint);
            }

            $hint->hint = $this->import_or_save_files($formdata->hint[$i],
                    $context, 'question', 'hint', $hint->id);
            $hint->hintformat = $formdata->hint[$i]['format'];
            if ($withparts) {
                $hint->clearwrong = $clearwrong;
                $hint->shownumcorrect = $shownumcorrect;
            }
            $hint->options = $showchoicefeedback;
            $DB->update_record('question_hints', $hint);
        }

        // Delete any remaining old hints.
        $fs = get_file_storage();
        foreach ($oldhints as $oldhint) {
            $fs->delete_area_files($context->id, 'question', 'hint', $oldhint->id);
            $DB->delete_records('question_hints', array('id' => $oldhint->id));
        }
    }

    protected function make_hint($hint) {
        return qtype_multichoiceset_hint::load_from_record($hint);
    }

    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());
        $class = 'qtype_multichoiceset_question';
        return new $class();
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->answernumbering = $questiondata->options->answernumbering;
        if (!empty($questiondata->options->layout)) {
            $question->layout = $questiondata->options->layout;
        } else {
            $question->layout = qtype_multichoice_single_question::LAYOUT_VERTICAL;
        }
        $question->correctfeedback = $questiondata->options->correctfeedback;
        $question->correctfeedbackformat = $questiondata->options->correctfeedbackformat;
        $question->incorrectfeedback = $questiondata->options->incorrectfeedback;
        $question->incorrectfeedbackformat = $questiondata->options->incorrectfeedbackformat;
        $question->shownumcorrect = $questiondata->options->shownumcorrect;

        $this->initialise_question_answers($question, $questiondata, false);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_multichoiceset_options', array('questionid' => $questionid));
        return parent::delete_question($questionid, $contextid);
    }

    protected function get_num_correct_choices($questiondata) {
        $numright = 0;
        foreach ($questiondata->options->answers as $answer) {
            if (!question_state::graded_state_for_fraction($answer->fraction)->is_incorrect()) {
                $numright += 1;
            }
        }
        return $numright;
    }

    public function get_random_guess_score($questiondata) {
        // Pretty much impossible to compute for _multi questions. Don't try.
        return null;
    }

    public function get_possible_responses($questiondata) {
        $parts = array();

        foreach ($questiondata->options->answers as $aid => $answer) {
            $parts[$aid] = array($aid =>
                    new question_possible_response(html_to_text(format_text(
                    $answer->answer, $answer->answerformat, array('noclean' => true)),
                    0, false), $answer->fraction));
        }

        return $parts;
    }

    /**
     * @return array of the numbering styles supported. For each one, there
     *      should be a lang string answernumberingxxx in teh qtype_multichoice
     *      language file, and a case in the switch statement in number_in_style,
     *      and it should be listed in the definition of this column in install.xml.
     */
    public static function get_numbering_styles() {
        $styles = array();
        foreach (array('abc', 'ABCD', '123', 'iii', 'IIII', 'none') as $numberingoption) {
            $styles[$numberingoption] =
                    get_string('answernumbering' . $numberingoption, 'qtype_multichoice');
        }
        return $styles;
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        $fs = get_file_storage();

        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid, true);

        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'question', 'correctfeedback', $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'question', 'incorrectfeedback', $questionid);
    }

    protected function delete_files($questionid, $contextid) {
        $fs = get_file_storage();

        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid, true);
        $fs->delete_area_files($contextid, 'question', 'correctfeedback', $questionid);
        $fs->delete_area_files($contextid, 'question', 'incorrectfeedback', $questionid);
    }


    // IMPORT EXPORT FUNCTIONS.

    /**
     * Provide export functionality for xml format
     * @param question object the question object
     * @param format object the format object so that helper methods can be used
     * @param extra mixed any additional format specific data that may be passed by the format (see format code for info)
     * @return string the data to append to the output buffer or false if error
     */
    public function export_to_xml($question, qformat_xml $format, $extra=null) {
        $expout = '';
        $fs = get_file_storage();
        $contextid = $question->contextid;

        $expout .= "    <shuffleanswers>".$format->get_single($question->options->shuffleanswers)."</shuffleanswers>\n";

        $textformat = $format->get_format($question->options->correctfeedbackformat);
        $files = $fs->get_area_files($contextid, 'question', 'correctfeedback', $question->id);
        $expout .= "    <correctfeedback format=\"$textformat\">\n"
                . '      ' . $format->writetext($question->options->correctfeedback);
        $expout .= $format->write_files($files);
        $expout .= "    </correctfeedback>\n";

        $textformat = $format->get_format($question->options->incorrectfeedbackformat);
        $files = $fs->get_area_files($contextid, 'question', 'incorrectfeedback', $question->id);
        $expout .= "    <incorrectfeedback format=\"$textformat\">\n"
                . '      ' . $format->writetext($question->options->incorrectfeedback);
        $expout .= $format->write_files($files);
        $expout .= "    </incorrectfeedback>\n";
        if (!empty($question->options->shownumcorrect)) {
            $expout .= "    <shownumcorrect/>\n";
        }
        $expout .= "    <answernumbering>{$question->options->answernumbering}</answernumbering>\n";
        $expout .= $format->write_answers($question->options->answers);

        return $expout;
    }

    /**
     * Provide import functionality for xml format
     * @param data mixed the segment of data containing the question
     * @param question object question object processed (so far) by standard import code
     * @param format object the format object so that helper methods can be used (in particular error())
     * @param extra mixed any additional format specific data that may be passed by the format (see format code for info)
     * @return object question object suitable for save_options() call or false if cannot handle
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        // Check question is for us.
        if (!isset($data['@']['type']) || $data['@']['type'] != 'multichoiceset') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'multichoiceset';

        $question->shuffleanswers = $format->trans_single(
                $format->getpath($data, array('#', 'shuffleanswers', 0, '#'), 1));

        $question->answernumbering = $format->getpath($data,
                array('#', 'answernumbering', 0, '#'), 'abc');

        $question->correctfeedback = array();
        $question->correctfeedback['text'] = $format->getpath($data, array('#', 'correctfeedback', 0, '#', 'text', 0, '#'),
                '', true);
        $question->correctfeedback['format'] = $format->trans_format(
                 $format->getpath($data, array('#', 'correctfeedback', 0, '@', 'format'),
                 $format->get_format($question->questiontextformat)));
        $question->correctfeedback['files'] = array();
        // Restore files in correctfeedback.
        $files = $format->getpath($data, array('#', 'correctfeedback', 0, '#', 'file'), array(), false);
        foreach ($files as $file) {
            $filesdata = new stdclass;
            $filesdata->content = $file['#'];
            $filesdata->encoding = $file['@']['encoding'];
            $filesdata->name = $file['@']['name'];
            $question->correctfeedback['files'][] = $filesdata;
        }

        $question->incorrectfeedback = array();
        $question->incorrectfeedback['text'] = $format->getpath($data, array('#', 'incorrectfeedback', 0, '#', 'text', 0, '#'),
                '', true );
        $question->incorrectfeedback['format'] = $format->trans_format(
                $format->getpath($data, array('#', 'incorrectfeedback', 0, '@', 'format'),
                $format->get_format($question->questiontextformat)));
        $question->incorrectfeedback['files'] = array();
        // Restore files in incorrectfeedback.
        $files = $format->getpath($data, array('#', 'incorrectfeedback', 0, '#', 'file'), array(), false);
        foreach ($files as $file) {
            $filesdata = new stdclass;
            $filesdata->content = $file['#'];
            $filesdata->encoding = $file['@']['encoding'];
            $filesdata->name = $file['@']['name'];
            $question->incorrectfeedback['files'][] = $filesdata;
        }

        $question->shownumcorrect = array_key_exists('shownumcorrect', $data['#']);

        // Run through the answers.
        $answers = $data['#']['answer'];
        foreach ($answers as $answer) {
            $ans = $format->import_answer($answer, true,
                    $format->get_format($question->questiontextformat));
            $question->answer[] = $ans->answer;
            $question->correctanswer[] = !empty($ans->fraction);
            $question->feedback[] = $ans->feedback;

            // Backwards compatibility.
            if (array_key_exists('correctanswer', $answer['#'])) {
                $key = end(array_keys($question->correctanswer));
                $question->correctanswer[$key] = $format->getpath($answer,
                        array('#', 'correctanswer', 0, '#'), 0);
            }
        }

        $format->import_hints($question, $data, true, true,
                $format->get_format($question->questiontextformat));

        // Get extra choicefeedback setting from each hint.
        if (!empty($question->hintoptions)) {
            foreach ($question->hintoptions as $key => $options) {
                $question->hintshowchoicefeedback[$key] = !empty($options);
            }
        }
        return $question;
    }

}

/**
 * An extension of {@link question_hint_with_parts} for multichoiceset questions
 * with an extra option for whether to show the feedback for each choice.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multichoiceset_hint extends question_hint_with_parts {
    /** @var boolean whether to show the feedback for each choice. */
    public $showchoicefeedback;

    /**
     * Constructor.
     * @param string $hint The hint text
     * @param bool $shownumcorrect whether the number of right parts should be shown
     * @param bool $clearwrong whether the wrong parts should be reset.
     * @param bool $showchoicefeedback whether to show the feedback for each choice.
     */
    public function __construct($id, $hint, $hintformat, $shownumcorrect,
            $clearwrong, $showchoicefeedback) {
        parent::__construct($id, $hint, $hintformat, $shownumcorrect, $clearwrong);
        $this->showchoicefeedback = $showchoicefeedback;
    }

    /**
     * Create a basic hint from a row loaded from the question_hints table in the database.
     * @param object $row with $row->hint, ->shownumcorrect and ->clearwrong set.
     * @return question_hint_with_parts
     */
    public static function load_from_record($row) {
        return new qtype_multichoiceset_hint($row->id, $row->hint, $row->hintformat,
                $row->shownumcorrect, $row->clearwrong, !empty($row->options));
    }

    public function adjust_display_options(question_display_options $options) {
        parent::adjust_display_options($options);
        $options->suppresschoicefeedback = !$this->showchoicefeedback;
    }
}