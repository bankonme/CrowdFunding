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
 * CrowdFunding comment controller
 *
 * @package     ITPrism Components
 * @subpackage  CrowdFunding
 */
class CrowdFundingControllerComment extends JControllerLegacy
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
    public function getModel($name = 'CommentItem', $prefix = 'CrowdFundingModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Method to load data via AJAX
     */
    public function getData()
    {
        // Get the input
        $app    = JFactory::getApplication();
        $itemId = $app->input->get('id', 0, 'int');
        $userId = JFactory::getUser()->id;

        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdFundingModelCommentItem * */

        try {

            $item = $model->getItem($itemId);

            if ($item->user_id != $userId) {
                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setText(JText::_('COM_CROWDFUNDING_INVALID_PROJECT'))
                    ->failure();

                echo $response;
                JFactory::getApplication()->close();
            }

        } catch (Exception $e) {

            JLog::add($e->getMessage());
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $data = array(
            "id"      => $item->id,
            "comment" => $item->comment
        );

        $response
            ->setData($data)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    /**
     * Method to remove records via AJAX.
     *
     * @throws Exception
     * @return  void
     */
    public function remove()
    {
        // Get the input
        $app    = JFactory::getApplication();
        $itemId = $app->input->post->get('id', 0, 'int');
        $userId = JFactory::getUser()->get("id");

        jimport("itprism.response.json");
        $response = new ITPrismResponseJson();

        // Get the model
        $model = $this->getModel();
        /** @var $model CrowdFundingModelCommentItem */

        try {

            $item = $model->getItem($itemId);

            if ($item->user_id != $userId) {
                $response
                    ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                    ->setText(JText::_('COM_CROWDFUNDING_COMMENT_CANNOT_REMOVED'))
                    ->failure();

                echo $response;
                JFactory::getApplication()->close();
            }

            $model->remove($itemId, $userId);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            $response
                ->setTitle(JText::_('COM_CROWDFUNDING_FAIL'))
                ->setText(JText::_('COM_CROWDFUNDING_ERROR_SYSTEM'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        $response
            ->setTitle(JText::_("COM_CROWDFUNDING_SUCCESS"))
            ->setText(JText::_("COM_CROWDFUNDING_COMMENT_REMOVED_SUCCESSFULLY"))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }
}
