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
<div class="cfbacking<?php echo $this->params->get("pageclass_sfx"); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>
	
	<div class="row-fluid">
		<div class="span12">
    		<?php 
    		  if(strcmp("three_steps", $this->wizardType) == 0) {
        		$layout      = new JLayoutFile('payment_wizard', $this->layoutsBasePath);
    		  } else {
        		$layout      = new JLayoutFile('payment_wizard_four_steps', $this->layoutsBasePath);
    		  }
        	  echo $layout->render($this->layoutData);
    		?>	
    	</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			
			<form method="post" action="<?php echo JRoute::_(CrowdFundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug));?>" class="bs-docs-example cfbf" id="form-pledge" autocomplete="off">
				<fieldset>
    				<legend><?php echo JText::_("COM_CROWDFUNDING_ENTER_YOUR_INVESTMENT_AMOUNT");?></legend>
    				<?php echo JHtml::_("crowdfunding.inputAmount", $this->rewardAmount, $this->currency, array("name"=>"amount", "id"=>"js-current-amount")); ?>
    				<?php 
    				if($this->params->get("backing_terms", 0)) {
    				    $termsUrl = $this->params->get("backing_terms_url", "");
    				?>
    				<label class="checkbox">
                    	<input type="checkbox" name="terms" value="1">&nbsp;
                        <?php echo (!$termsUrl) ? JText::_("COM_CROWDFUNDING_TERMS_AGREEMENT") : JText::sprintf("COM_CROWDFUNDING_TERMS_AGREEMENT_URL", $termsUrl);?>
                    </label>
                    <?php }?>
    				<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
    				<input type="hidden" name="rid" value="<?php echo $this->rewardId; ?>" id="js-reward-id" />
    				<input type="hidden" name="task" value="<?php echo $this->secondStepTask; ?>" />
    				<?php echo JHtml::_('form.token'); ?>
    				
    				<button type="submit" class="button button-large" <?php echo $this->disabledButton;?>>
                        <?php echo JText::_("COM_CROWDFUNDING_CONTINUE");?>
                    </button>
    				
                </fieldset>
            </form>
			
			<?php if($this->rewardsEnabled) {?>
			<div class="cfrewards">
			    <div class="reward_title pull-center"><?php echo JText::_("COM_CROWDFUNDING_REWARDS");?></div>
			
            	<div class="reward">
            		<a href="javascript: void(0);" class="js-reward-amount" >
            			<span class="ramount">
            			<input type="radio" name="reward" value="0" data-id="0" class="js-reward-amount-radio" <?php echo (!$this->rewardId) ? 'checked="checked"' : ""; ?>/>
            			<?php echo JText::_("COM_CROWDFUNDING_NO_REWARD"); ?>
            			</span>
            			<span class="rdesc"><?php echo JText::_("COM_CROWDFUNDING_JUST_INVEST"); ?></span>
            		</a>
            	</div>
            	<?php foreach($this->rewards as $reward) {?>
            	<div class="reward">
            		<a href="javascript: void(0);" class="js-reward-amount" >
            			<span class="ramount">
            			<input type="radio" name="reward" value="<?php echo $reward->amount;?>" data-id="<?php echo $reward->id;?>" class="js-reward-amount-radio" <?php echo ($this->rewardId != $reward->id) ? "" : 'checked="checked"'?>/>
            			<?php 
            			$amount = $this->currency->getAmountString($reward->amount);
            			echo JText::sprintf("COM_CROWDFUNDING_INVEST_MORE", $amount ); ?>
            			</span>
            			<span class="rtitle"><?php echo $this->escape($reward->title); ?></span>
            			<span class="rdesc"><?php echo $this->escape($reward->description); ?></span>
            		</a>
            	</div>
            	<?php }?>
            </div>
            <?php } ?>
    	</div>
    	
	</div>
</div>
<div class="clearfix">&nbsp;</div>
<?php echo $this->version->backlink;?>