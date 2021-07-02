<?php
/* This file has been prefixed by <PHP-Prefixer> for "PHP-Prefixer Getting Started" */

namespace Ttc\Intervention\Image\Imagick\Commands;

use Ttc\Intervention\Image\Commands\AbstractCommand;

class FlipCommand extends AbstractCommand
{
    /**
     * Mirrors an image
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $mode = $this->argument(0)->value('h');

        if (in_array(strtolower($mode), [2, 'v', 'vert', 'vertical'])) {
            // flip vertical
            return $image->getCore()->flipImage();
        } else {
            // flip horizontal
            return $image->getCore()->flopImage();
        }
    }
}
