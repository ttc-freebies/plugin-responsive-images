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

class PlgContentResponsiveInstallerScript  extends \Joomla\CMS\Installer\InstallerScript {
  public function __construct() {
    $this->minimumJoomla = '3.9';
    $this->minimumPhp    = JOOMLA_MINIMUM_PHP;
    $this->deleteFolders = array(
      '/media/cached-resp-images',
    );
  }

  /**
   * Called before any type of action
   *
   * @param   string  $type    Which action is happening (install|uninstall|discover_install|update)
   * @param   object  $adapter  The object responsible for running this script
   *
   * @return  boolean  True on success
   */
  public function preflight($type, $adapter) {
    if (strtolower($type) == 'update') {
      // Installed plug in version
      $oldRelease = $this->getParameter('manifest_cache', 'version');
      // Installing plug in version as per manifest file
      $newRelease = strval($adapter->manifest->version);

      $doCleanup = (bool)((version_compare($oldRelease, '3.0.0', '<=') && version_compare($newRelease, '3.0.0', '>')) && function_exists('imagewebp'));

      if ($doCleanup === true) {
        // Info message to remove generated folder, because the file name syntax is new
        $error = "<p><strong>Responsive content images</strong><br />Please delete the folder '/media/cached-resp-images' to finish the update process and start serving WebP images!</p>";
        Log::add($error, Log::WARNING, 'jerror');
      }
    }
  }

  /**
   * Called on uninstallation
   *
   * @param  JAdapterInstance  $adapter  The object responsible for running this script
   */
  public function uninstall(JAdapterInstance $adapter) {
    $cleanup = (bool)($this->getParameter('params', 'cleanup') == '1');

    if ($cleanup===true) {
      // user decided to cleanup on uninstall!
      $this->removeFiles();
    } elseif (Folder::exists(JPATH_ROOT . '/media/cached-resp-images')) {
      $error = "<p><strong>Responsive content images</strong><br />This plugin has created the folder '/media/cached-resp-images' to store the generated images. To save web space you could delete this folder if you do not need this files. If you reinstall this plug in, it will create the folder and the needed images again.</p>";
      Log::add($error, Log::WARNING, 'jerror');
    }
  }

  /**
   *  Method to fetch from the db
   *
   *  @param string  $cacheName  defined the cache who is used
   *  @param string  $paramName  whiche value is needed
   *
   *  @return string
   */
  public function getParameter($cacheName, $paramName) {
    $db = Factory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select($cacheName)
      ->from('#__extensions')
      ->where(array("type = 'plugin'", "folder = 'content'", "element = 'responsive'"));
    $db->setQuery($query);

    $cache = json_decode($db->loadResult(), true);

    return $cache[$paramName];
  }
}
