---
permalink: /update.xml
---

{% assign fff = downloads | first %}
{% assign url = 'https://responsive-images.dgrammatiko.dev/' %}

<?xml version="1.0" encoding="utf-8"?>
<updates>
  <update>
    <name>Responsive Images</name>
    <version>{{fff.version}}</version>
    <infourl title="Responsive Images">{{url}}downloads/index.html</infourl>
    <downloads>
      <downloadurl type="full" format="zip">{{url}}dist/{{fff.name}}</downloadurl>
    </downloads>
    <targetplatform name="joomla" version="4.0.6"/>
    <element>pkg_responsive</element>
    <type>package</type>
  </update>
</updates>
