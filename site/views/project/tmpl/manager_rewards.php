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
?>
<h4 class="cf-m-0 cf-pl-5 cf-bg-color-brown"><?php echo JText::_("COM_CROWDFUNDING_REWARDS"); ?></h4>
<table class="table table-striped">
    <thead>
    <tr>
        <th class="title">
            <?php echo JText::_("COM_CROWDFUNDING_TITLE"); ?>
        </th>
        <th width="10%" class="center hidden-phone">
            <?php echo JText::_("COM_CROWDFUNDING_NUMBER"); ?>
        </th>
        <th width="10%" class="center hidden-phone">
            <?php echo JText::_("COM_CROWDFUNDING_DISTRIBUTED"); ?>
        </th>
        <th width="10%" class="center hidden-phone">
            <?php echo JText::_("COM_CROWDFUNDING_AVAILABLE"); ?>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($this->rewards as $reward) {?>
        <tr class="">
            <td>
                <a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=reward&id=" . (int)$reward->id); ?>">
                    <?php echo $this->escape($reward->title); ?>
                </a>
                <p><?php echo $this->escape($reward->description); ?></p>
            </td>
            <td class="center hidden-phone">
                <?php echo JHtml::_('crowdfunding.rewardsNumber', $reward->number); ?>
            </td>
            <td class="center hidden-phone">
                <?php echo $reward->distributed; ?>
            </td>
            <td class="center hidden-phone">
                <?php echo JHtml::_('crowdfunding.rewardsAvailable', $reward->number, $reward->distributed); ?>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>