<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Utilities\ArrayHelper;

$images  = json_decode($displayData->images);
if (empty($images->image_intro))
{
  return;
}

$params    = $displayData->params;
$imgclass  = empty($images->float_intro) ? $params->get('float_intro') : $images->float_intro;
$extraAttr = '';
$img       = HTMLHelper::cleanImageURL($images->image_intro);
$alt       = empty($images->image_intro_alt) && empty($images->image_intro_alt_empty) ? '' : 'alt="' . htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'UTF-8') . '"';

// Set lazyloading only for images which have width and height attributes
if ((isset($img->attributes['width']) && (int) $img->attributes['width'] > 0)
&& (isset($img->attributes['height']) && (int) $img->attributes['height'] > 0))
{
  $extraAttr = ArrayHelper::toString($img->attributes) . ' loading="lazy"';
}

$image = LayoutHelper::render(
  'ttc.image',
  [
    'img'         => '<img src="' . htmlspecialchars($img->url, ENT_COMPAT, 'UTF-8') . '"' . $alt . $extraAttr . '/>',
    'breakpoints' => [200, 320, 480, 768, 992, 1200, 1600, 1920]
  ]
);

echo '<figure class="' . htmlspecialchars($imgclass, ENT_COMPAT, 'UTF-8') . ' item-image">';
if ($params->get('link_intro_image') && ($params->get('access-view') || $params->get('show_noauth', '0') == '1')) {
  echo '<a href="' . RouteHelper::getRoute($displayData->link, $displayData->params->get('id')) . '" itemprop="url">' . $image . '</a>';
} else {
  echo $image;
}
if (isset($images->image_intro_caption) && $images->image_intro_caption !== '') {
  echo '<figcaption>' . htmlspecialchars($images->image_intro_caption, ENT_COMPAT, 'UTF-8') . '</figcaption>';
}
echo '</figure>';
