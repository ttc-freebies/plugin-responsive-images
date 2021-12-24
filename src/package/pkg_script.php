<?php
/**
 * @package     ttc-freebies.plugin-responsive-images
 *
 * @copyright   Copyright (C) 2017 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;

class pkg_ResponsiveInstallerScript extends \Joomla\CMS\Installer\InstallerScript
{
  public function __construct()
  {
    $this->minimumJoomla = '4.0';
    $this->minimumPhp    = JOOMLA_MINIMUM_PHP;
    $this->deleteFolders = ['layouts/ttc'];
  }

  public function install(PackageAdapter $parent)
  {
    $parentInstance = $parent->getParent()->getInstance();
    $paths = $parentInstance->get('paths');
    if (is_file($paths['source'] . '/image.php')) {
      if (!is_dir(JPATH_ROOT . '/layouts/ttc')) {
        mkdir(JPATH_ROOT . '/layouts/ttc');
      }
      File::copy($paths['source'] . '/image.php', JPATH_ROOT . '/layouts/ttc/image.php');
    }
  }

  public function postflight($type, $parent)
  {
    if ($type === 'install' || $type === 'discover_install') {
      $db = Factory::getDbo();

      $db = Factory::getDbo();

      $list = [
        0 => [
          'enabled' => 1,
          'type'    => 'library',
          'element' => 'Ttc',
        ],
        1 => [
          'enabled' => 1,
          'type'    => 'plugin',
          'folder'  => 'content',
          'element' => 'responsive',
        ],
        2 => [
          'enabled' => 1,
          'type'    => 'plugin',
          'folder'  => 'media-action',
          'element' => 'responsive',
        ],
      ];

      foreach ($list as $a => $options) {
        $query = $db->getQuery(true)
        ->update('#__extensions')
        ->set($db->qn('enabled') . ' = ' . (int) $options['enabled'])
        ->where($db->qn('type') . ' = ' . $db->q($options['type']))
        ->where($db->qn('element') . ' = ' . $db->q($options['element']));

        switch ($options['type']) {
          case 'plugin':
            $query->where($db->qn('folder') . ' = ' . $db->q($options['folder']));
            break;
          case 'language':
          case 'module':
          case 'template':
            $query->where($db->qn('client_id') . ' = ' . (int) $options['client_id']);
            break;
          default:
          case 'library':
          case 'package':
          case 'component':
            break;
        }

        $db->setQuery($query);
        try {
          $db->execute();
        } catch (\Exception $e) {
          // var_dump($e);
        }
      }

      if (is_dir(JPATH_ROOT . '/plugins/content/responsive/vendor')) {
        self::deleteDir(JPATH_ROOT . '/plugins/content/vendor');
      }
      if (is_file(JPATH_ROOT . '/plugins/content/responsive/helper.php')) {
        @unlink(JPATH_ROOT . '/plugins/content/responsive/helper.php');
      }
      if (is_file(JPATH_ROOT . '/plugins/content/responsive/script.php')) {
        @unlink(JPATH_ROOT . '/plugins/content/responsive/script.php');
      }
    }
  }

  /**
   * Called on uninstallation
   *
   * @param  PackageAdapter  $adapter  The object responsible for running this script
   */
  public function uninstall(PackageAdapter $adapter)
  {
    $cleanup = (bool)($this->getParameter('params', 'cleanup') == '1');

    if ($cleanup === true) {
      // user decided to cleanup on uninstall!
      $this->deleteFolders[] = '/media/cached-resp-images';
    } elseif (Folder::exists(JPATH_ROOT . '/media/cached-resp-images')) {
      $error = "<p><strong>Responsive content images</strong><br />This plugin has created the folder '/media/cached-resp-images' to store the generated images. To save web space you could delete this folder if you do not need this files. If you reinstall this plug in, it will create the folder and the needed images again.</p>";
      Log::add($error, Log::WARNING, 'jerror');
    }
    $this->removeFiles();
  }

  /**
   *  Method to fetch from the db
   *
   *  @param string  $cacheName  defined the cache who is used
   *  @param string  $paramName  whiche value is needed
   *
   *  @return string
   */
  public function getParameter($cacheName, $paramName)
  {
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

  private static function deleteDir($dirPath)
  {
    if (!is_dir($dirPath)) {
      throw new \InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
      $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
      if (is_dir($file)) {
        self::deleteDir($file);
      } else {
        unlink($file);
      }
    }
    rmdir($dirPath);
  }
}
