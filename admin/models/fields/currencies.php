<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package      CrowdFunding
 * @subpackage   Components
 * @since        1.6
 */
class JFormFieldCurrencies extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'currencies';

    /**
     * Method to get the field options.
     *
     * @return  array   The field option objects.
     * @since   1.6
     */
    protected function getOptions()
    {
        // Initialize variables.
        $options = array();

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.id AS value, ' . $query->concatenate(array("a.abbr", "a.title"), " - ") . ' AS text')
            ->from($db->quoteName('#__crowdf_currencies', 'a'))
            ->order("a.abbr ASC");

        // Get the options.
        $db->setQuery($query);
        $options = $db->loadObjectList();

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
