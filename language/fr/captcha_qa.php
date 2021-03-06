<?php
/**
*
* captcha_qa [Standard french]
* translated by PhpBB-fr.com <http://www.phpbb-fr.com/>
*
* @package language
* @version $Id: captcha_qa.php v1.25 2009-10-16 15:47:00 Elglobo $
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'CAPTCHA_QA'				=> 'Q&amp;A CAPTCHA',
	'CONFIRM_QUESTION_EXPLAIN'	=> 'Cette question est un moyen d’identifier et d’empêcher des soumissions automatisées.',
	'CONFIRM_QUESTION_WRONG'	=> 'Vous avez fourni une réponse invalide à la question de confirmation.',

	'QUESTION_ANSWERS'			=> 'Réponses',
	'ANSWERS_EXPLAIN'			=> 'Entrez des réponses valides à la question, une par ligne.',
	'CONFIRM_QUESTION'			=> 'Question',

	'ANSWER'					=> 'Réponse',
	'EDIT_QUESTION'				=> 'Editer la question',
	'QUESTIONS'					=> 'Questions',
	'QUESTIONS_EXPLAIN'			=> 'Pendant l’inscription, les utilisateurs seront invités à répondre à une des questions indiquées ici. Pour utiliser ce plugin, au moins une question doit être définie dans la langue par défaut. Il devrait être simple pour votre public cible de répondre à ces questions, mais au delà de la capacité d’un robot à lancer une recherche Google™. En utilisant un large jeu de questions modifiées régulièrement, vous obtiendrez de meilleurs résultats. Activez le contrôle strict si votre question doit prendre en compte la ponctuation ou la casse des caractères.',
	'QUESTION_DELETED'			=> 'Question supprimé',
	'QUESTION_LANG'				=> 'Langue',
	'QUESTION_LANG_EXPLAIN'		=> 'La langue dans laquelle la question et sa réponse ont été écrites.',
	'QUESTION_STRICT'			=> 'Contrôle strict',
	'QUESTION_STRICT_EXPLAIN'	=> 'Si activé, la casse des caractères et les espaces seront pris en compte.',

	'QUESTION_TEXT'				=> 'Question',
	'QUESTION_TEXT_EXPLAIN'		=> 'La question qui sera demandée à l’inscription.',

	'QA_ERROR_MSG'				=> 'Complétez tous les champs et écrivez au moins une réponse.',
));

?>