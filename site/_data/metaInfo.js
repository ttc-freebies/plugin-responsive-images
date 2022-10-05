
const pakage = require('../../package.json');

module.exports = () => {
  return {
    url: 'https://responsive-images.dgrammatiko.dev',
    version: pakage.version,
    title: 'Responsive Images'
  };
}
