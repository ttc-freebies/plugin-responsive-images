
<?php
/**
 * @copyright   (C) 2022 Dimitrios Grammatikogiannis
 * @license     GNU General Public License version 2 or later
 */
defined('_JEXEC') || die('Restricted access');

use Joomla\CMS\Installer\Adapter\PluginAdapter;
use Joomla\CMS\Installer\InstallerScript;

class plgContentResponsiveInstallerScript extends InstallerScript
{
  protected $deleteFiles = [
    '/plugins/content/responsive.php',
    '/administrator/languages/en-GB/plg_content_responsive.sys.ini',
    '/administrator/languages/en-GB/plg_content_responsive.ini'
  ];
  protected $deleteFolders = [];

  public function update(PluginAdapter $parent)
  {
    $this->removeFiles();
  }
}
