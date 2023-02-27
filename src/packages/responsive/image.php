<?php
/**
 * @copyright   (C) 2021 Dimitrios Grammatikogiannis
 * @license     GNU General Public License version 2 or later
 */

/**
 * Layout variables
 * -----------------
 * @var   array  $displayData  Array with all the given attributes for the image element.
 *                             Eg: src, class, alt, width, height, loading, decoding, style, data-*
 *                             Note: only the alt and src attributes are escaped by default!
 */

defined('_JEXEC') || die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;
use Ttc\Freebies\Responsive\Helper as ResponsiveHelper;

$breakpoints = [320, 768, 1200];

// Handle the alt attribute
if (isset($displayData['alt'])) {
  if ($displayData['alt'] === false) {
    unset($displayData['alt']);
  } else {
    $displayData['alt'] = $this->escape($displayData['alt']);
  }
}
// Handle the breakpoins
if (isset($displayData['breakpoints'])) {
  if (is_array($displayData['breakpoints'])) {
    $breakpoints = $displayData['breakpoints'];
  }
  unset($displayData['breakpoints']);
}
// Handle the decoding attribute
if (empty($displayData['decoding'])) {
  $displayData['decoding'] = 'async';
}
// Handle the loading attribute
if (empty($displayData['loading'])) {
  $displayData['loading'] = 'lazy';
}

$img = '<img ' . ArrayHelper::toString($displayData) . '>';

// Respect the plugin state
if (PluginHelper::isEnabled('content', 'responsive')) {
  $img = (new ResponsiveHelper)->transformImage($img, $breakpoints);
}

echo $img;
