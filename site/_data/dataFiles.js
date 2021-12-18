const { readFile, readdir } = require('fs').promises;
const { extname, join } = require('path');

/**
 * Get files recursively
 *
 * @param {string} path The path
 */
async function getFiles(path) {
  const entries = await readdir(path, { withFileTypes: true });

  // Get files within the current directory
  const files = entries
    .filter(file => (!file.isDirectory() && ['.php', '.xml'].includes(extname(file.name))))
    .map(file => `${path}${file.name}`);

  // Get folders within the current directory
  const folders = entries.filter(folder => folder.isDirectory());

  for (const folder of folders) {
    // Recursive
    files.push(...await getFiles(`${path}${folder.name}/`));
  }

  return files;
}

const getData = async (files, file) => {
  files[file.replace(`./src/plugins/system/responsiveplgoverrides/`, '')] = await readFile(join(`${process.cwd()}`, file), 'utf8');
}

module.exports = async function() {
  const files = {};
  const processes = [];
  const filesSrcs = await getFiles('./src/plugins/system/responsiveplgoverrides/');

  for (const file of filesSrcs) {
    processes.push(getData(files, file));
  }

  await Promise.all(processes);

  return JSON.stringify(files);
}
