<?php
/* This file has been prefixed by <PHP-Prefixer> for "PHP-Prefixer Getting Started" */

namespace Ttc\Intervention\Image\Imagick\Commands;

use Ttc\Intervention\Image\Commands\AbstractCommand;

class OpacityCommand extends AbstractCommand
{
    /**
     * Defines opacity of an image
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $transparency = $this->argument(0)->between(0, 100)->required()->value();
        
        $transparency = $transparency > 0 ? (100 / $transparency) : 1000;

        return $image->getCore()->evaluateImage(\Imagick::EVALUATE_DIVIDE, $transparency, \Imagick::CHANNEL_ALPHA);
    }
}
