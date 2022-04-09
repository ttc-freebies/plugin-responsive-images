---
eleventyNavigation:
  key: Downloads
  url: '/downloads/index.html'
  order: 4
permalink: dist/index.html
layout: base.njk
title: Downloads
---
# Releases
## Versions

{% for dl in downloads %}
- [{{dl.version}}]({{ metainfo.url }}/dist/{{dl.name}})
{% else %}
- If you see this message the site is broken, please report it.
{% endfor %}

{% img %}
