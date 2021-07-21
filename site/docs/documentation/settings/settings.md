---
permalink: documentation/settings/index.html
layout: base.njk
title: Settings
description: Settings for responsive images
---

{% include "components/docsmenu.njk" %}

# Adjusting the settings to your needs

## The Plugin tab
On the langing page of the plugin you can enable or disable the plugin or adjust the Access level or the Order of execution. Basically these are basic settings found across the Joomla plugins, if you need further help on these please refer to Joomla'a documentation.

{% image "./site/images/settings1.png" "The plugin landing page", "The plugin landing page", "(min-width: 30em) 50vw, 100vw" %}

## The Components tab
Here you can adjust when the plugin will be invoked for the event `onContentPrepare`.
- You can add any component/module in the `Component Name` field.
- Then you need to specify the views in the `Component Views` field. Multiple views are supported, just separate them with comma (no spaces).
- Then you need to specify the database column that the plugin will operate, in the `Component Database Column` field. Multiple database columns are supported, just separate them with comma (no spaces).

{% image "./site/images/settings2.png" "Content behaviour", "Content behaviour", "(min-width: 30em) 50vw, 100vw" %}

## The Behaviour tab
Here you can adjust the inner plugin features.
- In the `Excluded Directories` field you can specify the image directories paths that the plugin **should NOT** operate. Multiple directory paths are supported, just separate them with comma (no spaces).
- In the `Filename Separator` field you can specify the separator sign eg a `file.jpg` will be transformed to `file_200.jpg`. Be aware that Joomla doesn't support all ASCII characters in the filenames and a change here will break existing sourcesets (you will need to manually remove the folder `media/cached-resp-images`).
- In the `Image Sizes` field you can specify the image dimensions (width) that the plugin should produce. Multiple width are supported, just separate them with comma (no spaces). Be aware that a change here will break existing sourcesets (you will need to manually remove the folder `media/cached-resp-images`).
- Cleanup on Uninstall will remove any produced images on the plugin's uninstallation.

{% image "./site/images/settings3.png" "The base settings", "The base settings", "(min-width: 30em) 50vw, 100vw" %}

## The Quality tab
- In the `JPEG Image Quality` field you can specify the quality of the produced `.jpg` images.
- The Create WebP Images field will enable or disable the creation of WebP images.
- In the `WebP Image Quality` field you can specify the quality of the produced `.webp` images.
- The Create Avif Images field will enable or disable the creation of Avif images.
- In the `Avif Image Quality` field you can specify the quality of the produced `.avif` images.

> In order to produce WebP or Avif images your server needs 
> the appropriate GD/Imagick and/or Libavif support

{% image "./site/images/settings4.png" "Soursets settings", "Soursets settings", "(min-width: 30em) 50vw, 100vw" %}

## The Scalling tab
The Allow scalling up field will enable or disable the creation of images using upscalling to match all the given widths (the ones assigned in the Behaviour tab).

{% image "./site/images/settings5.png" "Scalling", "Scalling settings", "(min-width: 30em) 50vw, 100vw" %}
