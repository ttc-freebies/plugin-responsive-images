---
eleventyNavigation:
  key: Documentation
  url: '/documentation/index.html'
  order: 2
permalink: documentation/index.html
layout: base.njk
title: Documentation
description: Responsive Images Documentation
---

{% include "components/docsmenu.njk" %}

# Overview

This plugin was coded to do the least amount of work possible per request. Efficiency was paramount in terms of wasted cpu cycles and searching for images applicable to transformations in a context that shouldn't be valid. Thus, the architecture differs from anything "similar" in the Joomla Extensions Directory. This plugin extends my work on the core Joomla regarding the image field. Let me explain...

## How images work in Joomla

The CMS has two ways exposed to the users to select and display images:

- using a field (or a custom field which in fact, is an extension of the original field)
- or using the editor (none, tinyMCE, CodeMirror or any 3rd part editor)

The field allows users to select an image that will eventually be rendered (the HTML tag) inside a layout. The image tag is generated automatically on the editor content when using the editor field. Since the editor's contents will be saved into the database, the plugin needs to parse the contents in real-time, find all the images and replace the tags. Thus the contents of an editor field are way more inefficient than a plain image field! If you could structure your posts using custom fields could be faster than using the images inside an editor.
So, for the image field, a simple override (installed on every template when you install the package) will effectively pass all the images through the plugin. Of course, you can be even more granular and pass only the images you want through the plugin (check the [layouts documentation](/documentation/layouts/index.html)). One new creative way is also present with this approach: you can specify a particular width to create a thumbnail effectively!
For the editor, as mentioned before, the plugin is doing a search/find/replace operation that is a bit less optimal. Still, if you can cache the articles (or whatever your component item is producing), then it's also extremely fast/efficient.
On top of all these, there is a plugin that will create the relative images (in all the allowed formats) for every source set whenever the image is uploaded. This means that the costly image generation happens when a user uploads a new image instead of when a visitor requests a page.

## Notes

- Most 3rd party developers still need to use the preferred way of rendering images. If you come across such an extension, please ask them to read the Joomla 4 [image convention](https://magazine.joomla.org/all-issues/february-2022/new-image-convention-to-help-developers) and comply!
- If you're using any 3rd party editor/Media Manager, be aware that most of them they **don't** save the same URL for the selected images. This is particularly concerning as the new URL format in Joomla 4 also embeds the width/height and other info. The width and height are crucial to keeping the Cumulative Layout Shift as low as possible. Ask them to comply!
