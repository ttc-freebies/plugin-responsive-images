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
    <sha384>{{ fff.sha384 }}</sha384>
    <targetplatform name="joomla" version="4.1"/>
    <element>pkg_responsive</element>
    <type>package</type>
    <tags>
      <tag>stable</tag>
    </tags>
  </update>
</updates>
