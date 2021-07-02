---
permalink: latest/index.html
layout: base.njk
title: Downloads
---
# Latest Version
{% assign fff = downloads | first%}

[{{fff.version}}]({{ metainfo.url }}/downloads/{{fff.name}})

{% img %}
