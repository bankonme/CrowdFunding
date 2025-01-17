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

jimport('joomla.application.component.modellist');

class CrowdFundingModelDiscover extends JModelList
{
    protected $items = null;
    protected $numbers = null;
    protected $params = null;

    /**
     * Constructor.
     *
     * @param   array  $config An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'ordering', 'a.ordering'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering
     * @param string $direction
     *
     * @return  void
     * @since   1.6
     */
    protected function populateState($ordering = 'ordering', $direction = 'ASC')
    {
        parent::populateState("a.ordering", "ASC");

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Load parameters
        $params = $app->getParams();
        $this->setState('params', $params);

        // Filter by country
        $value = $app->input->get("filter_country", "", "cmd");
        $this->setState($this->context . '.filter_country', $value);

        // Filter by phrase
        $value = $app->input->get("filter_phrase");
        $this->setState($this->context . '.filter_phrase', $value);

        // Filter by filter type
        $value = $app->input->get("filter_fundingtype", "", "cmd");
        $this->setState($this->context . '.filter_fundingtype', $value);

        // Filter by filter type
        $value = $app->input->get("filter_projecttype", 0, "uint");
        $this->setState($this->context . '.filter_projecttype', $value);

        // Set category id
        $catId = $app->input->get("id", 0, "uint");
        $this->setState($this->context . '.category_id', $catId);

        // It is a discovery page and I can filter it by category.
        // If it is a subcategory page, there is a category ID
        if (!$catId) {
            // Filter by category
            $value = $app->input->get("filter_category");
            $this->setState($this->context . '.category_id', $value);
        }

        // Set limit
        $value = $app->input->getInt("limit");
        if (!$value) {
            $value = $params->get("discover_items_limit", $app->get('list_limit', 20));
        }
        $this->setState('list.limit', $value);

        $value = $app->input->getInt('limitstart', 0);
        $this->setState('list.start', $value);

    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string $id A prefix for the store id.
     *
     * @return  string      A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState($this->context . '.category_id');
        $id .= ':' . $this->getState($this->context . '.filter_country');
        $id .= ':' . $this->getState($this->context . '.filter_fundingtype');
        $id .= ':' . $this->getState($this->context . '.filter_projecttype');
        $id .= ':' . $this->getState($this->context . '.filter_phrase');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        /** @var $db JDatabaseDriver */

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.short_desc, a.image, a.user_id, a.catid, a.featured, ' .
                'a.goal, a.funded, a.funding_start, a.funding_end, a.funding_days, a.funding_type, ' .
                $query->concatenate(array("a.id", "a.alias"), "-") . ' AS slug, ' .
                'b.name AS user_name, ' .
                $query->concatenate(array("c.id", "c.alias"), "-") . " AS catslug"
            )
        );
        $query->from($db->quoteName('#__crowdf_projects', 'a'));
        $query->innerJoin($db->quoteName('#__users', 'b') . ' ON a.user_id = b.id');
        $query->innerJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id');

        // Filter by category ID
        $categoryId = $this->getState($this->context . ".category_id", 0);
        if (!empty($categoryId)) {
            $query->where('a.catid = ' . (int)$categoryId);
        }

        // Filter by project type
        $projectTypeId = $this->getState($this->context . ".filter_projecttype", 0);
        if (!empty($projectTypeId)) {
            $query->where('a.type_id = ' . (int)$projectTypeId);
        }

        // Filter by country
        $countryCode = $this->getState($this->context . ".filter_country");
        if (!empty($countryCode)) {
            $query->innerJoin($db->quoteName("#__crowdf_locations") . " AS l ON a.location = l.id");
            $query->where('l.country_code = ' . $db->quote($countryCode));
        }

        // Filter by funding type
        $filterFundingType = JString::strtoupper(JString::trim($this->getState($this->context . ".filter_fundingtype")));
        if (!empty($filterFundingType)) {
            $allowedFundingTypes = array("FIXED", "FLEXIBLE");
            if (in_array($filterFundingType, $allowedFundingTypes)) {
                $query->where('a.funding_type = ' . $db->quote($filterFundingType));
            }
        }

        // Filter by phrase
        $phrase = $this->getState($this->context . ".filter_phrase");
        if (!empty($phrase)) {
            $escaped = $db->escape($phrase, true);
            $quoted  = $db->quote("%" . $escaped . "%", false);
            $query->where('a.title LIKE ' . $quoted);
        }

        // Filter by state
        $query->where('a.published = 1');
        $query->where('a.approved = 1');

        // Add the list ordering clause.
        $orderString = $this->getOrderString();
        $query->order($db->escape($orderString));

        return $query;
    }

    protected function getOrderString()
    {
        $params    = $this->getState("params");
        $order     = $params->get("discover_order", "start_date");
        $orderDirn = $params->get("discover_dirn", "desc");

        $allowedDirns = array("asc", "desc");
        if (!in_array($orderDirn, $allowedDirns)) {
            $orderDirn = "ASC";
        } else {
            $orderDirn = JString::strtoupper($orderDirn);
        }

        switch ($order) {

            case "ordering":
                $orderCol = "a.ordering";
                break;

            case "added":
                $orderCol = "a.id";
                break;

            default: // Start date
                $orderCol = "a.funding_start";
                break;

        }

        $orderString = $orderCol . ' ' . $orderDirn;

        return $orderString;
    }

    public function prepareItems($items)
    {
        $result = array();

        if (!empty($items)) {
            foreach ($items as $key => $item) {

                $result[$key] = $item;

                // Calculate funding end date
                if (!empty($item->funding_days)) {

                    $fundingStartDate = new CrowdFundingDate($item->funding_start);
                    $endDate = $fundingStartDate->calculateEndDate($item->funding_days);
                    $result[$key]->funding_end = $endDate->format("Y-m-d");

                }

                // Calculate funded percentage.
                $percent = new ITPrismMath();
                $percent->calculatePercentage($item->funded, $item->goal, 0);
                $result[$key]->funded_percents = (string)$percent;

                // Calculate days left
                $today = new CrowdFundingDate();
                $result[$key]->days_left       = $today->calculateDaysLeft($item->funding_days, $item->funding_start, $item->funding_end);

            }
        }

        return $result;
    }
}
