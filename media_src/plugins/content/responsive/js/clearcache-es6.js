document.querySelectorAll('.hide-aware-inline-help.d-none').forEach(el => el.classList.remove('hide-aware-inline-help', 'd-none'));
document.getElementById('toolbar-inlinehelp').remove();

customElements.define('clear-cache-field', class extends HTMLElement {
  get labelText() {
    return this.getAttribute('label-text');
  }
  get buttonText() {
    return this.getAttribute('button-text');
  }
  get token() {
    return this.getAttribute('token');
  }
  constructor() {
    super();

    const systemPaths = Joomla.getOptions('system.paths');
    this.button = null;
    this.url = new URL(`${systemPaths.baseFull}index.php?option=com_ajax&type=plugin&plugin=responsive&group=content&method=responsive&format=json&${this.token}=1`);
    this.onClick = this.onClick.bind(this);
  }
  connectedCallback() {
    this.innerHTML = Joomla.sanitizeHtml(`
<div class="control-group">
  <div class="control-label"><label>${this.labelText}</label></div>
  <div class="controls">
      <button class="btn btn-danger w-100" type="button">${this.buttonText}</button>
  </div>
</div>`, {'label': []});

    this.button = this.querySelector('button');
    this.button.addEventListener('click', this.onClick);
  }

  onClick() {
    fetch(this.url, {method: 'POST'})
    .then(resp => {
      if (resp.statusText !== 'OK' || !resp.ok) throw new Error('Bad Response!')

      return resp.json();
    })
    .then(resp => {
      if (resp.success) this.renderMsg({'success': ['Success! 🎉']}, undefined, false, 4000);
      else this.renderMsg({'danger': ["We've failed 🤷‍♂️"]}, null, false);
    })
    .catch(err => {
      this.renderMsg({'danger': ["We've failed 🤷‍♂️"]}, null, false);
     });
  }

  renderMsg(msg, selector, keepOld, timeout) {
    scrollTo({
      top: 0,
      left: 0,
      behavior: 'smooth'
    });
    Joomla.renderMessages(msg, selector, keepOld, timeout);
  }
});
