<?php
/* This file has been prefixed by <PHP-Prefixer> for "PHP-Prefixer Getting Started" */

namespace Ttc\Intervention\Image;

use League\Container\ServiceProvider\AbstractServiceProvider;

class ImageServiceProviderLeague extends AbstractServiceProvider
{
    /**
     * @var array $config
     */
    protected $config;

    /**
     * @var array $provides
     */
    protected $provides = [
        'Ttc\Intervention\Image\ImageManager'
    ];

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * Register the server provider.
     *
     * @return void
     */
    public function register()
    {
        $this->getContainer()->share('Ttc\Intervention\Image\ImageManager', function () {
            return new ImageManager($this->config);
        });
    }
}
