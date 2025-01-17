<?php
/**
 * @package      CrowdFunding
 * @subpackage   Projects
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality for managing a project.
 *
 * @package      CrowdFunding
 * @subpackage   Projects
 */
class CrowdFundingProject
{
    protected $id;
    protected $title;
    protected $alias;
    protected $short_desc;
    protected $description;
    protected $image;
    protected $image_square;
    protected $image_small;
    protected $location;
    protected $goal;
    protected $funded;
    protected $funding_type;
    protected $funding_start;
    protected $funding_end;
    protected $funding_days;
    protected $pitch_video;
    protected $pitch_image;
    protected $hits;
    protected $created;
    protected $featured;
    protected $published;
    protected $approved;
    protected $ordering;
    protected $catid;
    protected $type_id;
    protected $user_id;

    protected $rewards;
    protected $type;

    protected $fundedPercent = 0;
    protected $daysLeft = 0;
    protected $slug = "";
    protected $catslug = "";

    /**
     * Database driver.
     *
     * @var JDatabaseDriver
     */
    protected $db;
    
    protected static $instance;

    /**
     * Initialize the object.
     *
     * <code>
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        $this->db = $db;
    }

    /**
     * Set database object.
     *
     * <code>
     * $country   = new CrowdFundingProject();
     * $country->setDb(JFactory::getDbo());
     * </code>
     *
     * @param JDatabaseDriver $db
     *
     * @return self
     */
    public function setDb(JDatabaseDriver $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Create an object.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = CrowdFundingProject::getInstance(JFactory::getDbo());
     * $project->load($projectId);
     * </code>
     *
     * @param JDatabaseDriver $db
     * @param int $id
     *
     * @return null|CrowdFundingProject
     */
    public static function getInstance(JDatabaseDriver $db, $id)
    {
        if (is_null(self::$instance)) {
            $item  = new CrowdFundingProject($db);
            $item->load($id);
            self::$instance = $item;
        }

        return self::$instance;
    }

    /**
     * Load project data from database.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = CrowdFundingProject::getInstance(JFactory::getDbo());
     * $project->load($projectId);
     * </code>
     *
     * @param int $id
     *
     * @throws UnexpectedValueException
     */
    public function load($id)
    {
        if (!$id) {
            throw new UnexpectedValueException(JText::_("LIB_CROWDFUNDING_INVALID_PROJECT"));
        }

        $query = $this->db->getQuery(true);
        $query
            ->select(
                "a.id, a.title, a.alias, a.short_desc, a.description, a.image, a.image_square, a.image_small, " .
                "a.location, a.goal, a.funded, a.funding_type, a.funding_start, a.funding_end, a.funding_days, " .
                "a.pitch_video, a.pitch_image, a.hits, a.created, a.featured, a.published, a.approved, " .
                "a.ordering, a.catid, a.type_id, a.user_id, " .
                $query->concatenate(array("a.id", "a.alias"), "-") . " AS slug, " .
                $query->concatenate(array("b.id", "b.alias"), "-") . " AS catslug"
            )
            ->from($this->db->quoteName("#__crowdf_projects", "a"))
            ->leftJoin($this->db->quoteName("#__categories", "b") . " ON a.catid = b.id")
            ->where("a.id = " . (int)$id);

        $this->db->setQuery($query);
        $result = $this->db->loadAssoc();

        if (!$result) {
            $result = array();
        }

        $this->bind($result);

        // Calculate funded percent
        if (!$this->goal) {
            $this->fundedPercent = 0;
        } else {
            $math = new ITPrismMath();
            $math->calculatePercentage($this->funded, $this->goal, 0);
            $this->fundedPercent = (string)$math;
        }

        // Calculate end date
        if (!empty($this->funding_days)) {

            $fundingStartDateValidator = new ITPrismValidatorDate($this->funding_start);
            if (!$fundingStartDateValidator->isValid()) {
                $this->funding_end = "0000-00-00";
            } else {
                $fundingStartDate = new CrowdFundingDate($this->funding_start);
                $fundingEndDate = $fundingStartDate->calculateEndDate($this->funding_days);
                $this->funding_end = $fundingEndDate->format("Y-m-d");
            }

        }

        // Calculate days left
        $today = new CrowdFundingDate();
        $this->daysLeft = $today->calculateDaysLeft($this->funding_days, $this->funding_start, $this->funding_end);
    }

    /**
     * Set data to object properties.
     *
     * <code>
     * $data = (
     *  "title"  => "My project title...",
     *  "user_id" => 3
     * );
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->bind($data);
     * </code>
     *
     * @param array $data
     * @param array $ignored This is a name of an index, that will be ignored and will not be set as object parameter.
     */
    public function bind($data, $ignored = array())
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $ignored)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Store the data in database.
     *
     * <code>
     * $data = (
     *  "title"  => "My project...",
     *  "user_id" => 1
     * );
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->bind($data);
     * $project->store();
     * </code>
     */
    public function store()
    {
        if (!$this->id) { // Insert
            $this->insertObject();
        } else { // Update
            $this->updateObject();
        }
    }

