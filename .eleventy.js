const pluginSyntaxHighlight = require("@11ty/eleventy-plugin-syntaxhighlight");
const eleventyNavigationPlugin = require("@11ty/eleventy-navigation");
let Nunjucks = require('nunjucks');
const anchor = require("markdown-it-anchor");
// const htmlmin = require("html-minifier");
// const codepenIt = require("11ty-to-codepen");
const Image = require("@11ty/eleventy-img");

async function imageShortcode(src, alt, sizes) {
  let metadata = await Image(src, {
    widths: [300, 600, 1000],
    formats: ["avif", "jpeg"],
    outputDir: "./_site/images/",
    urlPath: "/images/"
  });

  let imageAttributes = {
    alt,
    sizes,
    loading: "lazy",
    decoding: "async",
  };

  // You bet we throw an error on missing alt in `imageAttributes` (alt="" works okay)
  return Image.generateHTML(metadata, imageAttributes);
}

let nunjucksEnvironment = new Nunjucks.Environment(
  new Nunjucks.FileSystemLoader('./site/_includes')
);

module.exports = function (eleventyConfig) {
  const { Liquid } = require('liquidjs');
  let options = {
    extname: ".liquid",
    dynamicPartials: true,
    strictFilters: false, // renamed from `strict_filters` in Eleventy 1.0
    root: ["site/_includes"]
  };

  eleventyConfig.setLibrary("liquid", new Liquid(options));
  // eleventyConfig.setLibrary('njk', nunjucksEnvironment);
  eleventyConfig.setDataDeepMerge(true);
  eleventyConfig.addPassthroughCopy({ "site/images": "images" });
  eleventyConfig.addPassthroughCopy({ "site/dist": "dist" });

  eleventyConfig.addNunjucksAsyncShortcode("image", imageShortcode);
  eleventyConfig.addLiquidShortcode("image", imageShortcode);
  eleventyConfig.addJavaScriptFunction("image", imageShortcode);
    // eleventyConfig.addPairedShortcode("codepen", codepenIt);
  eleventyConfig.addShortcode("img", function(name, twitterUsername) {
    return `<svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 192 144" style="width: 100%; height: auto;">
<defs/>
<g fill="currentColor" stroke="#000" stroke-width=".24">
  <path d="M31.543 89.65L.112 68.51l31.431-21.14v5.892L8.899 68.51l22.644 15.212v5.928zM47.02 46.585h-4.673v-6.142h4.673v6.142zm-.239 43.065h-4.195c.018-.381.04-2.726.067-7.035.026-4.309.04-7.868.04-10.677 0-2.452-.005-5.148-.014-8.088-.009-2.94-.04-6.386-.093-10.338h4.195c-.036 3.428-.067 6.636-.093 9.624a1067.977 1067.977 0 00.026 21.336c.045 3.357.067 5.083.067 5.178zM94.829 89.65h-4.194c.018-.262.04-1.928.067-4.999a1028.035 1028.035 0 00.026-13.945c-.009-2.44-.022-4.267-.04-5.481-.053-2.857-.473-4.833-1.261-5.928-.787-1.095-2.004-1.642-3.65-1.642-.973 0-2.128.321-3.464.964-1.336.643-2.845 1.642-4.526 2.999 0 .119.004.357.013.715.009.357.013.821.013 1.392 0 1.929-.009 3.887-.026 5.874a630.22 630.22 0 00-.027 5.696c0 3.952.022 7.273.067 9.963.044 2.69.066 4.154.066 4.392h-4.194c0-.262.017-1.702.053-4.321.035-2.618.053-5.713.053-9.284 0-1.19-.005-2.994-.013-5.41a524.52 524.52 0 00-.04-5.339 26.8 26.8 0 00-.213-2.999c-.124-.952-.354-1.762-.69-2.428-.372-.738-.849-1.292-1.433-1.661-.584-.369-1.416-.553-2.496-.553-.991 0-2.15.387-3.477 1.16-1.328.774-2.85 1.887-4.566 3.339 0 .405-.009 1.922-.027 4.553-.018 2.63-.026 4.779-.026 6.445 0 2.833.022 6.232.066 10.195.044 3.964.066 6.065.066 6.303h-4.194c0-.19.018-1.577.053-4.16.036-2.583.053-6.862.053-12.838 0-1.952-.004-4.582-.013-7.891s-.04-7.059-.093-11.249h4.141l-.026 4.107.026.036c1.186-1.143 2.677-2.274 4.473-3.393 1.797-1.119 3.385-1.678 4.765-1.678 1.735 0 3.182.488 4.341 1.464 1.159.976 1.986 2.214 2.482 3.714h.053c2.035-1.69 3.907-2.976 5.614-3.857 1.708-.881 3.164-1.321 4.367-1.321 2.443 0 4.349.958 5.721 2.875 1.372 1.916 2.057 4.505 2.057 7.766 0 2.524-.013 4.648-.039 6.374-.027 1.726-.04 3.553-.04 5.482 0 3.999.022 7.38.066 10.141.044 2.762.066 4.238.066 4.428zM124.694 53.191c0 .524-.013 2.881-.04 7.07-.026 4.19-.04 8.178-.04 11.963 0 2.571.009 5.19.027 7.856.018 2.666.027 5.106.027 7.32 0 4.071-.518 7.261-1.553 9.57-1.036 2.31-2.509 4.155-4.42 5.535-1.505 1.048-3.336 1.875-5.495 2.482-2.16.607-4.779.911-7.858.911l-.425-4.535c2.071-.048 3.929-.197 5.575-.447 1.646-.25 3.141-.708 4.486-1.375 2.071-1.047 3.513-2.434 4.327-4.16.814-1.726 1.239-3.826 1.274-6.302h-1.274c-2.106 0-4.274-.34-6.504-1.018-2.23-.679-4.132-1.756-5.707-3.232-1.558-1.404-2.823-3.237-3.796-5.499-.974-2.261-1.46-5.059-1.46-8.392 0-3.332.681-6.272 2.044-8.82 1.362-2.547 3.185-4.57 5.468-6.07a15.624 15.624 0 015.323-2.232c1.92-.417 4.243-.625 6.968-.625h3.053zm-4.062 18.997c0-1.714-.004-3.856-.013-6.427-.009-2.571-.031-5.25-.066-8.035h-.69c-1.646 0-3.084.143-4.314.429-1.23.285-2.526.821-3.889 1.607-1.805 1.047-3.186 2.582-4.141 4.606-.956 2.023-1.434 4.309-1.434 6.856 0 2.619.474 4.857 1.42 6.713.947 1.857 2.217 3.298 3.81 4.321 1.079.691 2.331 1.25 3.756 1.679a15.65 15.65 0 004.526.642h.982c0-.285.009-1.613.027-3.981.018-2.369.026-5.172.026-8.41zM136.221 90l-4.605.171 27.161-49.911 3.939-.063L136.221 90zM160.499 89.65l31.431-21.14-31.431-21.14v5.892l22.644 15.248-22.644 15.212v5.928z"/>
</g>
</svg>`;
  });

  // Filter source file names using a glob
  eleventyConfig.addCollection("docs", function (collection) {
    return collection.getFilteredByGlob(['site/docs/*.md']);
  });

  eleventyConfig.addPlugin(pluginSyntaxHighlight);
  eleventyConfig.addPlugin(eleventyNavigationPlugin);
  // eleventyConfig.addTransform("htmlmin", function (content, outputPath) {
  //   if (outputPath.endsWith(".html")) {
  //     let minified = htmlmin.minify(content, {
  //       useShortDoctype: true,
  //       removeComments: true,
  //       collapseWhitespace: true,
  //     });
  //     return minified;
  //   }
  //   return content;
  // });

  eleventyConfig.setLibrary(
    "md",
    require("markdown-it")({
      html: true,
      breaks: true,
      linkify: true,
    })
  .use(anchor, {
    permalink: anchor.permalink.headerLink(),
    permalinkClass: "direct-link",
    permalinkSymbol: "¶",
  })
  );

  return {
    pathPrefix: "/",
    passthroughFileCopy: true,
    dataTemplateEngine: 'njk',
    markdownTemplateEngine: "liquid",
    htmlTemplateEngine: "njk",
    dir: {
      data: `_data`,
      input: 'site',
      includes: `_includes`,
      layouts: `_includes`,
      output: '_site',
    },
  };
};
