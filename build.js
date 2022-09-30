const { readFile } = require('fs').promises;
const { readdirSync, existsSync } = require('fs');
const admZip = require('adm-zip');

const { version, pkgs_, doNotZip } = require('./package.json');
const packagesZips = [];
const processes = [];

const getDirectories = source => readdirSync(source, { withFileTypes: true }).filter(dirent => dirent.isDirectory()).map(dirent => dirent.name);

const getCurrentXml = async (path, name) => {
  if (existsSync(`${path !== '' ? path + '/' : ''}${name}.xml`))
    return (await readFile(`${path !== '' ? path + '/' : ''}${name}.xml`, { encoding: 'utf8' })).replace(/{{version}}/g, version);
}

function getZipData(obj) {
  const index = pkgs_.responsive.zips.findIndex(element => element.name === obj.name && element.type === obj.type && element.subType === obj.subType);
  if (index >= 0) return pkgs_.responsive.zips[index].data;
}

function getShortType(type) {
  if (type === 'libraries') return 'lib_';
  if (type === 'components') return 'com_';
  if (type === 'modules') return 'mod_';
  if (type === 'plugins') return 'plg_';
  if (type === 'templates') return 'tpl_';
}

const zipExtensionRecurse = (file, zip, obj, xml, noRootPath) => {
  if (file.isDirectory()) {
    zip.addLocalFolder(`${noRootPath}/${file.name}`, file.name, /^(?!\.DS_Store)/);
  } else if (file.name === `${obj.type === 'packages' ? 'pkg_' + obj.name : obj.name}.xml`) {
    zip.addFile(file.name, xml);
  } else {
    zip.addLocalFile(`${noRootPath}/${file.name}`, false);
  }
}

const zipExtension = async (obj) => {
  const noRootPath = `src/${obj.type}/${obj.subType ? obj.subType + '/' : ''}${obj.name}`;
  const xml = await getCurrentXml(`${process.cwd()}/${noRootPath}`, obj.type === 'packages' ? 'pkg_' + obj.name : obj.name);
  const zip = new admZip();

  readdirSync(`${process.cwd()}/${noRootPath}`, { withFileTypes: true }).filter(item => !/(^|\/)\.[^/.]/g.test(item.name)).forEach(file => zipExtensionRecurse(file, zip, obj, xml, noRootPath));

  if (existsSync(`${process.cwd()}/${noRootPath.replace('src/', 'media_src/')}`)) {
    readdirSync(`${process.cwd()}/${noRootPath.replace('src/', 'media_src/')}`, { withFileTypes: true }).filter(item => !/(^|\/)\.[^/.]/g.test(item.name)).forEach(file => zipExtensionRecurse(file, zip, obj, xml, noRootPath.replace('src/', 'media_src/')));
  }

  zip.getEntries().forEach(entry => {
    if (/^\.DS_Store/.test(entry.entryName)) zip.deleteFile(entry.entryName);
  });

  if (obj.type !== 'packages') {
    Object.keys(pkgs_).forEach(pkk => {
      const index = pkgs_[pkk].zips.findIndex(element => element.name === obj.name && element.type === obj.type && element.subType === obj.subType);
      if (index >= 0) {
        pkgs_[pkk].zips[index].data = zip.toBuffer();
        pkgs_[pkk].zips[index].zip = zip;
      }
    });
  } else {
    if (pkgs_[obj.name]) {
      pkgs_[obj.name].files.forEach(file => zip.addLocalFile(file.path, '', file.name));
      pkgs_[obj.name].zips.forEach(z => zip.addFile(`packages/${getShortType(z.type)}${z.subType ? z.subType + '_' : ''}${z.name.toLowerCase()}_${version}.zip`, getZipData({type: z.type, subType: z.subType, name: z.name})));
    }
    packagesZips.push({name: obj.name, data: zip.toBuffer(), type: obj.type, subType: obj.subType, zip});
  }
}

(async function exec() {
  getDirectories(`${process.cwd()}/src`).forEach(dir => {
    if (dir === 'packages') return;
    if (dir === 'libraries') {
      getDirectories(`${process.cwd()}/src/libraries`).forEach(lib => processes.push({ name: lib, type: 'libraries', subType: '' }))
    }
    if (dir === 'plugins') {
      getDirectories(`${process.cwd()}/src/plugins`).forEach(type => {
        getDirectories(`${process.cwd()}/src/plugins/${type}`).forEach(name => {
          let shallZip = true;
          doNotZip.forEach(dontZip => {
            if (`${type}/${name}`.startsWith(dontZip)) shallZip = false;
          });
          if (!shallZip) return;

          processes.push({ name: name, type: 'plugins', subType: type });
        })
      })
    }
  });

  await Promise.all(processes.map(pk => zipExtension(pk)));

  let pkgs = [];
  getDirectories(`${process.cwd()}/src/packages`).forEach(dir => pkgs.push({ name: dir, type: 'packages', subType: null }));

  await Promise.all(pkgs.map(pk => zipExtension(pk)));

  packagesZips.forEach(z => z.zip.writeZip(`site/dist/pkg_${z.name.toLowerCase()}_${version}.zip`, z.data));
})();
