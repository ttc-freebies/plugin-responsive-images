const { createHash } = require('crypto');
const { createReadStream } = require('fs');

module.exports.checksum = (location, algorithm = 'sha256') => {
  return new Promise(function(resolve, reject){
    if (!location) {
      reject(new Error('No location provided'));
    }
    const hash = createHash(algorithm);
    const input = createReadStream(location);
    input.on('readable', () => {
      const data = input.read();
      if (data) {
        hash.update(data);
      } else {
        resolve(hash.digest('hex'))
      }
    });
  })
}
