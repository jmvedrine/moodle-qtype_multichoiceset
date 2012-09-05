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
 * Strings for component 'qtype_multichoiceset', language 'fr', branch 'MOODLE_20_STABLE'
 *
 * @package   qtype_multichoiceset
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['distractor'] = 'Incorrect';
$string['errnocorrect'] = 'Au moins un des choix proposés devrait être correct, afin qu\'il soit possible d\'obtenir une note maximale pour cette question.';
$string['included'] = 'Correct';
$string['pluginname'] = 'Choix multiple Tout-ou-rien';
$string['pluginname_help'] = 'Dans ce type de question, l\'étudiant peut choisir une ou plusieurs réponses. Si les choix effectués correspondent exactement aux réponses notées à 100% dans la question, il obtient un score de 100%. Si l\'étudiant choisit l\'une des réponses notées à 0% <em>ou</em> s\'il ne choisit pas <em>toutes</em> les réponses notées à 100%", il obtient un score de 0%.<br />
Le message de feedback correspondant à chaque choix s\'affiche à droite de chaque réponse sélectionnée.
Dans ce type de question, une réponse au moins doit être avoir une note de 100%. Si aucune réponse n\'est correcte, il faudra ajouter - comme réponse à 100% - une réponse intitulée par exemple "Aucune de ces réponses".<br />
Le système de score pour cette question <em>Choix Multiple Tout-ou-rien</em> différe de celui des <em>questions à choix multiple et réponses multiples</em>, car le score <em>global</em> pour chaque réponse est soit 100% ("tout") soit 0% ("rien").';
$string['pluginname_link'] = 'question/type/multichoiceset';
$string['pluginnameadding'] = 'Ajouter une question à choix multiple Tout-ou-rien';
$string['pluginnameediting'] = 'Modification d\'une question à choix multiple Tout-ou-rien';
$string['pluginnamesummary'] = 'Permet la sélection d\'une ou de plusieurs réponses à partir d\'une liste prédéfinie. Le score ne peut être que de 100% ou 0%.';
$string['correctanswer'] = 'Correct';
$string['showeachanswerfeedback'] = 'Afficher le feedback pour les réponses sélectionnées.';
