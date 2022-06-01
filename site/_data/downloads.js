const fs = require('fs');
const { extname } = require('path');
const crypto = require('crypto');
const semver = require('semver')

const getHash = (file) => {
  const hash = crypto.createHash('sha384');
  hash.update(fs.readFileSync(file));
  return hash.digest('hex');
};

const getSortedFiles = async (dir) => {
  const files = await fs.promises.readdir(dir);

  return files
    .map(fileName => ({
      name: fileName,
      time: fs.statSync(`${dir}/${fileName}`).mtime.getTime(),
      version: fileName.replace('pkg_responsive_', '').replace('.zip', ''),
      sha384: getHash(`${dir}/${fileName}`),
    }))
    .filter(x => extname(x.name) === '.zip')
    // .sort((a, b) => a.time - b.time)
    .sort((a, b) => semver.compare(b.version, a.version, {
        loose: true,
        includePrerelease: true
    }))
};

module.exports = async () => {
  const xx = await getSortedFiles(`${process.cwd()}/site/dist`);
  // console.table(xx)
  return xx;
}
