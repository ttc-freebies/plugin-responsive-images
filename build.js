const { readFile } = require('fs').promises;
const { readdirSync, existsSync } = require('fs');
const admZip = require('adm-zip');
const { version } = require('./package.json');

globalThis.doNotZip = [`system/responsiveplgoverrides`, '.DS_Store'];
globalThis.zips = [];
const getDirectories = source =>
    readdirSync(source, { withFileTypes: true })
    .filter(dirent => dirent.isDirectory())
    .map(dirent => dirent.name);

const getCurrentXml = async (path, name) => {
  let xml;
  if (existsSync(`${path !== '' ? path + '/' : ''}${name}.xml`)) {
    xml = await readFile(`${path !== '' ? path + '/' : ''}${name}.xml`, {
      encoding: 'utf8',
    });

    return xml.replace(/{{version}}/g, version);
  }
}

const zipExtension = async (path, name, type) => {
  const noRoot = path.replace(`${process.cwd()}/`, '');
  xml = await getCurrentXml(path, name);
  const zip = new admZip();
  readdirSync(path, { withFileTypes: true })
  .filter(item => !/(^|\/)\.[^/.]/g.test(item.name))
  .forEach(file => {
    if (file.isDirectory()) {
      zip.addLocalFolder(`${noRoot}/${file.name}`, file.name, /^(?!\.DS_Store)/);
    } else if (file.name === `${name}.xml`) {
      zip.addFile(file.name, xml);
    } else if (!['composer.json', 'composer.lock', '.DS_Store'].includes(file.name)) {
      zip.addLocalFile(`${noRoot}/${file.name}`, false);
    }
  });

  zip.addLocalFile('license.txt', false);
    zip.getEntries().forEach(entry => {
      if (/^\.DS_Store/.test(entry.entryName)) {
        zip.deleteFile(entry.entryName);
      }
    });
  globalThis.zips.push({name: name, data: zip.toBuffer(), type: type});
}

(async function exec() {
  const processes = [];
  console.log(getDirectories(`${process.cwd()}/src`));
  getDirectories(`${process.cwd()}/src`).forEach(dir => {
    if (dir === 'package') {
      return;
    }
    if (dir === 'libraries') {
      const zipped = getDirectories(`${process.cwd()}/src/libraries`)
      zipped.forEach(lib => {
        processes.push(zipExtension(`${process.cwd()}/src/libraries/${lib}`, lib, 'libraries'));
      })
    }
    if (dir === 'plugins') {
      const plgTypes = getDirectories(`${process.cwd()}/src/plugins`);
      plgTypes.forEach(type => {
        const plugins = getDirectories(`${process.cwd()}/src/plugins/${type}`);
        plugins.forEach(lib => {
          let shallZip = true;
          globalThis.doNotZip.forEach(dontZip => {
            if (`${type}/${lib}`.startsWith(dontZip)) {
              shallZip = false;
            }
          });
          if (!shallZip) {
            return;
          }
          processes.push(zipExtension(`${process.cwd()}/src/plugins/${type}/${lib}`, lib, `${type}_`));
        })
      })
    }
  });

  await Promise.all(processes);
  const zip = new admZip();
  globalThis.zips.forEach(z => {
    const pre = z.type === `libraries` ? 'lib_' : `plg_${z.type}`;
    zip.addFile(`packages/${pre}${z.name.toLowerCase()}_${version}.zip`, z.data);
  });
  zip.addLocalFile('src/package/pkg_script.php');
  zip.addLocalFile('src/plugins/system/responsiveplgoverrides/html/layouts/joomla/html/image.php');
  zip.addLocalFile('src/plugins/system/responsiveplgoverrides/html/layouts/ttc/image.php', '', 'image2.php');
  let xmlContent = await getCurrentXml('src/package', 'pkg_responsive');
  zip.addFile('pkg_responsive.xml', xmlContent);
  zip.addLocalFile('license.txt');

  zip.writeZip(`site/dist/pkg_responsive_${version}.zip`);
})();
