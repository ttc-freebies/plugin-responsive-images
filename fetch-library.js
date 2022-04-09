const exec = require('util').promisify(require('child_process').exec);
const { mkdir, rm, rename } = require('fs').promises;

(async () => {
  await mkdir('build_tmp', { recursive: true });
  await exec('git clone --depth 1 --branch gh-pages git@github.com:ttc-freebies/ttc-common-library.git build_tmp');
})()
