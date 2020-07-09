# Enable Responsive images ANYWHERE

!> Enabling Responsive images in any Component/Module/Template is as easy as it gets

```php
$image = '<img src="some/path/to/some/image.jpg" alt="Some Text" data-something="whatever" />';

if (JPluginHelper::isEnabled('content', 'responsive')) {
  JLoader::register('Ttc\Freebies\Responsive\Helper', JPATH_ROOT . '/plugins/content/responsive/helper.php', true);
  $image = (new \Ttc\Freebies\Responsive\Helper)->transformImage($image, array(200, 320, 480, 768, 992, 1200, 1600, 1920));
}

echo $image;
```

> Code explanation:

- Grab in a variable the image tag that you want to transform to responsive image (Note that the image path should be **relative**, eg `images/some-file-name.jpg`)

```php
$image = '<img src="some/path/to/some/image.jpg" alt="Some Text" data-something="whatever" />';
```

- Then copy/paste these 5 lines:

```php
if (JPluginHelper::isEnabled('content', 'responsive')) {
  JLoader::register('Ttc\Freebies\Responsive\Helper', JPATH_ROOT . '/plugins/content/responsive/helper.php', true);
  $image = (new \Ttc\Freebies\Responsive\Helper)->transformImage($image, array(200, 320, 480, 768, 992, 1200, 1600, 1920));
}
```

- This code essentially checks if the plugin is enabled and if so it will pass the variable `$img` to the internals and finally replace the variable with the new picture element

- The `array(200, 320, 480, 768, 992, 1200, 1600, 1920)` can be used to reduce the sizes that will be displayed (the generated images are controlled by the actual width of the image and the setting of the plugin `scale up`). This is extremely useful and in reality is a camouflaged way to create sufficient thumbnails in any layout! 👌🏻

- Finally echo the image so it can be displayed, if the plugin gets disabled or uninstalled the site will continue functioning since the previous step will not be executed

```php
echo $image;
```

That's all, enjoy responsive images anywhere!!!

# Examples

> intro_image.php:

```php

defined('JPATH_BASE') or die;

$images = json_decode($displayData->images);

if (!empty($images->image_intro)) {
  $img = '<img';
  if ($images->image_intro_caption) {
    $img .= ' class="caption" title="' . htmlspecialchars($images->image_intro_caption) . '"';
  }

  $img .= ' src="' . htmlspecialchars($images->image_intro) . '"';
  $img .= ' alt="' . htmlspecialchars($images->image_intro_alt) . '"';
  $img .= ' itemprop="image"/>';

  if (\Joomla\CMS\Plugin\PluginHelper::isEnabled('content', 'responsive')) {
    JLoader::register('Ttc\Freebies\Responsive\Helper', JPATH_ROOT . '/plugins/content/responsive/helper.php', true);
    $img = (new \Ttc\Freebies\Responsive\Helper)->transformImage($img, array(200, 320, 480, 768, 992, 1200, 1600, 1920));
  }

  $imgfloat = empty($images->float_intro) ? $displayData->params->get('float_intro') : $images->float_intro;

  echo '<div class="pull-' . htmlspecialchars($imgfloat) . ' item-image">' . $img . '</div>';
}
```

> full_image.php:

```php
defined('JPATH_BASE') or die;

$images = json_decode($displayData->images);

if (!empty($images->image_fulltext)) {
  $img = '<img';
  if ($images->image_fulltext_caption) {
    $img .= ' class="caption" title="' . htmlspecialchars($images->image_fulltext_caption) . '"';
  }

  $img .= ' src="' . htmlspecialchars($images->image_fulltext) . '"';
  $img .= ' alt="' . htmlspecialchars($images->image_fulltext_alt) . '"';
  $img .= ' itemprop="image"/>';

  if (\Joomla\CMS\Plugin\PluginHelper::isEnabled('content', 'responsive')) {
    JLoader::register('Ttc\Freebies\Responsive\Helper', JPATH_ROOT . '/plugins/content/responsive/helper.php', true);
    $img = (new \Ttc\Freebies\Responsive\Helper)->transformImage($img, array(200, 320, 480, 768, 992, 1200, 1600, 1920));
  }

  $imgfloat = empty($images->float_fulltext) ? $displayData->params->get('float_fulltext') : $images->float_fulltext;

  echo '<div class="pull-' . htmlspecialchars($imgfloat) . ' item-image">' . $img . '</div>';
}
```
