---
permalink: /update.xml
---
{% assign fff = downloads | first %}
{% assign url = 'https://responsive-images.dgrammatiko.dev/' %}
<?xml version="1.0" encoding="utf-8"?>
<updates>
  <update>
    <name>Responsive Images</name>
    <description>Responsive Images Package</description>
    <element>pkg_responsive</element>
    <type>package</type>
    <version>{{fff.version}}</version>
    <client>site</client>
    <infourl title="Responsive Images">{{url}}downloads/index.html</infourl>
    <downloads>
      <downloadurl type="full" format="zip">{{url}}dist/{{fff.name}}</downloadurl>
    </downloads>
    <sha384>{{ fff.sha384 }}</sha384>
    <targetplatform name="joomla" version="4\.[1]"/>
    <tags>
      <tag>stable</tag>
    </tags>
  </update>
</updates>
