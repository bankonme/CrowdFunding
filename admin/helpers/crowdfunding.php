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

/**
 * It is CrowdFunding helper class
 *
 */
abstract class CrowdFundingHelper
{
    protected static $extension = "com_crowdfunding";

    protected static $statistics = array();

    /**
     * Configure the Linkbar.
     *
     * @param    string $vName The name of the active view.
     *
     * @since    1.6
     */
    public static function addSubmenu($vName = 'dashboard')
    {

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_DASHBOARD'),
            'index.php?option=' . self::$extension . '&view=dashboard',
            $vName == 'dashboard'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_CATEGORIES'),
            'index.php?option=com_categories&extension=' . self::$extension . '',
            $vName == 'categories'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_PROJECTS'),
            'index.php?option=' . self::$extension . '&view=projects',
            $vName == 'projects'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_TRANSACTIONS'),
            'index.php?option=' . self::$extension . '&view=transactions',
            $vName == 'transactions'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_LOCATIONS'),
            'index.php?option=' . self::$extension . '&view=locations',
            $vName == 'locations'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_COUNTRIES'),
            'index.php?option=' . self::$extension . '&view=countries',
            $vName == 'countries'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_CURRENCIES'),
            'index.php?option=' . self::$extension . '&view=currencies',
            $vName == 'currencies'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_UPDATES'),
            'index.php?option=' . self::$extension . '&view=updates',
            $vName == 'updates'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_COMMENTS'),
            'index.php?option=' . self::$extension . '&view=comments',
            $vName == 'comments'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_TYPES'),
            'index.php?option=' . self::$extension . '&view=types',
            $vName == 'types'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_EMAILS'),
            'index.php?option=' . self::$extension . '&view=emails',
            $vName == 'emails'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_USERS'),
            'index.php?option=' . self::$extension . '&view=users',
            $vName == 'users'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_LOGS'),
            'index.php?option=' . self::$extension . '&view=logs',
            $vName == 'logs'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_CROWDFUNDING_PLUGINS'),
            'index.php?option=com_plugins&view=plugins&filter_search=' . rawurlencode("crowdfunding"),
            $vName == 'plugins'
        );

    }

    public static function getProjectTitle($projectId)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select("title")
            ->from("#__crowdf_projects")
            ->where("id = " . (int)$projectId);

        $db->setQuery($query);

        return $db->loadResult();

    }

    public static function getProject($projectId, $fields = array("id"))
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $selectFields = array();
        foreach ($fields as $field) {
            $selectFields[] = $db->quoteName($field);
        }

        $query
            ->select($selectFields)
            ->from("#__crowdf_projects")
            ->where($db->quoteName("id") . " = " . (int)$projectId);

        $db->setQuery($query);

        return $db->loadObject();

    }

    public static function getUserIdByRewardId($rewardId)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("b.user_id")
            ->from($db->quoteName("#__crowdf_rewards", "a"))
            ->innerJoin($db->quoteName("#__crowdf_projects", "b") . " ON a.project_id = b.id")
            ->where("a.id = " . (int)$rewardId);

        $db->setQuery($query);
        $result = $db->loadResult();

        return (int)$result;
    }

    /**
     * Calculate percentage.
     *
     * @param $funded
     * @param $goal
     *
     * @return float|int
     * @deprecated since v1.8
     */
    public static function calculatePercent($funded, $goal)
    {
        if (($funded == 0) or ($goal == 0)) {
            return 0;
        }

        $value = ($funded / $goal) * 100;

        return round($value, 2);
    }

    /**
     * Calculate days left.
     *
     * @param int    $fundingDays
     * @param string $fundingStart
     * @param string $fundingEnd
     *
     * @return int
     * @deprecated since v1.8. Use CrowdFundingDate object.
     */
    public static function calcualteDaysLeft($fundingDays, $fundingStart, $fundingEnd)
    {
        // Calculate days left
        $today = new DateTime("today");
        if (!empty($fundingDays)) {

            // Validate starting date.
            // If there is not starting date, set number of day.
            if (!self::isValidDate($fundingStart)) {
                return (int)$fundingDays;
            }

            $endingDate = new DateTime($fundingStart);
            $endingDate->modify("+" . (int)$fundingDays . " days");

        } else {
            $endingDate = new DateTime($fundingEnd);
        }

        $interval = $today->diff($endingDate);
        $daysLeft = $interval->format("%r%a");

        if ($daysLeft < 0) {
            $daysLeft = 0;
        }

        return $daysLeft;
    }

    /**
     * Calculate end date
     *
     * @param string $fundingStart This is starting date
     * @param int    $fundingDays  This is period in days.
     *
     * @return string
     * @deprecated since v1.8. Use CrowdFundingDate->calculateEndDate();
     */
    public static function calcualteEndDate($fundingStart, $fundingDays)
    {
        // Calculate days left
        $endingDate = new DateTime($fundingStart);
        $endingDate->modify("+" . (int)$fundingDays . " days");

        return $endingDate->format("Y-m-d");
    }

    /**
     * Validate a date
     *
     * @param string $string
     *
     * @return boolean
     * @deprecated deprecated since version 1.8
     */
    public static function isValidDate($string)
    {
        $string = JString::trim($string);

        try {
            $date = new DateTime($string);
        } catch (Exception $e) {
            return false;
        }

        $month = $date->format('m');
        $day   = $date->format('d');
        $year  = $date->format('Y');

        if (checkdate($month, $day, $year)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * This module collects statistical data about project - number of updates, comments, funders,...
     *
     * @param integer $projectId
     *
     * @return array
     */
    public static function getProjectData($projectId)
    {
        $db = JFactory::getDbo();

        /// Updates
        if (!isset(self::$statistics[$projectId])) {
            self::$statistics[$projectId] = array(
                "updates"  => null,
                "comments" => null,
                "funders"  => null
            );

        }

        // Count updates
        if (is_null(self::$statistics[$projectId]["updates"])) {

            $query = $db->getQuery(true);
            $query
                ->select("COUNT(*) AS updates")
                ->from($db->quoteName("#__crowdf_updates"))
                ->where("project_id = " . (int)$projectId);

            $db->setQuery($query);

            self::$statistics[$projectId]["updates"] = $db->loadResult();
        }

        // Count comments
        if (is_null(self::$statistics[$projectId]["comments"])) {

            $query = $db->getQuery(true);
            $query
                ->select("COUNT(*) AS comments")
                ->from($db->quoteName("#__crowdf_comments"))
                ->where("project_id = " . (int)$projectId)
                ->where("published = 1");

            $db->setQuery($query);

            self::$statistics[$projectId]["comments"] = $db->loadResult();
        }

        // Count funders
        if (is_null(self::$statistics[$projectId]["funders"])) {

            $query = $db->getQuery(true);
            $query
                ->select("COUNT(*) AS funders")
                ->from($db->quoteName("#__crowdf_transactions"))
                ->where("project_id  = " . (int)$projectId);

            $db->setQuery($query);

            self::$statistics[$projectId]["funders"] = $db->loadResult();
        }

        return self::$statistics[$projectId];
    }

    /**
     * This method validates the period between minimum and maximum days.
     *
     * @param string  $fundingStart
     * @param string  $fundingEnd
     * @param integer $minDays
     * @param integer $maxDays
     *
     * @return boolean
     * @deprecated since v1.8
     */
    public static function isValidPeriod($fundingStart, $fundingEnd, $minDays, $maxDays)
    {
        // Get only date and remove the time
        $date         = new DateTime($fundingStart);
        $fundingStart = $date->format("Y-m-d");
        $date         = new DateTime($fundingEnd);
        $fundingEnd   = $date->format("Y-m-d");

        // Get interval between starting and ending date
        $startingDate = new DateTime($fundingStart);
        $endingDate   = new DateTime($fundingEnd);
        $interval     = $startingDate->diff($endingDate);

        $days = $interval->format("%r%a");

        // Validate minimum dates
        if ($days < $minDays) {
            return false;
        }

        if (!empty($maxDays) and $days > $maxDays) {
            return false;
        }

        return true;
    }

    /**
     * @param $userId
     * @param $type
     *
     * @return ITPrismIntegrateInterfaceProfile|null
     */
    public static function getSocialProfile($userId, $type)
    {
        $profile = null;

        switch ($type) {

            case "socialcommunity":

                if (!defined("SOCIALCOMMUNITY_PATH_COMPONENT_SITE")) {
                    define("SOCIALCOMMUNITY_PATH_COMPONENT_SITE", JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_socialcommunity");
                }

                JLoader::register("SocialCommunityHelperRoute", SOCIALCOMMUNITY_PATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "route.php");

                jimport("itprism.integrate.profile.socialcommunity");

                $profile = ITPrismIntegrateProfileSocialCommunity::getInstance($userId);

                // Set path to pictures
                /** @var  $params Joomla\Registry\Registry */
                $params = JComponentHelper::getParams("com_socialcommunity");
                $path   = $params->get("images_directory", "images/profiles") . "/";

                $profile->setPath($path);

                break;

            case "gravatar":

                jimport("itprism.integrate.profile.gravatar");
                $profile = ITPrismIntegrateProfileGravatar::getInstance($userId);

                break;

            case "kunena":

                jimport("itprism.integrate.profile.kunena");
                $profile = ITPrismIntegrateProfileKunena::getInstance($userId);

                break;

            case "jomsocial":

                jimport("itprism.integrate.profile.jomsocial");
                $profile = ITPrismIntegrateProfileJomSocial::getInstance($userId);

                break;

            case "easysocial":

                jimport("itprism.integrate.profile.easysocial");
                $profile = ITPrismIntegrateProfileEasySocial::getInstance($userId);

                break;

            default:

                break;
        }

        return $profile;
    }

    /**
     * Generate a path to the folder, where the images are stored.
     *
     * @param int    $userId User Id.
     * @param string $path   A base path to the folder. It can be JPATH_BASE, JPATH_ROOT, JPATH_SITE,... Default is JPATH_ROOT.
     *
     * @return string
     */
    public static function getImagesFolder($userId = 0, $path = JPATH_ROOT)
    {
        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.folder');

        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $folder = $path . DIRECTORY_SEPARATOR . $params->get("images_directory", "images/crowdfunding");

        if (!empty($userId)) {
            $folder .= DIRECTORY_SEPARATOR . "user" . (int)$userId;
        }

        return JPath::clean($folder);
    }

    /**
     * Create a folder and index.html file.
     *
     * @param string $folder
     *
     * @return string
     */
    public static function createFolder($folder)
    {
        JFolder::create($folder);

        $folderIndex = JPath::clean($folder . DIRECTORY_SEPARATOR . "index.html");
        $buffer      = "<!DOCTYPE html><title></title>";

        jimport('joomla.filesystem.file');
        JFile::write($folderIndex, $buffer);
    }

    /**
     * Generate a URI path to the folder, where the images are stored.
     *
     * @param int $userId User Id.
     *
     * @return string
     */
    public static function getImagesFolderUri($userId = 0)
    {
        $params = JComponentHelper::getParams(self::$extension);
        /** @var $params Joomla\Registry\Registry */

        $uriImages = $params->get("images_directory", "images/crowdfunding");

        if (!empty($userId)) {
            $uriImages .= "/user" . (int)$userId;
        }

        return $uriImages;
    }

    /**
     * Generate a URI string by a given list of parameters.
     *
     * @param array $params
     *
     * @return string
     */
    public static function generateUrlParams($params)
    {
        $result = "";
        foreach ($params as $key => $param) {
            $result .= "&" . $key . "=" . $param;
        }

        return $result;
    }

    /**
     * Prepare date format.
     *
     * @param string $calendar
     *
     * @return string
     */
    public static function getDateFormat($calendar = "")
    {
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        $dateFormat = $params->get("project_date_format", "%Y-%m-%d");

        if (!$calendar) {
            $dateFormat = str_replace("%", "", $dateFormat);
        }

        return $dateFormat;
    }
}
