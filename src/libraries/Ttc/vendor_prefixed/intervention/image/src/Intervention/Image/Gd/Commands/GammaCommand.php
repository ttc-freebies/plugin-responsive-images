<?php
/* This file has been prefixed by <PHP-Prefixer> for "Responsive Images" */

namespace Ttc\Intervention\Image\Gd\Commands;

use Ttc\Intervention\Image\Commands\AbstractCommand;

class GammaCommand extends AbstractCommand
{
    /**
     * Applies gamma correction to a given image
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $gamma = $this->argument(0)->type('numeric')->required()->value();

        return imagegammacorrect($image->getCore(), 1, $gamma);
    }
}
