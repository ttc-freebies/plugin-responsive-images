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

    this.button = null;
    this.systemPaths = Joomla.getOptions('system.paths');
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
    fetch(new URL(`${this.systemPaths.baseFull}index.php?option=com_ajax&type=plugin&plugin=responsive&group=content&method=responsive&format=json&${this.token}=1`), {method: 'POST'})
    .then((response)=>{
      if (!response.ok) throw new Error("HTTP status " + response.status);
      return response.json();
    })
    .then(resp => {
      if (resp.success) this.renderMsg({'success': ['Success! 🎉']}, undefined, false, 4000);
      else this.renderMsg({'danger': ["We've failed 🤷‍♂️"]}, undefined, false);
    })
    .catch(err => {
      console.log(err)
      this.renderMsg({'danger': ["We've failed 🤷‍♂️"]}, undefined, false);
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
