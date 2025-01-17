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
<tr>
    <th width="1%" class="hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th class="title">
        <?php echo JText::_('COM_CROWDFUNDING_TXN_ID'); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_SENDER', 'e.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_BENEFICIARY', 'b.name', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_PROJECT', 'c.title', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="center nowrap">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_AMOUNT', 'a.txn_amount', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_DATE', 'a.txn_date', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_CROWDFUNDING_PAYMENT_GETAWAY', 'a.service_provider', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JText::_('COM_CROWDFUNDING_PAYMENT_STATUS'); ?>
    </th>
    <th width="10%" class="center nowrap hidden-phone">
        <?php echo JText::_('COM_CROWDFUNDING_REWARD'); ?>
    </th>
    <th width="3%"
        class="center nowrap hidden-phone"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?></th>
</tr>
	  