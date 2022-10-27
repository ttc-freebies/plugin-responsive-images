---
permalink: documentation/requirements/index.html
layout: base.njk
title: Requirements
description: Responsive images requirements
---

{% include "components/docsmenu.njk" %}

# Requirements

The requiements are pretty much the same as those for Joomla 4. Additionally, you need to have a graphics library (either GD or Imagick) enabled in your php.ini (something like: `extension=gd` or `extension=imagick`).
Also the plugin due to its nature is going to consume more storage space on your server per image. Effectivelly you are trading cheap storage space with better performance and better SEO...
