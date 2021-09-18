<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

if ($field->value == '' || !$field->value) return;

$class = $fieldParams->get('image_class');

if ($class) {
  $class = ' class="' . htmlentities($class, ENT_COMPAT, 'UTF-8', true) . '"';
}

$value  = $field->value;
$img    = HTMLHelper::cleanImageURL($value['imagefile']);
$imgUrl = htmlentities($img->url, ENT_COMPAT, 'UTF-8', true);
$alt    = empty($value['alt_text']) && empty($value['alt_empty']) ? '' : ' alt="' . htmlspecialchars($value['alt_text'], ENT_COMPAT, 'UTF-8') . '"';

echo LayoutHelper::render(
  'ttc.image',
  [
    'img'         => '<img src="' . $imgUrl . '"' . $alt . $class . ' />',
    'breakpoints' => [200, 320, 480, 768, 992, 1200, 1600, 1920]
  ]
);
