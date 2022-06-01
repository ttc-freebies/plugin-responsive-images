module.exports = async () => {
  return [
    {
      version: '4.0.0',
      type: 'stable',
      bugs: [],
      features: [
        'Cache invalidation',
        'Use of JLayouts for media fields or other layout based images',
        'Functionality to process textarea fields in any component or module',
        'Support Avif files',
      ],
      changes: [],
      notes: ['Initial release (for Joomla v4.x, the plugin exists since 2016...)'],
    },
    {
      version: '4.0.1',
      type: 'stable',
      bugs: ['Wrong paths for the Media-action prevents generating sourcsets'],
      features: [],
      changes: [],
      notes: [],
    },
    {
      version: '4.0.6',
      type: 'stable',
      bugs: [],
      features: [],
      changes: [
        'Support the Joomla JLayout image',
        'Update all the composer packages to their latest versions'
      ],
      notes: [],
    },
    {
      version: '4.0.7',
      type: 'stable',
      bugs: [
        'Fix an incompatibility for Windows OS, thanks thanks @sashau, <a href="https://github.com/ttc-freebies/plugin-responsive-images/pull/140">Fix</a>',
      ],
      features: [],
      changes: [

      ],
      notes: [],
    },
    {
      version: '4.0.8',
      type: 'stable',
      bugs: [
        'Fix an issue with some attributes, thanks thanks @sashau, <a href="https://github.com/ttc-freebies/plugin-responsive-images/pull/141">Fix</a>',
        'Fix an issue were the user preferences would be ignorred (for WebP images)',

      ],
      features: [],
      changes: [

      ],
      notes: [],
    },
    {
      version: '4.1.0-beta.1',
      type: 'Beta',
      bugs: [],
      features: [
        'Added suppor for the attribute `decoding`, set to `async` by default',
      ],
      changes: [
        'Switch to own tools for the prefixing of the 3rd party libraries',
        'Switch the library to use PHP namespaces',
      ],
      notes: [
        'Only for testing purposes, not for production',
      ],
    },
    {
      version: '4.1.0-beta.2',
      type: 'Beta',
      bugs: [
        'Fixed an issue with the urls containig double slashes',
      ],
      features: [],
      changes: [],
      notes: [
        'Only for testing purposes, not for production. Also if you installed `beta.1` please remove the folder `media/cached-resp-images` to fix the urls',
      ],
    },
    {
      version: '4.1.0-beta.3',
      type: 'Beta',
      bugs: [
        'Fixed an issue with `getimagesize` not returnng the channels of PNG images',
      ],
      features: [],
      changes: [],
      notes: [
        'Only for testing purposes, not for production.',
      ],
    },
    {
      version: '4.1.0',
      type: 'stable',
      bugs: [
        'Fixed an issue with the urls containig double slashes',
        'Fixed an issue with `getimagesize` not returnng the channels of PNG images',
      ],
      features: [
        'Added suppor for the attribute `decoding`, set to `async` by default',
      ],
      changes: [
        'Switch to own tools for the prefixing of the 3rd party libraries',
        'Switch the library to use PHP namespaces',
      ],
      notes: [],
    },
    {
      version: '4.1.1',
      type: 'stable',
      bugs: [
        'Fixed an issue for the images with spaces in their filename',
      ],
      features: [],
      changes: [],
      notes: [],
    }
  ];
}
