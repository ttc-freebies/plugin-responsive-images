
<?php
/**
 * @copyright   (C) 2022 Dimitrios Grammatikogiannis
 * @license     GNU General Public License version 2 or later
 */
defined('_JEXEC') || die('Restricted access');

use Joomla\CMS\Installer\Adapter\PluginAdapter;
use Joomla\CMS\Installer\InstallerScript;

class plgMediaActionResponsiveInstallerScript extends InstallerScript
{
  protected $deleteFiles = [
    '/plugins/media-action/responsive.php',
    '/administrator/language/en-GB/plg_media-action_responsive.sys.ini',
    '/administrator/language/en-GB/plg_media-action_responsive.ini'
  ];
  protected $deleteFolders = [];

  public function update(PluginAdapter $parent)
  {
    $this->removeFiles();
  }
}
