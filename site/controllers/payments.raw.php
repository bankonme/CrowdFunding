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

jimport('joomla.application.component.controller');

/**
 * This controller provides functionality
 * that helps to payment plugins to prepare their data.
 *
 * @package        CrowdFunding
 * @subpackage     Payments
 */
class CrowdFundingControllerPayments extends JControllerLegacy
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param    string $name   The model name. Optional.
     * @param    string $prefix The class prefix. Optional.
     * @param    array  $config Configuration array for model. Optional.
     *
     * @return    object    The model.
     * @since    1.5
     */
    public function getModel($name = 'Payments', $prefix = '', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * This method trigger the event onPaymentsPreparePayment.
     * The purpose of this method is to load a data and send it to browser.
     * That data will be used in the process of payment.
     */
    public function preparePaymentAjax()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Get component parameters
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        jimport('itprism.response.json');
        $response = new ITPrismResponseJson();

        // Check for disabled payment functionality
        if ($params->get("debug_payment_disabled", 0)) {

            // Send response to the browser
            $response
                ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE"))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $output         = array();

        // Prepare payment service name.
        $filter         = new JFilterInput();
        $paymentService = JString::trim(JString::strtolower($this->input->getCmd("payment_service")));
        $paymentService = $filter->clean($paymentService, "ALNUM");

        // Trigger the event
        try {

            $context = 'com_crowdfunding.preparepayment.' . $paymentService;

            // Import CrowdFunding Payment Plugins
            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onContentPreparePayment event.
            $results = $dispatcher->trigger("onPaymentsPreparePayment", array($context, &$params));

            // Get the result, that comes from the plugin.
            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!is_null($result) and is_array($result)) {
                        $output = & $result;
                        break;
                    }
                }
            }

        } catch (Exception $e) {

            // Store log data in the database
            JLog::add($e->getMessage());

            // Send response to the browser
            $response
                ->failure()
                ->setTitle(JText::_("COM_CROWDFUNDING_FAIL"))
                ->setText(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"));

            echo $response;
            JFactory::getApplication()->close();

        }

        // Check the response
        $success = JArrayHelper::getValue($output, "success");
        if (!$success) { // If there is an error...

            // Get project id.
            $projectId = $this->input->getUint("pid");
            $paymentProcessContext = CrowdFundingConstants::PAYMENT_SESSION_CONTEXT . $projectId;

            // Initialize the payment process object.
            $paymentProcess        = new JData();
            $paymentProcess->step1 = false;
            $app->setUserState($paymentProcessContext, $paymentProcess);

            // Send response to the browser
            $response
                ->failure()
                ->setTitle(JArrayHelper::getValue($output, "title"))
                ->setText(JArrayHelper::getValue($output, "text"));

        } else { // If all is OK...

            // Send response to the browser
            $response
                ->success()
                ->setTitle(JArrayHelper::getValue($output, "title"))
                ->setText(JArrayHelper::getValue($output, "text"))
                ->setData(JArrayHelper::getValue($output, "data"));

        }

        echo $response;
        JFactory::getApplication()->close();
    }
}
