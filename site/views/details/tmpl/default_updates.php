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
<?php if($this->isOwner) {?>
<form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="updatesForm" id="crowdf-updates-form" class="form-validate" autocomplete="off">
            
        <?php echo $this->form->getLabel('title'); ?>
        <?php echo $this->form->getInput('title'); ?>
        
        <?php echo $this->form->getLabel('description'); ?>
        <?php echo $this->form->getInput('description'); ?>
        
        <?php echo $this->form->getInput('id'); ?>
        <?php echo $this->form->getInput('project_id'); ?>
        
        <input type="hidden" name="task" value="update.save" />
        <?php echo JHtml::_('form.token'); ?>
        
        <div class="clearfix"></div>
        <button type="submit" class="btn btn-primary"><?php echo JText::_("JSAVE")?></button>
        <button type="submit" class="btn" id="cf-updates-reset"><?php echo JText::_("COM_CROWDFUNDING_RESET")?></button>
</form>
<div class="hr margin-tb-15px"></div>
<?php }?>
<?php if(!empty($this->items)) {
    $socialProfile  = (!$this->socialProfiles) ? null : $this->socialProfiles->getLink($this->item->user_id); 
    $socialAvatar   = (!$this->socialProfiles) ? $this->defaultAvatar : $this->socialProfiles->getAvatar($this->item->user_id, $this->avatarsSize);
?>
<?php foreach($this->items as $item ) { ?>
    <div class="row-fluid cf-update-item" id="update<?php echo $item->id;?>">
    
        <div class="media">
            <a class="pull-left" href="<?php echo (!$socialProfile) ? "javascript: void(0);" : $socialProfile;?>">
                <img class="media-object" src="<?php echo $socialAvatar;?>" width="<?php echo $this->avatarsSize;?>" height="<?php echo $this->avatarsSize;?>">
            </a>
            
            <div class="media-body">
            	<div class="cf-info-bar"> 
            		<div class="pull-left">
            		  <?php echo JHtml::_("crowdfunding.postedby", $item->author, $item->record_date, $socialProfile)?>
            		</div>
                	<?php if($this->userId == $item->user_id ) {?>
                	<div class="pull-right">
                		<a href="javascript: void(0);" class="btn btn-mini upedit_btn" data-id="<?php echo $item->id;?>"><?php echo JText::_("COM_CROWDFUNDING_EDIT");?></a>
                		<a href="javascript: void(0);" class="btn btn-mini btn-danger upremove_btn" data-id="<?php echo $item->id;?>"><?php echo JText::_("COM_CROWDFUNDING_DELETE");?></a>
                	</div>
                	<?php }?>
                	<div class="clearfix"></div>
            	</div>
            	<h3><?php echo $item->title?></h3>
            	<p><?php echo nl2br($item->description);?></p>
        	</div>
    	</div>
    	
    </div>
    <?php }?>
    
<input type="hidden" value="<?php echo JText::_("COM_CROWDFUNDING_QUESTION_REMOVE_RECORD");?>" id="cf-hidden-question" />
<?php }?>