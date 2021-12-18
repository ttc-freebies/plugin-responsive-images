<?php
/* This file has been prefixed by <PHP-Prefixer> for "Responsive Images" */

namespace Ttc\Intervention\Image\Gd\Commands;

use Ttc\Intervention\Image\Commands\AbstractCommand;

class BrightnessCommand extends AbstractCommand
{
    /**
     * Changes image brightness
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $level = $this->argument(0)->between(-100, 100)->required()->value();

        return imagefilter($image->getCore(), IMG_FILTER_BRIGHTNESS, ($level * 2.55));
    }
}
