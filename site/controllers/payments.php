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
 * that helps to payment plugins to prepare their payment data.
 *
 * @package        CrowdFunding
 * @subpackage     Payments
 *
 */
class CrowdFundingControllerPayments extends JControllerLegacy
{
    protected $log;

    protected $paymentProcessContext;
    protected $paymentProcess;

    protected $projectId;
    protected $currency;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite * */

        // Get project id.
        $this->projectId = $this->input->getUint("pid");

        // Prepare log object
        $registry = JRegistry::getInstance("com_crowdfunding");
        /** @var  $registry Joomla\Registry\Registry */

        $fileName  = $registry->get("logger.file");
        $tableName = $registry->get("logger.table");

        $file = JPath::clean(JFactory::getApplication()->get("log_path") . DIRECTORY_SEPARATOR . $fileName);

        $this->log = new ITPrismLog();
        $this->log->addWriter(new ITPrismLogWriterDatabase(JFactory::getDbo(), $tableName));
        $this->log->addWriter(new ITPrismLogWriterFile($file));

        // Create an object that contains a data used during the payment process.
        $this->paymentProcessContext = CrowdFundingConstants::PAYMENT_SESSION_CONTEXT . $this->projectId;
        $this->paymentProcess        = $app->getUserState($this->paymentProcessContext);

    }

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

    public function checkout()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get component parameters
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Check for disabled payment functionality
        if ($params->get("debug_payment_disabled", 0)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE"));
        }

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $output = array();

        // Get payment gateway name.
        $paymentService = $this->input->get("payment_service");
        if (!$paymentService) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PAYMENT_GATEWAY"));
        }

        // Set the name of the payment service to session.
        $this->paymentProcess->paymentService = $paymentService;

        // Trigger the event
        try {

            $item = $this->prepareItem($this->projectId, $params);

            $context = 'com_crowdfunding.payments.checkout.' . JString::strtolower($paymentService);

            // Import CrowdFunding Payment Plugins
            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onContentPreparePayment event.
            $results = $dispatcher->trigger("onPaymentsCheckout", array($context, &$item, &$params));

            // Get the result, that comes from the plugin.
            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!is_null($result) and is_array($result)) {
                        $output = & $result;
                        break;
                    }
                }
            }

        } catch (UnexpectedValueException $e) {

            $this->setMessage($e->getMessage(), "notice");
            $this->setRedirect(JRoute::_(CrowdFundingHelperRoute::getDiscoverRoute(), false));

            return;

        } catch (Exception $e) {

            // Store log data in the database
            $this->log->add(
                JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"),
                "CONTROLLER_PAYMENTS_CHECKOUT_ERROR",
                $e->getMessage()
            );

            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"));

        }

        $redirectUrl = JArrayHelper::getValue($output, "redirect_url");
        if (!$redirectUrl) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REDIRECT_URL"));
        }

        // Store the name of the payment service to session.
        $app->setUserState($this->paymentProcessContext, $this->paymentProcess);

        $this->setRedirect($redirectUrl);

    }

    public function docheckout()
    {
        // Get component parameters
        $params = JComponentHelper::getParams("com_crowdfunding");
        /** @var  $params Joomla\Registry\Registry */

        // Check for disabled payment functionality
        if ($params->get("debug_payment_disabled", 0)) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_PAYMENT_HAS_BEEN_DISABLED_MESSAGE"));
        }

        $output = array();

        // Get the name of the payment service.
        $paymentService = $this->paymentProcess->paymentService;

        // Trigger the event
        try {

            // Create project object.
            $item = $this->prepareItem($this->projectId, $params);

            $context = 'com_crowdfunding.payments.docheckout.' . JString::strtolower($paymentService);

            // Import CrowdFunding Payment Plugins
            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('crowdfundingpayment');

            // Trigger onContentPreparePayment event.
            $results = $dispatcher->trigger("onPaymentsDoCheckout", array($context, &$item, &$params));

            // Get the result, that comes from the plugin.
            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!is_null($result) and is_array($result)) {
                        $output = & $result;
                        break;
                    }
                }
            }

        } catch (UnexpectedValueException $e) {

            $this->setMessage($e->getMessage(), "notice");
            $this->setRedirect(JRoute::_(CrowdFundingHelperRoute::getDiscoverRoute(), false));

            return;

        } catch (Exception $e) {

            // Store log data in the database
            $this->log->add(
                JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"),
                "CONTROLLER_PAYMENTS_DOCHECKOUT_ERROR",
                $e->getMessage()
            );

            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_SYSTEM"));

        }

        $redirectUrl = JArrayHelper::getValue($output, "redirect_url");
        if (!$redirectUrl) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_REDIRECT_URL"));
        }

        $this->setRedirect($redirectUrl);

    }

    /**
     * @param int $projectId
     * @param Joomla\Registry\Registry $params
     *
     * @return stdClass
     * @throws UnexpectedValueException
     */
    protected function prepareItem($projectId, $params)
    {
        jimport("crowdfunding.project");
        $project = new CrowdFundingProject(JFactory::getDbo());
        $project->load($projectId);

        if (!$project->getId()) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"));
        }

        if ($project->isCompleted()) {
            throw new UnexpectedValueException(JText::_("COM_CROWDFUNDING_ERROR_COMPLETED_PROJECT"));
        }

        // Get currency
        jimport("crowdfunding.currency");
        $currencyId     = $params->get("project_currency");
        $this->currency = CrowdFundingCurrency::getInstance(JFactory::getDbo(), $currencyId, $params);

        $item = new stdClass();

        $item->id       = $project->getId();
        $item->title    = $project->getTitle();
        $item->slug     = $project->getSlug();
        $item->catslug  = $project->getCatSlug();
        $item->rewardId = $this->paymentProcess->rewardId;
        $item->amount   = $this->paymentProcess->amount;
        $item->currency = $this->currency->getAbbr();

        return $item;
    }
}