    protected function insertObject()
    {
        $created   = (!$this->created) ? "NULL" : $this->db->quote($this->created);

        $query = $this->db->getQuery(true);
        $query
            ->insert($this->db->quoteName("#__crowdf_projects"))
            ->set($this->db->quoteName("title") . "=" . $this->db->quote($this->title))
            ->set($this->db->quoteName("alias") . "=" . $this->db->quote($this->alias))
            ->set($this->db->quoteName("short_desc") . "=" . $this->db->quote($this->short_desc))
            ->set($this->db->quoteName("description") . "=" . $this->db->quote($this->description))
            ->set($this->db->quoteName("image") . "=" . $this->db->quote($this->image))
            ->set($this->db->quoteName("image_square") . "=" . $this->db->quote($this->image_square))
            ->set($this->db->quoteName("image_small") . "=" . $this->db->quote($this->image_small))
            ->set($this->db->quoteName("location") . "=" . $this->db->quote($this->location))
            ->set($this->db->quoteName("goal") . "=" . $this->db->quote($this->goal))
            ->set($this->db->quoteName("funded") . "=" . $this->db->quote($this->funded))
            ->set($this->db->quoteName("funding_type") . "=" . $this->db->quote($this->funding_type))
            ->set($this->db->quoteName("funding_start") . "=" . $this->db->quote($this->funding_start))
            ->set($this->db->quoteName("funding_end") . "=" . $this->db->quote($this->funding_end))
            ->set($this->db->quoteName("funding_days") . "=" . $this->db->quote($this->funding_days))
            ->set($this->db->quoteName("pitch_video") . "=" . $this->db->quote($this->pitch_video))
            ->set($this->db->quoteName("pitch_image") . "=" . $this->db->quote($this->pitch_image))
            ->set($this->db->quoteName("hits") . "=" . (int)$this->hits)
            ->set($this->db->quoteName("created") . "=" . $created)
            ->set($this->db->quoteName("featured") . "=" . $this->db->quote($this->featured))
            ->set($this->db->quoteName("published") . "=" . $this->db->quote($this->published))
            ->set($this->db->quoteName("approved") . "=" . $this->db->quote($this->approved))
            ->set($this->db->quoteName("ordering") . "=" . $this->db->quote($this->ordering))
            ->set($this->db->quoteName("catid") . "=" . (int)$this->catid)
            ->set($this->db->quoteName("type_id") . "=" . (int)$this->type_id)
            ->set($this->db->quoteName("user_id") . "=" . (int)$this->user_id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    protected function updateObject()
    {
        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName("#__crowdf_projects"))
            ->set($this->db->quoteName("title") . "=" . $this->db->quote($this->title))
            ->set($this->db->quoteName("alias") . "=" . $this->db->quote($this->alias))
            ->set($this->db->quoteName("short_desc") . "=" . $this->db->quote($this->short_desc))
            ->set($this->db->quoteName("description") . "=" . $this->db->quote($this->description))
            ->set($this->db->quoteName("image") . "=" . $this->db->quote($this->image))
            ->set($this->db->quoteName("image_square") . "=" . $this->db->quote($this->image_square))
            ->set($this->db->quoteName("image_small") . "=" . $this->db->quote($this->image_small))
            ->set($this->db->quoteName("location") . "=" . $this->db->quote($this->location))
            ->set($this->db->quoteName("goal") . "=" . $this->db->quote($this->goal))
            ->set($this->db->quoteName("funded") . "=" . $this->db->quote($this->funded))
            ->set($this->db->quoteName("funding_type") . "=" . $this->db->quote($this->funding_type))
            ->set($this->db->quoteName("funding_start") . "=" . $this->db->quote($this->funding_start))
            ->set($this->db->quoteName("funding_end") . "=" . $this->db->quote($this->funding_end))
            ->set($this->db->quoteName("funding_days") . "=" . $this->db->quote($this->funding_days))
            ->set($this->db->quoteName("pitch_video") . "=" . $this->db->quote($this->pitch_video))
            ->set($this->db->quoteName("pitch_image") . "=" . $this->db->quote($this->pitch_image))
            ->set($this->db->quoteName("hits") . "=" . (int)$this->hits)
            ->set($this->db->quoteName("created") . "=" . $this->db->quote($this->created))
            ->set($this->db->quoteName("featured") . "=" . $this->db->quote($this->featured))
            ->set($this->db->quoteName("published") . "=" . $this->db->quote($this->published))
            ->set($this->db->quoteName("approved") . "=" . $this->db->quote($this->approved))
            ->set($this->db->quoteName("ordering") . "=" . $this->db->quote($this->ordering))
            ->set($this->db->quoteName("catid") . "=" . (int)$this->catid)
            ->set($this->db->quoteName("type_id") . "=" . (int)$this->type_id)
            ->set($this->db->quoteName("user_id") . "=" . (int)$this->user_id)
            ->where($this->db->quoteName("id") . "=" . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Add a new amount to current funded one.
     * Calculate funded percent.
     *
     * <code>
     * $projectId = 1;
     * $finds = 50;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     * $project->addFunds($finds);
     * $project->store();
     * </code>
     *
     * @param float $amount
     */
    public function addFunds($amount)
    {
        $this->funded = $this->funded + $amount;

        // Calculate new percentage
        $math = new ITPrismMath();
        $math->calculatePercentage($this->funded, $this->goal, 0);
        $this->setFundedPercent((string)$math);
    }

    /**
     * Remove amount from current funded one.
     * Calculate funded percent.
     *
     * <code>
     * $projectId = 1;
     * $finds = 50;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     * $project->removeFunds($finds);
     * $project->store();
     * </code>
     *
     * @param float $amount
     */
    public function removeFunds($amount)
    {
        $this->funded = $this->funded - $amount;

        // Calculate new percentage
        $math = new ITPrismMath();
        $math->calculatePercentage($this->funded, $this->goal, 0);
        $this->setFundedPercent((string)$math);
    }

    /**
     * Update project funds record.
     *
     * <code>
     * $projectId = 1;
     * $finds = 50;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     * $project->addFunds($finds);
     * $project->updateFunds();
     * </code>
     */
    public function updateFunds()
    {
        $query = $this->db->getQuery(true);
        $query
            ->update($this->db->quoteName("#__crowdf_projects"))
            ->set($this->db->quoteName("funded") . "=" . $this->db->quote($this->funded))
            ->where($this->db->quoteName("id") . "=" . $this->db->quote($this->id));

        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Return all project rewards.
     *
     * <code>
     * $projectId = 1;
     *
     * $rewardsOptions = array(
     *  "state" => CrowdFundingConstants::PUBLISHED
     * );
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $rewards = $project->getRewards($rewardsOptions);
     * </code>
     *
     * @param array $options
     *
     * @return CrowdFundingRewards
     */
    public function getRewards($options = array())
    {
        if (is_null($this->rewards)) {
            jimport("crowdfunding.rewards");
            $this->rewards = CrowdFundingRewards::getInstance($this->db, $this->id, $options);
        }

        return $this->rewards;
    }

    /**
     * Return the percent of funded amount.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $percentage = $project->getFundedPercent();
     * </code>
     *
     * @return float
     */
    public function getFundedPercent()
    {
        return $this->fundedPercent;
    }

    /**
     * Set the percent of funded amount.
     *
     * @param float $percent
     */
    public function setFundedPercent($percent)
    {
        $this->fundedPercent = $percent;
    }

    /**
     * Return project ID.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * if (!$project->getId()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return category ID.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $categoryId = $project->getCategoryId();
     * </code>
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->catid;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $userId = $project->getUserId();
     * </code>
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Return project title.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $title = $project->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return project goal.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $goal = $project->getGoal();
     * </code>
     *
     * @return float
     */
    public function getGoal()
    {
        return $this->goal;
    }

    /**
     * Return the amount that has been funded.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $fundedAmount = $project->getFunded();
     * </code>
     *
     * @return float
     */
    public function getFunded()
    {
        return $this->funded;
    }

    /**
     * Return the funding type of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $fundedType = $project->getFundingType();
     * </code>
     *
     * @return string
     */
    public function getFundingType()
    {
        return $this->funding_type;
    }

    /**
     * Return the date of the end of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $fundedEndDate = $project->getFundingEnd();
     * </code>
     *
     * @return string
     */
    public function getFundingEnd()
    {
        return $this->funding_end;
    }

    /**
     * Return original image of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $image = $project->getImage();
     * </code>
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Return square image of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $image = $project->getSquareImage();
     * </code>
     *
     * @return string
     */
    public function getSquareImage()
    {
        return $this->image_square;
    }

    /**
     * Return small image of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $image = $project->getSmallImage();
     * </code>
     *
     * @return string
     */
    public function getSmallImage()
    {
        return $this->image_small;
    }

    /**
     * Return short description of the project.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $desc = $project->getShortDesc();
     * </code>
     *
     * @return string
     */
    public function getShortDesc()
    {
        return $this->short_desc;
    }

    /**
     * Return object properties.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $properties = $project->getProperties();
     * </code>
     *
     * @return array
     */
    public function getProperties()
    {
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {
            if (strcmp("db", $key) == 0) {
                unset($vars[$key]);
            }
        }

        return $vars;
    }

    /**
     * Check if the project is published.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * if (!$project->isPublished()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isPublished()
    {
        return (!$this->published) ? false : true;
    }

    /**
     * Return project type.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $type = $project->getType();
     * </code>
     *
     * @return CrowdFundingType
     */
    public function getType()
    {
        if (is_null($this->type) and !empty($this->type_id)) {

            jimport("crowdfunding.type");
            $this->type = new CrowdFundingType();
            $this->type->setDb(JFactory::getDbo());
            $this->type->load($this->type_id);

            if (!$this->type->getId()) {
                $this->type = null;
            }
        }

        return $this->type;
    }

    /**
     * Return the days that left to the end of campaign.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $days = $project->getDaysLeft();
     * </code>
     *
     * @return int
     */
    public function getDaysLeft()
    {
        return $this->daysLeft;
    }

    /**
     * Return project slug.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $slug = $project->getSlug();
     * </code>
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Return project category slug.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * $catslug = $project->getCatSlug();
     * </code>
     *
     * @return string
     */
    public function getCatSlug()
    {
        return $this->catslug;
    }

    /**
     * Check if the project is completed.
     *
     * <code>
     * $projectId = 1;
     *
     * $project   = new CrowdFundingProject(JFactory::getDbo());
     * $project->load($projectId);
     *
     * if (!$project->isCompleted()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isCompleted()
    {
        $today      = strtotime("today");
        $fundingEnd = strtotime($this->funding_end);

        return ($today <= $fundingEnd) ? false : true;
    }
}
