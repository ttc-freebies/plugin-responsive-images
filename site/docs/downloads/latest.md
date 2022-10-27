---
permalink: latest/index.html
layout: base.njk
title: Downloads
---
# Latest Version
{% assign fff = releases | first %}

- {{fff.version}} is the latest version.
- Download it here: [{{fff.version}}](/dist/pkg_responsive_{{fff.version}}.zip)

{% img %}
