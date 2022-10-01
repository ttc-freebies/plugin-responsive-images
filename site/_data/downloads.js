

module.exports = async () => {
  const fn = require('./releases');
  const rels = await fn();
  // console.table(xx)
  return rels.reverse();
}
