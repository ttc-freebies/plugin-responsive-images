<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

if (!count($images)) {
  echo Text::_('MOD_RANDOM_IMAGE_NO_IMAGES');
  return;
}

$img = HTMLHelper::_('image', $image->folder . '/' . htmlspecialchars($image->name, ENT_COMPAT, 'UTF-8'), '', array('width' => $image->width, 'height' => $image->height));
$img = LayoutHelper::render(
  'ttc.image',
  [
    'img'         => $img,
    'breakpoints' => [200, 320, 480, 768, 992, 1200, 1600, 1920]
  ]
);

echo '<div class="mod-randomimage random-image">'
. ($link ? '<a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">' . $img . '</a>' : $img)
. '</div>';
