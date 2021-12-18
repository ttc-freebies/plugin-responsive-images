<?php
/* This file has been prefixed by <PHP-Prefixer> for "Responsive Images" */

namespace Ttc\Intervention\Image\Gd\Commands;

use Ttc\Intervention\Image\Commands\AbstractCommand;

class PixelateCommand extends AbstractCommand
{
    /**
     * Applies a pixelation effect to a given image
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $size = $this->argument(0)->type('digit')->value(10);

        return imagefilter($image->getCore(), IMG_FILTER_PIXELATE, $size, true);
    }
}
