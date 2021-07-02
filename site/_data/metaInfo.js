
const pakage = require('../../package.json');

module.exports = () => {
  return {
    url: 'https://responsive-images.dgrammatiko.dev',
    repo: pakage.data.repo,
    version: pakage.version,
    title: 'Responsive Images'
  };
}
