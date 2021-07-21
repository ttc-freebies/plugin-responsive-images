const { readFile } = require('fs').promises;
const rollup = require('rollup');
const { nodeResolve } = require('@rollup/plugin-node-resolve');
const { terser } = require("rollup-plugin-terser");
const template = require("rollup-plugin-html-literals");
const { existsSync, mkdirSync } = require('fs');
const postcss  = require('postcss')


module.exports = async () => {
    // eslint-disable-next-line no-console
  console.log('Building js..');

  try {
    const bundle = await rollup.rollup({
        input: `${process.cwd()}/site/src_media/js/downloader.js`,
        plugins: [
          template(),
          nodeResolve(),
          terser()
        ]
      });

    await bundle.write({
        format: 'es',
        sourcemap: false,
        dir: `${process.cwd()}/_site/js`,
        chunkFileNames: '[name].[hash].js',
      });

    // closes the bundle
    await bundle.close();

    if (!existsSync(`${process.cwd()}/site/css`)) {
      mkdirSync(`${process.cwd()}/site/css`, {recursive: true});
    }

    const result = await postcss(
      [
        require('postcss-import')({
        path: ['site/src_media/css']}),
      ]
    ).process((await readFile(`${process.cwd()}/site/src_media/css/base.css`, {encoding: 'utf8'})));

    if (result) {
      return result;
    }
  } catch (error) {
    // eslint-disable-next-line no-console
    console.error(error);
    process.exit(1);
  }
  return '';
}
