<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

jimport("itprism.init");
jimport("crowdfunding.init");

jimport('joomla.application.component.controller');

$controller = JControllerLegacy::getInstance('CrowdFunding');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
