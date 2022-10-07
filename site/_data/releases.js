const fs = require('fs');
const crypto = require('crypto');

module.exports = async () => {
  const dir = 'site/dist';
  let rels = JSON.parse(fs.readFileSync('releases.json'));
  const files = await fs.promises.readdir(dir);

  rels.map(rel => {
    if (files.includes(`pkg_responsive_${rel.version}.zip`)) {
      rel.sha384 = crypto.createHash('sha384').update(fs.readFileSync(`${dir}/pkg_responsive_${rel.version}.zip`)).digest('hex');
    }
  });

  rels = rels.filter(rel => rel.type === 'stable');
  return rels.reverse();
}
