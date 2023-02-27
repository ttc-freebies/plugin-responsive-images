<?php

/**
 * @copyright   (C) 2021 Dimitrios Grammatikogiannis
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Plugin\PluginHelper;
use Ttc\Freebies\Responsive\Helper as ResponsiveHelper;

extract($displayData);

/** @var $img          string  the original image tag*/
/** @var $breakpoints  array   the breakpoints */

if (PluginHelper::isEnabled('content', 'responsive')) {
  $img = (new ResponsiveHelper)->transformImage($img, $breakpoints);
}

echo $img;
