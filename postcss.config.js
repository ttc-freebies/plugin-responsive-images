module.exports = {
  plugins: [
    require('postcss-import')({
      path: ['site/_includes/css'],
    }),
  ],
}
