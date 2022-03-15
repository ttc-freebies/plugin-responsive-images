const exec = require('util').promisify(require('child_process').exec);
const { mkdir, rm, rename } = require('fs').promises;

(async () => {
  await mkdir('build_tmp', { recursive: true });
  await mkdir('src/libraries/Ttc/vendor');
  await exec('git clone --depth 1 --branch main git@github.com:ttc-freebies/ttc--intervention-image.git build_tmp');
  await rename('build_tmp/vendor', 'src/libraries/Ttc/vendor');
  await rm('build_tmp', { recursive: true });
})()
