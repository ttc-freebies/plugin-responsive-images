---
eleventyNavigation:
  key: Downloads
  url: '/downloads/index.html'
  order: 4
permalink: downloads/index.html
layout: base.njk
title: Downloads
---
# Releases

{% for dl in releases %}
<section aria-label="Release: {{dl.version}}" style="padding: 1rem 0;">
  <details>
    <summary>Release: {{dl.version}}</summary>
    <div class="release-notes" style="background-color: var(--secondary-transparent); padding: 1rem 2rem;">
      <h2 style="text-align: center">
        <a type="button" href="/dist/{{dl.name}}">Download v.{{dl.version}}</a>
      </h2>
      <h3>Release type: {{dl.type}}</h3>
      <h3>New feature</h3>
      <ul>
        {% for feature in dl.features %}
          <li> {{ feature | safe }} </li>
          {% else %}
          <li>No new features</li>
        {% endfor %}
      </ul>
      <h3>Change</h3>
      <ul>
        {% for change in dl.changes %}
          <li> {{ change | safe }} </li>
          {% else %}
          <li>No change</li>
        {% endfor %}
      </ul>
      <h3>Bug fixes</h3>
      <ul>
        {% for bug in dl.bugs %}
          <li> {{ bug | safe }} </li>
          {% else %}
          <li>No bug fixes</li>
        {% endfor %}
      </ul>
      <h3>Notes</h3>
      <ul>
        {% for note in dl.notes %}
          <li> {{ note | safe }} </li>
          {% else %}
          <li>No notes</li>
        {% endfor %}
      </ul>
    </div>
  </details>
</section>
{% else %}
- If you see this message the site is broken, please report it.
{% endfor %}

<h3>Looking for the custom image layout?</h3>
<a href="/create-template-overrides/index.html">Create a custom image layout for your template</a>
<br>
{% img %}
