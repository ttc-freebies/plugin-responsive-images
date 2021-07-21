---
permalink: documentation/layouts/index.html
layout: base.njk
title: Layouts
description: Using responsive images in layouts
---

{% include "components/docsmenu.njk" %}

# Using responsive images in layouts

The most powerfull feature of the plugin responsive images is the ability to use it on any layout in Joomla, making it the utlimate and together with the Cache Invalidation/Versioning the only thing you will ever need...

> Enable Responsive images ANYWHERE

Enabling Responsive images in any Component/Module/Template is as easy as it gets

``` php
/**
 * Assuming that the imports exist in the top of the file
 * or they need to be added, eg.:
 */
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
 /** 
 * Also assumes that the image URL is in a variable named $imageUrl
 * And that the image alt attribute value is a variable named $imageAlt
 */
$image = '<img
            src="' . HTMLHelper::cleanImageURL($imageUrl) . '"
            alt="' . $imageAlt . '"
            // Other attributes
          />';

// Replace the old code with:
echo LayoutHelper::render(
  'ttc.image',
  [
    'img' => $image,
    'breakpoints' => [200, 320, 480, 768, 992, 1200, 1600, 1920]
  ]
);
```

- The `array(200, 320, 480, 768, 992, 1200, 1600, 1920)` can be used to reduce the sizes that will be displayed (the generated images are controlled by the actual width of the image and the setting of the plugin). This is extremely useful and in reality is a camouflaged way to create sufficient thumbnails in any layout! 👌🏻


### A note for people updating to version 4:
### The old code will not work, you have to edit all your instanses.
