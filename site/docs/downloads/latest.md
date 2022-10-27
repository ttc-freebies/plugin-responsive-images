---
permalink: latest/index.html
layout: base.njk
title: Downloads
---
# Latest Version
{% assign fff = releases | first %}

- {{fff.version}} is the latest version.
- Download it here: [{{fff.version}}](/dist/{{fff.name}})

{% img %}
