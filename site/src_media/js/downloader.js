import {render, html} from 'uhtml';
import { generateZip } from './utils.js';

class ComponentCreator extends HTMLElement {
  constructor() {
    super()

    this.store = {
      name: 'cassiopeia',
      files: {},
    };
    this.enabled = true;
    this.renderEl = this.renderEl.bind(this);
    this.updState = this.updState.bind(this);
    this.onCreate = this.onCreate.bind(this);
  }

  connectedCallback() {
    let files;
    const jsonEl = document.getElementById('data');
    if (!jsonEl) {
      throw new Error('Data is missing...')
    }
    try {
      files = JSON.parse(jsonEl.innerText);
    } catch (err) {
      throw new Error('Malformed JSON...')
    }

    if (!files) {
      throw new Error('Data is missing...')
    }

    this.store.files = files.files;

    this.renderEl();
  }

  renderEl() {
    render(
      this,
      html`<div>
        <label>Template Name
          <input type="text" value="${this.store.name}" oninput="${e => this.updState('name', e.target.value)}" />
          ${this.enabled ? html`<button onclick="${this.onCreate}" style="width: 100%;">Get it!</button>` : html`<p>Template name is required!</p>`}
      </div>
      <hr/>`)
  }

  updState(type, value) {
    if (value.length > 0) {
      this.enabled = true;
      this.store[type] = value.toLowerCase();
    } else {
      this.enabled = false;
    }
    this.renderEl();
  }

  async onCreate(ev) {
    ev.preventDefault;
    generateZip(this);
  }
}

customElements.define('create-joomla-plugin', ComponentCreator);
