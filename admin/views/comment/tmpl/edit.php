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
<div class="row-fluid">
    <div class="span6 form-horizontal">
        <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="adminForm"
              id="adminForm" class="form-validate">

            <fieldset>

                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('published'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('project_id'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('project_id'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('comment'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('comment'); ?></div>
                </div>

            </fieldset>

            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>