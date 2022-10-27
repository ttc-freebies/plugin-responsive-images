---
permalink: /update.xml
---
{% assign url = 'https://responsive-images.dgrammatiko.dev/' %}
<?xml version="1.0" encoding="utf-8"?>
<updates>
{% for curItem in releases %}
  <update>
    <name>Responsive Images</name>
    <description>Responsive Images Package</description>
    <element>pkg_responsive</element>
    <type>package</type>
    <version>{{curItem.version}}</version>
    <infourl title="Responsive Images {{curItem.version}}">{{url}}downloads/index.html</infourl>
    <downloads><downloadurl type="full" format="zip">{{url}}dist/pkg_responsive_{{curItem.version}}.zip</downloadurl></downloads>
    <tags><tag>{{curItem.type}}</tag></tags>
    <targetplatform name="joomla" version="{{curItem.joomlaVer}}"/>
    <sha384>{{curItem.sha384}}</sha384>
    <client>{{curItem.client}}</client>
    <php_minimum>{{curItem.phpMin}}</php_minimum>
  </update>
{% endfor %}
</updates>
