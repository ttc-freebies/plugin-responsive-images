---
permalink: latest/index.html
layout: base.njk
title: Downloads
---
# Latest Version
{% assign fff = downloads | first%}

[{{fff.version}}]({{ metainfo.url }}/dist/{{fff.name}})

{% img %}
