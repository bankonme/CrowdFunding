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

JLoader::register("CrowdFundingModelProject", CROWDFUNDING_PATH_COMPONENT_SITE . "/models/project.php");

class CrowdFundingModelStory extends CrowdFundingModelProject
{
    /**
     * Method to get the profile form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param    array   $data     An optional array of data for the form to interogate.
     * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return    JForm    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.story', 'story', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $data = $app->getUserState($this->option . '.edit.story.data', array());
        if (!$data) {

            $itemId = (int)$this->getState($this->getName() . '.id');
            $userId = JFactory::getUser()->get("id");

            $data = $this->getItem($itemId, $userId);

        }

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param    array    $data    The form data.
     *
     * @return    mixed        The record id on success, null on failure.
     * @since    1.6
     */
    public function save($data)
    {
        $id          = JArrayHelper::getValue($data, "id");
        $description = JArrayHelper::getValue($data, "description");

        $keys = array(
            "id" => $id,
            "user_id" => JFactory::getUser()->get("id"),
        );

        // Load a record from the database.
        /** @var $row CrowdFundingTableProject */
        $row = $this->getTable();
        $row->load($keys);

        $row->set("description", $description);

        $this->prepareTable($row, $data);

        $row->store();

        // Trigger the event onContentAfterSave.
        $this->triggerEventAfterSave($row, "story");

        return $row->get("id");
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @since    1.6
     */
    protected function prepareTable(&$table, $data)
    {
        // Prepare the video
        $pitchVideo = JArrayHelper::getValue($data, "pitch_video");
        $table->set("pitch_video", $pitchVideo);

        // Prepare the image.
        if (!empty($data["pitch_image"])) {

            // Delete old image if I upload a new one.
            if (!empty($table->pitch_image)) {

                $params       = JComponentHelper::getParams($this->option);
                /** @var  $params Joomla\Registry\Registry */

                $imagesFolder = $params->get("images_directory", "images/crowdfunding");

                // Remove an image from the filesystem
                $pitchImage = JPAth::clean($imagesFolder . DIRECTORY_SEPARATOR . $table->pitch_image);

                if (is_file($pitchImage)) {
                    JFile::delete($pitchImage);
                }
            }

            $table->set("pitch_image", $data["pitch_image"]);
        }
    }


    /**
     * Upload an image
     *
     * @param  array $image
     *
     * @throws Exception
     * @return array
     */
    public function uploadImage($image)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $uploadedFile = JArrayHelper::getValue($image, 'tmp_name');
        $uploadedName = JArrayHelper::getValue($image, 'name');
        $errorCode    = JArrayHelper::getValue($image, 'error');

        // Load parameters.
        $params     = JComponentHelper::getParams($this->option);
        /** @var  $params Joomla\Registry\Registry */

        $destFolder = $params->get("images_directory", "images/crowdfunding");

        $tmpFolder = $app->get("tmp_path");

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Joomla\Registry\Registry */

        jimport("itprism.file");
        jimport("itprism.file.uploader.local");
        jimport("itprism.file.validator.size");
        jimport("itprism.file.validator.image");
        jimport("itprism.file.validator.server");

        $file = new ITPrismFile();

        // Prepare size validator.
        $KB            = 1024 * 1024;
        $fileSize      = (int)$app->input->server->get('CONTENT_LENGTH');
        $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

        $sizeValidator = new ITPrismFileValidatorSize($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new ITPrismFileValidatorServer($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $imageValidator = new ITPrismFileValidatorImage($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $mimeTypes = explode(",", $mediaParams->get("upload_mime"));
        $imageValidator->setMimeTypes($mimeTypes);

        // Get allowed image extensions from media manager options
        $imageExtensions = explode(",", $mediaParams->get("image_extensions"));
        $imageValidator->setImageExtensions($imageExtensions);

        $file
            ->addValidator($sizeValidator)
            ->addValidator($imageValidator)
            ->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new RuntimeException($file->getError());
        }

        // Generate temporary file name
        $ext = JString::strtolower(JFile::makeSafe(JFile::getExt($image['name'])));

        jimport("itprism.string");
        $generatedName = new ITPrismString();
        $generatedName->generateRandomString(32);

        $tmpDestFile = $tmpFolder . DIRECTORY_SEPARATOR . $generatedName . "." . $ext;

        // Prepare uploader object.
        $uploader = new ITPrismFileUploaderLocal($uploadedFile);
        $uploader->setDestination($tmpDestFile);

        // Upload temporary file
        $file->setUploader($uploader);

        $file->upload();

        // Get file
        $tmpDestFile = $file->getFile();

        if (!is_file($tmpDestFile)) {
            throw new Exception('COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED');
        }

        // Resize image
        $image = new JImage();
        $image->loadFile($tmpDestFile);
        if (!$image->isLoaded()) {
            throw new Exception(JText::sprintf('COM_CROWDFUNDING_ERROR_FILE_NOT_FOUND', $tmpDestFile));
        }

        $imageName = $generatedName . "_pimage.png";
        $imageFile = $destFolder . DIRECTORY_SEPARATOR . $imageName;

        // Create main image
        $width  = $params->get("pitch_image_width", 600);
        $height = $params->get("pitch_image_height", 400);
        $image->resize($width, $height, false);
        $image->toFile($imageFile, IMAGETYPE_PNG);

        // Remove the temporary file.
        if (is_file($tmpDestFile)) {
            JFile::delete($tmpDestFile);
        }

        return $imageName;
    }

    /**
     * Delete pitch image.
     *
     * @param integer $id Item id
     * @param integer $userId User id
     */
    public function removeImage($id, $userId)
    {
        $keys = array(
            "id" => $id,
            "user_id" => $userId
        );

        // Load category data
        $row = $this->getTable();
        $row->load($keys);

        // Delete old image if I upload the new one
        if ($row->get("pitch_image")) {
            jimport('joomla.filesystem.file');

            $params       = JComponentHelper::getParams($this->option);
            /** @var  $params Joomla\Registry\Registry */

            $imagesFolder = $params->get("images_directory", "images/crowdfunding");

            // Remove an image from the filesystem
            $pitchImage = JPath::clean($imagesFolder . DIRECTORY_SEPARATOR . $row->get("pitch_image"));

            if (is_file($pitchImage)) {
                JFile::delete($pitchImage);
            }
        }

        $row->set("pitch_image", "");
        $row->store();
    }

    public function uploadExtraImages($files, $options)
    {
        $images      = array();
        $destination = JArrayHelper::getValue($options, "destination", "images/crowdfunding");

        jimport("itprism.file");
        jimport("itprism.file.image");
        jimport("itprism.file.uploader.local");
        jimport("itprism.file.validator.size");
        jimport("itprism.file.validator.image");
        jimport("itprism.file.validator.server");
        jimport("itprism.string");

        // Joomla! media extension parameters
        $mediaParams = JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Joomla\Registry\Registry */

        // check for error
        foreach ($files as $image) {

            // Upload image
            if (!empty($image['name'])) {

                $uploadedFile = JArrayHelper::getValue($image, 'tmp_name');
                $uploadedName = JArrayHelper::getValue($image, 'name');
                $errorCode    = JArrayHelper::getValue($image, 'error');

                $file = new ITPrismFile();

                // Prepare size validator.
                $KB            = 1024 * 1024;
                $fileSize      = JArrayHelper::getValue($image, "size");
                $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

                // Prepare file size validator
                $sizeValidator = new ITPrismFileValidatorSize($fileSize, $uploadMaxSize);

                // Prepare server validator.
                $serverValidator = new ITPrismFileValidatorServer($errorCode, array(UPLOAD_ERR_NO_FILE));

                // Prepare image validator.
                $imageValidator = new ITPrismFileValidatorImage($uploadedFile, $uploadedName);

                // Get allowed mime types from media manager options
                $mimeTypes = explode(",", $mediaParams->get("upload_mime"));
                $imageValidator->setMimeTypes($mimeTypes);

                // Get allowed image extensions from media manager options
                $imageExtensions = explode(",", $mediaParams->get("image_extensions"));
                $imageValidator->setImageExtensions($imageExtensions);

                $file
                    ->addValidator($sizeValidator)
                    ->addValidator($imageValidator)
                    ->addValidator($serverValidator);

                // Validate the file
                if (!$file->isValid()) {
                    throw new RuntimeException($file->getError());
                }

                // Generate file name
                $ext = JString::strtolower(JFile::makeSafe(JFile::getExt($image['name'])));

                $generatedName = new ITPrismString();
                $generatedName->generateRandomString(6);

                $tmpDestFile = $destination . DIRECTORY_SEPARATOR . $generatedName . "_extra." . $ext;

                // Prepare uploader object.
                $uploader = new ITPrismFileUploaderLocal($uploadedFile);
                $uploader->setDestination($tmpDestFile);

                // Upload temporary file
                $file->setUploader($uploader);

                $file->upload();

                // Get file
                $imageSource = $file->getFile();

                if (!JFile::exists($imageSource)) {
                    throw new RuntimeException(JText::_("COM_CROWDFUNDING_ERROR_FILE_CANT_BE_UPLOADED"));
                }

                // Create thumbnail
                $fileImage              = new ITPrismFileImage($imageSource);
                $options["destination"] = $destination . DIRECTORY_SEPARATOR . $generatedName . "_extra_thumb." . $ext;
                $thumbSource            = $fileImage->createThumbnail($options);

                $names          = array("image" => "", "thumb" => "");
                $names['image'] = basename($imageSource);
                $names["thumb"] = basename($thumbSource);

                $images[] = $names;
            }
        }

        return $images;
    }

    /**
     * Save additional images to the project.
     *
     * @param array $images
     * @param int $projectId
     * @param string $imagesUri
     *
     * @return array
     */
    public function storeExtraImage($images, $projectId, $imagesUri)
    {
        settype($images, "array");
        settype($projectId, "integer");
        $result = array();

        if (!empty($images) and !empty($projectId)) {

            $image = array_shift($images);

            $db = JFactory::getDbo();
            /** @var $db JDatabaseMySQLi * */

            $query = $db->getQuery(true);
            $query
                ->insert($db->quoteName("#__crowdf_images"))
                ->set($db->quoteName("image") . "=" . $db->quote($image["image"]))
                ->set($db->quoteName("thumb") . "=" . $db->quote($image["thumb"]))
                ->set($db->quoteName("project_id") . "=" . (int)$projectId);

            $db->setQuery($query);
            $db->execute();

            $lastId = $db->insertid();

            // Add URI path to images
            $result = array(
                "id"    => $lastId,
                "image" => $imagesUri . "/" . $image["image"],
                "thumb" => $imagesUri . "/" . $image["thumb"]
            );

        }

        return $result;
    }

    /**
     * Only delete an additional image.
     *
     * @param integer $imageId Image ID
     * @param string  $imagesFolder A path to the images folder.
     * @param integer  $userId
     *
     * @throws RuntimeException
     */
    public function removeExtraImage($imageId, $imagesFolder, $userId)
    {
        jimport("itprism.file.image");
        jimport("itprism.file.remover.local");
        jimport("crowdfunding.image.validator.owner");
        jimport("crowdfunding.image.remover.extra");

        $file = new ITPrismFileImage();

        // Validate owner of the project.
        $ownerValidator = new CrowdFundingImageValidatorOwner(JFactory::getDbo(), $imageId, $userId);
        if (!$ownerValidator->isValid()) {
            throw new RuntimeException(JText::_("COM_CROWDFUNDING_INVALID_PROJECT"));
        }

        // Remove the image.
        $remover = new CrowdFundingImageRemoverExtra(JFactory::getDbo(), $imageId, $imagesFolder);
        $file->addRemover($remover);

        $file->remove();
    }
}
