const { default: axios } = require('axios');
const AdmZip = require('adm-zip');
const { existsSync, mkdirSync, readFileSync, writeFileSync } = require('fs');
const { resolve, sep } = require('path');
const {lstat, mkdir, readdir } = require('fs/promises');
const symlinkDir = require('symlink-dir')

// Options
const r =  {
  destinationPath: 'www',
  joomlaVersion: '4.2.2',
  pathsForSymLinks: [
    // PHP
    'src/libraries/Ttc',
    'src/plugins/content/responsive',
    'src/plugins/media_action/responsive',

    // Media
    'media/templates/site'
  ]
};

/**
 * Logic starts here
 */
const root = process.cwd();
const logger = { log: (value) => process.stdout.write(`${value}\n`) };

const symLinkDir = async (input, isMedia= false) => {
  if (['.DS_Store'].includes(input)) return;

const dest = isMedia ? resolve(process.cwd(), `${r.destinationPath}${sep}${input.replace(`media_src${sep}`, 'media')}`) : resolve(process.cwd(), `${r.destinationPath}${sep}templates${input.replace('src/templates/site', '')}`);

  // const dest = input.startsWith('src/templates/site') ?
  //   resolve(process.cwd(), `${r.destinationPath}${sep}templates${input.replace('src/templates/site', '')}`) :
  //   resolve(process.cwd(), `${r.destinationPath}${sep}${input.replace(`src${sep}`, '')}`);

  let stat;
  try {
    stat = await lstat(dest);
  } catch(err) {
    // console.log(`${dest} not found, skipping`);
  }

  if (!stat || !stat.isSymbolicLink) {
    console.log(`Linking ${input} -> ${dest}`);
    await symlinkDir(`${resolve(process.cwd(), `${input}`)}`, dest)
  } else {
    console.log(`Link already exists, skipping: ${input}`);
  }
};

const init = async () => {
  if (!existsSync(resolve(root, r.destinationPath))) {
    await mkdir(`${r.destinationPath}`, { recursive: true });
    //https://github.com/joomla/joomla-cms/releases/download/4.1.2/Joomla_4.1.2-Stable-Full_Package.zip
    const {data} = await axios.get(
      `https://github.com/joomla/joomla-cms/releases/download/${r.joomlaVersion}/Joomla_${r.joomlaVersion}-Stable-Full_Package.zip`,
      {
          responseType: 'arraybuffer',
      });

    const zip = new AdmZip(data);
    await zip.extractAllTo(resolve(root, r.destinationPath), true);

    await mkdir(`${r.destinationPath}${sep}libraries${sep}Ttc`, { recursive: true });
  } else {
    logger.log('Joomla installation already exists, skipping clonning...');
  }
};

const resolveExts = async (dir) => {
  let currentDir;
  try {
    currentDir = await lstat(resolve(process.cwd(), dir));
  } catch(err) {
    console.log(`${dir} not found, skipping`);
  }

  if (currentDir && currentDir.isDirectory()) {
    const subDirs = await readdir(resolve(root, dir), { withFileTypes: true });
    await Promise.all(subDirs.map((directory) => symLinkDir(`${dir}${sep}${directory.name}`))).catch(err => console.log(err));
  }
};

init();
r.pathsForSymLinks.forEach(async (dir) => {
  await resolveExts(dir);
});

if (!existsSync(resolve(root, 'www/layouts/libraries/ttc'))) {
  mkdirSync(resolve(root, 'www/layouts/libraries/ttc'), { recursive: true });
}

(async () => {
  const subDirs = await readdir(resolve(root, 'src/layouts/libraries/ttc'), { withFileTypes: true });

  subDirs.forEach((file) => {
    console.dir(file)
    if (['.DS_Store'].includes(file.name)) return;
    const data = readFileSync(resolve(root, `src/layouts/libraries/ttc/${file.name}`), 'utf8');
    writeFileSync(resolve(root, `www/layouts/libraries/ttc/${file.name}`), data, 'utf8');
  });
})();
