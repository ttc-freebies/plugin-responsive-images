<?php
/**
 * @copyright   (C) 2022 Dimitrios Grammatikogiannis
 * @license     GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;


Factory::getDocument()->getWebAssetManager()
  ->registerScript(
    'clearcache-es6',
    'media/plg_content_responsive/js/clearcache-es6.js',
    ['version' => 'auto', 'relative' => true],
    ['nomodule' => true, 'defer' => true],
    ['core']
  )
  ->registerAndUseScript(
    'clearcache-esm',
    'media/plg_content_responsive/js/clearcache-esm.js',
    ['version' => 'auto', 'relative' => true],
    ['type' => 'module'],
    ['clearcache-es6', 'core']
  );
?>
<clear-cache-field label-text="<?= Text::_('PLG_CONTENT_RESPONSIVE_MORE_FIELDSET_CLEAR_CACHE_DESC'); ?>" button-text="<?= Text::_('PLG_CONTENT_RESPONSIVE_MORE_FIELDSET_CLEAR_CACHE_BTN'); ?>" token="<?= Session::getFormToken(); ?>">
  <button>Clean cache</button>
</clear-cache-field>
