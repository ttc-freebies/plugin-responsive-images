<?php

/**
 * @copyright   (C) 2021 Dimitrios Grammatikogiannis
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

extract($displayData);

/** @var $img          string  the original image tag*/
/** @var $breakpoints  array   the breakpoints */

if (\Joomla\CMS\Plugin\PluginHelper::isEnabled('content', 'responsive')) {
  if (!class_exists('\Ttc\Freebies\Responsive\Helper') && is_dir(JPATH_LIBRARIES . '/ttc')) {
    JLoader::registerNamespace('Ttc', JPATH_LIBRARIES . '/ttc');
  }

  if (class_exists('\Ttc\Freebies\Responsive\Helper')) {
    $img = (new \Ttc\Freebies\Responsive\Helper)->transformImage($img, $breakpoints);
  }
}

echo $img;
