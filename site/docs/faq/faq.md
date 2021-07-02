---
eleventyNavigation:
  key: FAQ
  url: '/faq/index.html'
  order: 5
permalink: faq/index.html
layout: base.njk
title: Settings
description: FAQ for responsive images
---
# Frequently Aske Questions

- Why the plugin doesn't use the existing `media/cache` folder?

Because that would be a terrible idea. The folder `media/cache` is emptied on each System update. So if we used that folder all the responsive images sets would have to be recreated on every update, but changing some php,js,css,xml files DOES NOT INVALIDATE the images.

- Will it be compatible with the GSOC 2021 Responsive Images project?

No, but don't hold your breath for that.

- I'm not able to create Avif files

Right now you will have to build PHP 8.1 from source as this is the version that introduces this feature.
