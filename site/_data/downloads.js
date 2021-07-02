const fs = require('fs');
const { extname } = require('path');
const semver = require('semver')

const getSortedFiles = async (dir) => {
  const files = await fs.promises.readdir(dir);

  return files
    .map(fileName => ({
      name: fileName,
      time: fs.statSync(`${dir}/${fileName}`).mtime.getTime(),
      version: fileName.replace('pkg_responsive_', '').replace('.zip', '')
    }))
    .filter(x => extname(x.name) === '.zip')
    // .sort((a, b) => a.time - b.time)
    .sort((a, b) => semver.compare(b.version, a.version, {
        loose: true,
        includePrerelease: true
    }))
};

module.exports = async () => {
  return await getSortedFiles(`${process.cwd()}/site/dist`);
}
