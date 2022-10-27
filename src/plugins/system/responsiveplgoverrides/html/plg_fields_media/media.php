<?php
defined('_JEXEC') || die;

use Joomla\CMS\Layout\LayoutHelper;

if ($field->value == '' || !$field->value) return;

$class = $fieldParams->get('image_class');
$value  = $field->value;
$options = [
  'src'         => $value['imagefile'],
  'alt'         => empty($value['alt_text']) && empty($value['alt_empty']) ? '' : $value['alt_text'],
  'breakpoints' => [320, 768, 1200]
];

if ($class) {
  $options['class'] = htmlentities($class, ENT_COMPAT, 'UTF-8', true);
}

echo LayoutHelper::render('joomla.html.image', $options);
