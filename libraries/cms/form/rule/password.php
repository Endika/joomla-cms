<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       3.1.2
 */
class JFormRulePassword extends JFormRule
{

	/**
	 * Method to test if two values are not equal. To use this rule, the form
	 * XML needs a validate attribute of equals and a field attribute
	 * that is equal to the field to test against.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   JRegistry         $input    An optional JRegistry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   3.1.2
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
	{
		$field = (string) $element['field'];

		$meter		= isset($this->element['strengthmeter'])  ? ' meter="0"' : '1';
		$threshold	= isset($this->element['threshold']) ? (int) $this->element['threshold'] : 66;
		$minimumLength = isset($this->element['minimum_length']) ? (int) $this->element['minimum_length'] : 4;
		$minimumIntegers = isset($this->element['minimum_integers']) ? (int) $this->element['minimum_integers'] : 0;
		$minimumSymbols = isset($this->element['minimum_symbols']) ? (int) $this->element['minimum_symbols'] : 0;
		$minimumUppercase = isset($this->element['minimum_uppercase']) ? (int) $this->element['minimum_uppercase'] : 0;

		// If we have parameters from com_users, use those instead.
		// Some of these may be empty for legacy reasons.
		$params = JComponentHelper::getParams('com_users');

		if(!empty($params))
		{
			$minimumLengthp = $params->get('minimum_length');
			$minimumIntegersp = $params->get('minimum_integers');
			$minimumSymbolsp = $params->get('minimum_symbols');
			$minimumUppercasep = $params->get('minimum_uppercase');
			$meterp = $params->get('meter');
			$thresholdp = $params->get('threshold');

			empty($minimumLengthp) ? : $minimumLength = (int) $minimumLengthp;
			empty($minimumIntegersp) ? : $minimumIntegers = (int) $minimumIntegersp;
			empty($minimumSymbolsp) ? : $minimumSymbols = (int) $minimumSymbolsp;
			empty($minimumUppercasep) ? : $minimumUppercase = (int) $minimumUppercasep;
			empty($meterp) ? : $meter = $meterp;
			empty($thresholdp) ? : $threshold = $thresholdp;
		}

		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		$valueLength = strlen($value);

		// We set a maximum length to prevent abuse since it is unfiltered.
		if ($valueLength > 99)
		{
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_USERS_MSG_PASSWORD_TOO_LONG'),
				'warning'
				);
		}

		// We don't allow white space inside passwords
		$valueTrim =  trim($value);

		if (strlen($valueTrim) != $valueLength)
		{
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_USERS_MSG_SPACES_IN_PASSWORD'),
				'warning'
				);

			return false;
		}

		// Minimum number of integers required
		if (!empty($minimumIntegers))
		{
			$nInts = preg_match_all('/[0-9]/', $value );

			if ($nInts < $minimumIntegers)
			{
				JFactory::getApplication()->enqueueMessage(
					JText::plural('COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N', count($minimumIntegers)),
					'warning'
				);

				return false;
			}
		}

		// Minimum number of symbols required
		if (!empty($minimumSymbols))
		{

			$nsymbols = preg_match_all('[\W]', $value );

			if ($nsymbols < $minimumSymbols)
			{
				JFactory::getApplication()->enqueueMessage(
					JText::plural('COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N', count($minimumSymbols)),
					'warning'
				);

				return false;
			}
		}

		// Minimum number of upper case ASII characters required
		if (!empty($minimumUppercase))
		{

			$nUppercase = preg_match_all( "/[A-Z]/", $value );
			if ($nUppercase < $minimumUppercase)
			{
				JFactory::getApplication()->enqueueMessage(
					JText::plural('COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N', count($minimumUppercase)),
					'warning'
			);

				return false;
			}
		}

		// Minimum length option
		if (!empty($minimumLength))
		{
			if (strlen((string) $value) < $minimumLength)
			{
				JFactory::getApplication()->enqueueMessage(
					JText::plural('COM_USERS_MSG_PASSWORD_TOO_SHORT', count($minimumLength)),
					'warning'
					);

				return false;
			}
		}

		return true;
	}
}