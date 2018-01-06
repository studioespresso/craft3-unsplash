<?php
/**
 * Splashing Images plugin for Craft CMS 3.x
 *
 * unsplash.com integration for Craft 3
 *
 * @link      https://studioespresso.co
 * @copyright Copyright (c) 2017 Studio Espresso
 */

namespace studioespresso\splashingimages\controllers;

use craft\elements\Asset;
use craft\services\Path;
use studioespresso\splashingimages\services\UnsplashService;
use studioespresso\splashingimages\SplashingImages;

use Craft;
use craft\web\Controller;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Studio Espresso
 * @package   SplashingImages
 * @since     1.0.0
 */
class DownloadController extends Controller
{

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/splashing-images/default
     *
     * @return mixed
     */
    public function actionIndex()
    {
        if(!Craft::$app->request->isAjax) {
            return false;
        }

        $path = new Path();
        $dir = $path->getTempAssetUploadsPath() . '/unsplash/';
        if(!is_dir($dir)){ mkdir($dir); }

        $id = Craft::$app->request->post('id');
        $unplash = new UnsplashService();
        $payload = $unplash->registerDownload($id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $payload);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $picture = curl_exec($ch);
        curl_close($ch);


        $tmpImage = 'photo-' . rand() . '.jpg';
        $tempPath = $dir . $tmpImage;
        $saved = file_put_contents($tempPath, $picture);

        $assets = Craft::$app->getAssets();
        $folder = $assets->findFolder(['id' => 3]);

        $asset = new Asset();
        $asset->tempFilePath = $tempPath;
        $asset->filename = $tmpImage;
        $asset->newFolderId = $folder->id;
        $asset->volumeId = $folder->volumeId;
        $asset->avoidFilenameConflicts = true;
        $asset->setScenario(Asset::SCENARIO_CREATE);

        $result = Craft::$app->getElements()->saveElement($asset);
        if($result and file_exists($tempPath)) {
            unlink($tempPath);
        }
        if($result) {
            return true;
        } else {
            return false;
        }
        exit;

    }

}