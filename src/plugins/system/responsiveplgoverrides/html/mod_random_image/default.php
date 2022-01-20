<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

if (!count($images)) {
  echo Text::_('MOD_RANDOM_IMAGE_NO_IMAGES');
  return;
}

$img = LayoutHelper::render('joomla.html.image', [
  'src'         => $image->folder . '/' . $image->name,
  'alt'         => empty($value['alt_text']) && empty($value['alt_empty']) ? '' : $value['alt_text'],
  'breakpoints' => [200, 320, 480, 768, 992, 1200, 1600, 1920]
]);

echo '<div class="mod-randomimage random-image">'
. ($link ? '<a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">' . $img . '</a>' : $img)
. '</div>';
