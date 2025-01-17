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

if (strcmp("five_steps", $this->wizardType) == 0) {
    $layout      = new JLayoutFile('project_wizard', $this->layoutsBasePath);
} else {
    $layout      = new JLayoutFile('project_wizard_six_steps', $this->layoutsBasePath);
}
echo $layout->render($this->layoutData);
?>

<?php
if (!empty($this->item->event->onExtrasDisplay)) {
    echo $this->item->event->onExtrasDisplay;
}
?>

<div class="row-fluid">
    <div class="span12">
        <a class="btn" <?php echo $this->disabledButton;?> href="<?php echo JRoute::_("index.php?option=com_crowdfunding&view=project&layout=manager&id=".(int)$this->item->id); ?>">
            <i class="icon-ok icon-white"></i>
            <?php echo JText::_("COM_CROWDFUNDING_CONTINUE_NEXT_STEP");?>
        </a>
    </div>
</div>
<div class="clearfix">&nbsp;</div>
<?php echo $this->version->backlink;?>