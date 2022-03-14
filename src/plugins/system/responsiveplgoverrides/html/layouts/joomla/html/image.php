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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

$breakpoints = [200, 320, 480, 768, 992, 1200, 1600, 1920];
$imgOriginal = HTMLHelper::_('cleanImageURL', $displayData['src']);
$displayData['src'] = $this->escape($imgOriginal->url);

if (isset($displayData['alt'])) {
  if ($displayData['alt'] === false) {
    unset($displayData['alt']);
  } else {
    $displayData['alt'] = $this->escape($displayData['alt']);
  }
}

if ($imgOriginal->attributes['width'] > 0 && $imgOriginal->attributes['height'] > 0) {
  $displayData['width']  = $imgOriginal->attributes['width'];
  $displayData['height'] = $imgOriginal->attributes['height'];

  if (empty($displayData['loading'])) {
    $displayData['loading'] = 'lazy';
  }
}

if (isset($displayData['breakpoints']) && is_array($displayData['breakpoints'])) {
  $breakpoints = $displayData['breakpoints'];
  unset($displayData['breakpoints']);
}

if (empty($displayData['decoding'])) {
  $displayData['decoding'] = 'async';
}

$img = '<img ' . ArrayHelper::toString($displayData) . '>';

if (\Joomla\CMS\Plugin\PluginHelper::isEnabled('content', 'responsive')) {
  if (!class_exists('\Ttc\Freebies\Responsive\Helper') && is_dir(JPATH_LIBRARIES . '/ttc')) {
    JLoader::registerNamespace('Ttc', JPATH_LIBRARIES . '/ttc');
  }

  if (class_exists('\Ttc\Freebies\Responsive\Helper')) {
    $img = (new \Ttc\Freebies\Responsive\Helper)->transformImage($img, $breakpoints);
  }
}

echo $img;
