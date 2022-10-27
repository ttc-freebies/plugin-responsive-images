
<?php
/**
 * @copyright   (C) 2022 Dimitrios Grammatikogiannis
 * @license     GNU General Public License version 2 or later
 */
defined('_JEXEC') || die('Restricted access');

use Joomla\CMS\Installer\Adapter\LibraryAdapter;
use Joomla\CMS\Installer\InstallerScript;

class libTtcInstallerScript extends InstallerScript
{
  protected $deleteFiles = [];
  protected $deleteFolders = [
    '/libraries/Ttc/vendor',
    '/libraries/Ttc/vendor_prefixed',
    '/libraries/Ttc/src/Intervention',
  ];

  public function update(LibraryAdapter $parent)
  {
    $this->removeFiles();
  }
}
