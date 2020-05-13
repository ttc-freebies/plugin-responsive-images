const { copy, exists, mkdir, readFile, writeFile } = require('fs-extra');
const util = require('util');
const rimRaf = util.promisify(require('rimraf'));
const { version } = require('./package.json');

(async function exec() {
  await rimRaf('./dist');
  await rimRaf('./package');
  await copy('./src', './package');

  if (!(await exists('./dist'))) {
    await mkdir('./dist');
  }

  let xml =  await readFile('./package/responsive.xml', { encoding: 'utf8' });
  xml = xml.replace('{{version}}', version);

  await writeFile('./package/responsive.xml', xml, { encoding: 'utf8' });

  // Package it
  const zip = new (require('adm-zip'));
  zip.addLocalFolder('package', false);
  zip.writeZip(`dist/plg_responsive_${version}.zip`);

  await rimRaf('./docs/dist');
  await copy('./dist', './docs/dist');

  // Update the version, docs
  ['docs/_coverpage.txt', 'docs/installation.txt'].forEach(async file => {
    let cont = await readFile(file, { encoding: 'utf8' });
    cont = cont.replace('{{version}}', version);
    cont = cont.replace('{{download}}', `[Download](dist/plg_responsive_${version}.zip ':ignore')`);

    await writeFile(file.replace('.txt', '.md'), cont, { encoding: 'utf8' });
  })
})();
