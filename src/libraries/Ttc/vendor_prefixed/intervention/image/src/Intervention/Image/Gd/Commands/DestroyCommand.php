<?php
/* This file has been prefixed by <PHP-Prefixer> for "Responsive Images" */

namespace Ttc\Intervention\Image\Gd\Commands;

use Ttc\Intervention\Image\Commands\AbstractCommand;

class DestroyCommand extends AbstractCommand
{
    /**
     * Destroys current image core and frees up memory
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        // destroy image core
        imagedestroy($image->getCore());

        // destroy backups
        foreach ($image->getBackups() as $backup) {
            imagedestroy($backup);
        }

        return true;
    }
}
