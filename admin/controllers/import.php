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

jimport('itprism.controller.form.backend');

/**
 * CrowdFunding import controller.
 *
 * @package      CrowdFunding
 * @subpackage   Components
 */
class CrowdFundingControllerImport extends ITPrismControllerFormBackend
{
    /**
     * Proxy for getModel.
     * @since   1.6
     */
    public function getModel($name = 'Import', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function currencies()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data = $this->input->post->get('jform', array(), 'array');
        $file = $this->input->files->get('jform', array(), 'array');
        $data = array_merge($data, $file);

        $redirectOptions = array(
            "view" => "currencies",
        );

        $model = $this->getModel();
        /** @var $model CrowdFundingModelImport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED"), 500);
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);

            return;
        }

        $fileData = JArrayHelper::getValue($data, "data");
        if (empty($fileData) or empty($fileData["name"])) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);
            return;
        }

        try {

            $filePath = $model->uploadFile($fileData, "currencies");

            $resetId   = JArrayHelper::getValue($data, "reset_id", false, "bool");
            $removeOld = JArrayHelper::getValue($data, "remove_old", false, "bool");
            if (!empty($removeOld)) {
                $model->removeAll("currencies");
            }
            $model->importCurrencies($filePath, $resetId);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_CURRENCIES_IMPORTED'), $redirectOptions);

    }

    public function locations()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data = $this->input->post->get('jform', array(), 'array');
        $file = $this->input->files->get('jform', array(), 'array');
        $data = array_merge($data, $file);

        $redirectOptions = array(
            "view" => "locations",
        );

        $model = $this->getModel();
        /** @var $model CrowdFundingModelImport */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED"), 500);
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);

            return;
        }

        $fileData = JArrayHelper::getValue($data, "data");
        if (empty($fileData) or empty($fileData["name"])) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);
            return;
        }

        try {

            $filePath = $model->uploadFile($fileData, "locations");

            $resetId       = JArrayHelper::getValue($data, "reset_id", false, "bool");
            $minPopulation = JArrayHelper::getValue($data, "minimum_population", 0, "int");

            $removeOld = JArrayHelper::getValue($data, "remove_old", false, "bool");
            if (!empty($removeOld)) {
                $model->removeAll("locations");
            }
            $model->importLocations($filePath, $resetId, $minPopulation);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_LOCATIONS_IMPORTED'), $redirectOptions);

    }

    public function countries()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data = $this->input->post->get('jform', array(), 'array');
        $file = $this->input->files->get('jform', array(), 'array');
        $data = array_merge($data, $file);

        $redirectOptions = array(
            "view" => "countries",
        );

        $model = $this->getModel();
        /** @var $model CrowdFundingModelImport * */

        $form = $model->getForm($data, false);
        /** @var $form JForm * */

        if (!$form) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED"), 500);
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);

            return;
        }

        $fileData = JArrayHelper::getValue($data, "data");
        if (empty($fileData) or empty($fileData["name"])) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);
            return;
        }

        try {

            $filePath = $model->uploadFile($fileData, "countries");

            $resetId   = JArrayHelper::getValue($data, "reset_id", false, "bool");
            $removeOld = JArrayHelper::getValue($data, "remove_old", false, "bool");
            if (!empty($removeOld)) {
                $model->removeAll("countries");
            }
            $model->importCountries($filePath, $resetId);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_COUNTRIES_IMPORTED'), $redirectOptions);

    }

    public function states()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data = $this->input->post->get('jform', array(), 'array');
        $file = $this->input->files->get('jform', array(), 'array');
        $data = array_merge($data, $file);

        $redirectOptions = array(
            "view" => "locations",
        );

        $model = $this->getModel();
        /** @var $model CrowdFundingModelImport * */

        $form = $model->getForm($data, false);
        /** @var $form JForm * */

        if (!$form) {
            throw new Exception(JText::_("COM_CROWDFUNDING_ERROR_FORM_CANNOT_BE_LOADED"), 500);
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);

            return;
        }

        $fileData = JArrayHelper::getValue($data, "data");
        if (empty($fileData) or empty($fileData["name"])) {
            $this->displayNotice(JText::_('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED'), $redirectOptions);

            return;
        }

        try {

            $filePath = $model->uploadFile($fileData, "states");

            $model->importStates($filePath);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_CROWDFUNDING_STATES_IMPORTED'), $redirectOptions);

    }

    public function cancel($key = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator * */

        $view = $app->getUserState("import.context", "currencies");

        // Redirect to locations if the view is "states".
        if (strcmp("states", $view) == 0) {
            $view = "locations";
        }

        $link = $this->defaultLink . "&view=" . $view;
        $this->setRedirect(JRoute::_($link, false));
    }
}
