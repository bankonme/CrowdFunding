<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class CrowdFundingViewLocations extends JViewLegacy
{
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    protected $items;
    protected $pagination;

    protected $option;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;
    protected $sortFields;

    protected $sidebar;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Add submenu
        CrowdFundingHelper::addSubmenu($this->getName());

        // Prepare sorting data
        $this->prepareSorting();

        // Prepare actions
        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Prepare sortable fields, sort values and filters.
     */
    protected function prepareSorting()
    {
        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') != 0) ? false : true;

        if ($this->saveOrder) {
            $this->saveOrderingUrl = 'index.php?option=' . $this->option . '&task=' . $this->getName() . '.saveOrderAjax&format=raw';
            JHtml::_('sortablelist.sortable', $this->getName() . 'List', 'adminForm', strtolower($this->listDirn), $this->saveOrderingUrl);
        }

        $this->sortFields = array(
            'a.published'    => JText::_('JSTATUS'),
            'a.name'         => JText::_('COM_CROWDFUNDING_NAME'),
            'a.country_code' => JText::_('COM_CROWDFUNDING_COUNTRY_CODE'),
            'a.time_zone'    => JText::_('COM_CROWDFUNDING_TIMEZONE'),
            'a.id'           => JText::_('JGRID_HEADING_ID')
        );
    }

    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar()
    {
        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_state',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array("archived" => false, "trash" => false)), 'value', 'text', $this->state->get('filter.state'), true)
        );

        $this->sidebar = JHtmlSidebar::render();
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        // Set toolbar items for the page
        JToolbarHelper::title(JText::_('COM_CROWDFUNDING_LOCATIONS_MANAGER'));
        JToolbarHelper::addNew('location.add');
        JToolbarHelper::editList('location.edit');
        JToolbarHelper::divider();

        // Add custom buttons
        $bar = JToolbar::getInstance('toolbar');

        // Import
        $link = JRoute::_('index.php?option=com_crowdfunding&view=import&type=locations');
        $bar->appendButton('Link', 'upload', JText::_("COM_CROWDFUNDING_IMPORT_LOCATIONS"), $link);

        $link = JRoute::_('index.php?option=com_crowdfunding&view=import&type=states');
        $bar->appendButton('Link', 'upload', JText::_("COM_CROWDFUNDING_IMPORT_STATES"), $link);

        // Export
        $link = JRoute::_('index.php?option=com_crowdfunding&task=export.download&format=raw&type=locations');
        $bar->appendButton('Link', 'download', JText::_("COM_CROWDFUNDING_EXPORT_LOCATIONS"), $link);

        $link = JRoute::_('index.php?option=com_crowdfunding&task=export.download&format=raw&type=states');
        $bar->appendButton('Link', 'download', JText::_("COM_CROWDFUNDING_EXPORT_STATES"), $link);

        JToolbarHelper::divider();
        JToolbarHelper::publishList("locations.publish");
        JToolbarHelper::unpublishList("locations.unpublish");
        JToolbarHelper::divider();
        JToolbarHelper::deleteList(JText::_("COM_CROWDFUNDING_DELETE_ITEMS_QUESTION"), "locations.delete");
        JToolbarHelper::divider();
        JToolbarHelper::custom('locations.backToDashboard', "dashboard", "", JText::_("COM_CROWDFUNDING_DASHBOARD"), false);
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_CROWDFUNDING_LOCATIONS_MANAGER'));

        // Scripts
        JHtml::_('behavior.multiselect');
        JHtml::_('bootstrap.tooltip');

        JHtml::_('formbehavior.chosen', 'select');

        JHtml::_('itprism.ui.joomla_list');
    }
}
