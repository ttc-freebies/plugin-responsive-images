
<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\PluginAdapter;
use Joomla\CMS\Installer\InstallerScript;

class plgSystemResponsiveplgoverridesInstallerScript extends InstallerScript
{
  public function __construct()
  {
    $this->minimumJoomla = '4.0';
    $this->minimumPhp = JOOMLA_MINIMUM_PHP;
    $this->template = new stdClass();
    $this->files = [];
    /**{{replacement}}**/
    /** replacement should be like: $this->template->name = 'cassiopeia'; **/
  }

  public function postflight($type, PluginAdapter $parent)
  {
    if ($type === 'install' || $type === 'discover_install') {
      // Check if the plugin is installed and enabled
      if (\Joomla\CMS\Plugin\PluginHelper::isEnabled('content', 'responsive')) {
        // Load the library helper
        if (!class_exists('\Ttc\Freebies\Responsive\Helper') && is_dir(JPATH_LIBRARIES . '/Ttc')) {
          JLoader::registerNamespace('Ttc', JPATH_LIBRARIES . '/Ttc');
        }

        if (class_exists('\Ttc\Freebies\Responsive\Helper')) {
          if (!empty($this->template->name)) {
            $templateName = strtolower($this->template->name);
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
              ->select('*')
              ->from('#__extensions')
              ->where('type = ' . $db->q('template'))
              ->where('name = ' . $db->q($templateName))
              ->where('client_id = ' . 0);

            $db->setQuery($query);
            try {
              $templateObj = $db->loadColumn();
            } catch (\Exception $e) {
            }

            if (!empty($templateObj) && is_dir(JPATH_ROOT . '/templates/' . $templateName)) {
              if (!is_dir(JPATH_ROOT . '/templates/' . $templateName . '/html')) {
                @mkdir(JPATH_ROOT . '/templates/' . $templateName . '/html');
              }
              if (!is_dir(JPATH_ROOT . '/templates/' . $templateName . '/html')) {
                return;
              }

              $this->scanFiles(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/html');
              foreach ($this->files as $file) {
                if (is_file(JPATH_ROOT . '/templates/' . $templateName . $file)) {
                  @rename(JPATH_ROOT . '/templates/' . $templateName . '/' . $file, JPATH_ROOT . '/templates/' . $templateName . '/' . $file . '.bak');
                  if (!is_file(JPATH_ROOT . '/templates/' . $templateName . '/' . $file . '.bak')) {
                    continue;
                  }
                }
                $dest = JPATH_ROOT . '/templates/' . $templateName . '/html/' . $file;
                $path = pathinfo($dest);
                if (!file_exists($path['dirname'])) {
                  mkdir($path['dirname'], 0777, true);
                }
                @copy(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/html/' . $file, JPATH_ROOT . '/templates/' . $templateName . '/html/' . $file);
              }
            }
          }
        }
      }

      $db = Factory::getDbo();

      $query = $db->getQuery(true)
        ->delete('#__extensions')
        ->where('type = ' . $db->q('plugin'))
        ->where('element = ' . $db->q('responsiveplgoverrides'))
        ->where('folder = ' . $db->q('system'));

      $db->setQuery($query);

      try {
        $db->execute();
      } catch (\Exception $e) {
      }

      if (is_dir(JPATH_ROOT . '/plugins/system/responsiveplgoverrides')) {
        if (is_file(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/responsiveplgoverrides.php')) {
          unlink(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/responsiveplgoverrides.php');
        }
        if (is_file(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/responsiveplgoverrides.xml')) {
          unlink(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/responsiveplgoverrides.xml');
        }
        if (is_file(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/script.php')) {
          unlink(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/script.php');
        }
        self::deleteDir(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/html');
        rmdir(JPATH_ROOT . '/plugins/system/responsiveplgoverrides');
      }
    }
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

  private function scanFiles($dir)
  {
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1) return;

    foreach ($ffs as $ff) {
      $file = $dir . '/' . $ff;
      if (is_file($file)) $this->files[] = str_replace(JPATH_ROOT . '/plugins/system/responsiveplgoverrides/html/', '', $file);
      if (is_dir($file)) $this->scanFiles($file);
    }
  }
}
