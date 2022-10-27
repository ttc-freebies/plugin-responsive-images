---
eleventyNavigation:
  key: Home
  url: '/'
  order: 1
permalink: /
layout: base.njk
title: Responsive Images
description: The ultimate images solution for Joomla 4
---

# Joomla 4 Responsive Images

{% img %}

## Or images done right©
A rock solid solution for creating and using responsive images with Joomla 4 from the developer that brought the images lazyloading and a bunch more goodies into Joomla 4.

### Features:
- Generates Avif images and source sets
- Generates Webp images and source sets
- Use Imagic or GD for image processing
- Clever way to handle any component/module
- Dead easy helper function for layout files
- Versioning because Cache Invalidation Is Hard
- Dead easy to customise


### Latest Version
{% assign fff = releases | first %}

- {{fff.version}} is the latest version.
- Download it here: 

Download the latest Version ( {{fff.version}} ) [here](/dist/pkg_responsive_{{fff.version}}.zip) or for any previous vesrion go to : [Downloads](/downloads)

### What it does

Transforms content (or layout) images from this:
```html
<img
  src="/images/test/34zckrf0l6i51.jpg"
  alt=""
>
```
{% image "./site/images/default-j-image.png", "Default Joomla Image Tag", "(min-width: 30em) 50vw, 100vw" %}

...to this:
```html
<picture class="responsive-image">
  <source
    type="image/avif"
    sizes="(max-width: 1920px) 100vw 1920px"
    srcset="/media/cached-resp-images/images/test/34zckrf0l6i51_480.avif?version=722e4d8793f156da1ad89b44ee0e30b8 480w, /media/cached-resp-images/images/test/34zckrf0l6i51_320.avif?version=722e4d8793f156da1ad89b44ee0e30b8 320w, /media/cached-resp-images/images/test/34zckrf0l6i51_200.avif?version=722e4d8793f156da1ad89b44ee0e30b8 200w">
  <source
    type="image/webp"
    sizes="(max-width: 1920px) 100vw 1920px"
    srcset="/media/cached-resp-images/images/test/34zckrf0l6i51_480.webp?version=722e4d8793f156da1ad89b44ee0e30b8 480w, /media/cached-resp-images/images/test/34zckrf0l6i51_320.webp?version=722e4d8793f156da1ad89b44ee0e30b8 320w, /media/cached-resp-images/images/test/34zckrf0l6i51_200.webp?version=722e4d8793f156da1ad89b44ee0e30b8 200w">
  <source
    type="image/jpeg"
    sizes="(max-width: 1920px) 100vw 1920px"
    srcset="/media/cached-resp-images/images/test/34zckrf0l6i51_480.jpg?version=722e4d8793f156da1ad89b44ee0e30b8 480w, /media/cached-resp-images/images/test/34zckrf0l6i51_320.jpg?version=722e4d8793f156da1ad89b44ee0e30b8 320w, /media/cached-resp-images/images/test/34zckrf0l6i51_200.jpg?version=722e4d8793f156da1ad89b44ee0e30b8 200w">
  <img
    loading="lazy"
    width="2381"
    height="1283"
    src="/media/cached-resp-images/images/test/34zckrf0l6i51_ 1920.jpg?version=722e4d8793f156da1ad89b44ee0e30b8"
    alt="">
</picture>
```
{% image "./site/images/resp-img-tag.png", "Responsive Images Generated Tag", "(min-width: 30em) 50vw, 100vw" %}
