<?php
/**
 * @package     ttc-freebies.plugin-responsive-images
 *
 * @copyright   Copyright (C) 2020 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Log\Log;

class PlgContentResponsiveInstallerScript {
  /**
   *  @brief method to uninstall the component
   *
   *  @param [in] $parent
   *  @return void
   */
  function uninstall($parent) {
    $cleanup = (bool)($this->getParam('params', 'cleanup') == '1');
    if ($cleanup===true) {
      // user decided to cleanup on uninstall!
      $errorMsg = "<p><strong>Responsive content images</strong><br />The uninstall process cannot delete the generated folder. Please delete the folder '/media/cached-resp-images' by your own to complete the uninstallation.</p>";
      $this->deleteGeneratedFiles($errorMsg);
    } elseif (Folder::exists(JPATH_ROOT . '/media/cached-resp-images')) {
      $error = "<p><strong>Responsive content images</strong><br />This plugin have created the folder '/media/cached-resp-images' to store the generated images. To save web space you could delete this folder if you do not need this files. If you reinstall this plug in, it will create the folder and the needed images again.</p>";
      Log::add($error, Log::WARNING, 'jerror');
    }
  }

  /**
   *  @brief method to run before an install/update/uninstall method
   *
   *  @param [in] $type
   *  @param [in] $parent
   *  @return void
   */
  function preflight($type, $parent) {
    if (strtolower($type) == 'update') {
      // Installed plug in version
      $oldRelease = $this->getParam('manifest_cache', 'version');
      // Installing plug in version as per manifest file
      $newRelease = $parent->get('manifest')->version;
      $doCleanup = (bool)(version_compare($oldRelease, '1.0.0', '<=') && version_compare($newRelease, '1.0.0', '>'));
      $doCleanup2 = (bool)((version_compare($oldRelease, '3.0.0', '<=') && version_compare($newRelease, '3.0.0', '>')) && function_exists('imagewebp'));

      // delete available images if installed version is <= 1.0.0 and new version is > 1.0.0
      if ($doCleanup === true) {
        // Info message to remove generated folder, because the file name syntax is new
        $error = "<p><strong>Responsive content images</strong><br />Please delete the folder '/media/cached-resp-images' to finish the update process, because the file name syntax is new!</p>";
        Log::add($error, Log::WARNING, 'jerror');
      }

      // delete available images if installed version is <= 3.0.0 and new version is > 3.0.0 and there is WebP support
      if ($doCleanup2 === true) {
        // Info message to remove generated folder, because the file name syntax is new
        $error = "<p><strong>Responsive content images</strong><br />Please delete the folder '/media/cached-resp-images' to finish the update process and start serving WebP images!</p>";
        Log::add($error, Log::WARNING, 'jerror');
      }
    }
  }

  /**
   *  @brief Get value of a param
   *
   *  @param [in] $cacheName defined the cache who is used
   *  @param [in] $paramName whiche value is needed
   *  @return value of the specifiv param
   */
  private function getParam($cacheName, $paramName) {
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select($cacheName)->from('#__extensions')->where(array("type = 'plugin'", "folder = 'content'", "element = 'responsive'"));
    $db->setQuery($query);
    $cache = json_decode($db->loadResult(), true);
    return $cache[$paramName];
  }

  /**
   *  @brief Remove folder(s)
   *
   *  @return void
   *
   *  @details The folder used for the cached images will be deleted
   */
  private function deleteGeneratedFiles($errorMsg = '') {
    $folder = '/media/cached-resp-images';

    if ($errorMsg = ''){
      $errorMsg = "The folder '" . $folder . "' could not be deleted!<br />";
    }

    if (Folder::exists(JPATH_ROOT . $folder) && !Folder::delete(JPATH_ROOT . $folder)) {
      Log::add($errorMsg, Log::WARNING, 'jerror');
    }
  }
}
