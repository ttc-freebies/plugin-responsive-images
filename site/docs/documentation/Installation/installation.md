---
permalink: documentation/installation/index.html
layout: base.njk
title: Installation
description: Responsive images installation
---

{% include "components/docsmenu.njk" %}

# Installation

{% assign fff = downloads | first %}

For the installation the procedure is the expected one and once the package is installed the fuctionality is immediately available. Here are the two different ways to install the package:

- Using drag and drop
  - Download the package [{{fff.version}}]({{ metainfo.url }}/dist/{{fff.name}})
  - Login to your site's backend and go to system from the menu {% image "./site/images/install_1.png", "System Dashboard", "(min-width: 30em) 50vw, 100vw" %}

  - Click on the link `Extensions` in the `Install` card. The new page should have the tab `Upload Package File` selected, if not click that tab.   {% image "./site/images/install_2.png" "Drag and drop installation", "Drag and drop installation", "(min-width: 30em) 50vw, 100vw" %}

  - Drag and drop the file in the dropdown area. Done!
- Using a link
  - Login to your site's backend and go to system
  - Click on the link `Extensions` in the `Install` card
  - On the new page click on the tab `Install from URL`. {% image "./site/images/install_3.png" "Drag and drop installation", "Install from URL", "(min-width: 30em) 50vw, 100vw" %}
  - Paste the link:
    `{{ metainfo.url }}/dist/{{fff.name}}`
    and click the button Check and Install. Done.
