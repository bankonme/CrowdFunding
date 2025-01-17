<?php
/**
 * @package      CrowdFunding
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

jimport("crowdfunding.filters");

/**
 * Form Field class for the Joomla Framework.
 *
 * @package      CrowdFunding
 * @subpackage   Component
 * @since        1.6
 */
class JFormFieldCfTypes extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'cftypes';

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

        // Get types
        $filters = new CrowdFundingFilters(JFactory::getDbo());

        $typesOptions = $filters->getProjectsTypes();

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options, $typesOptions);

        return $options;
    }
}
