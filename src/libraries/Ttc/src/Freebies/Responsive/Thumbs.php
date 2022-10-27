<?php
/**
 * @copyright   (C) 2022 Dimitrios Grammatikogiannis
 * @license     GNU General Public License version 2 or later
 */

namespace Ttc\Freebies\Responsive;

class Thumbs
{
  private $driver = 'gd';

  public function __construct($driver = 'gd')
  {
    $this->driver = $this->getGraphicsDriver($driver);
  }

  public function create($img, $options, $srcSets)
  {
    if ($this->driver === 'imagick') return $this->createImagic($img, $options, $srcSets);
    elseif ($this->driver === 'gd') return $this->createGd($img, $options, $srcSets);
    else return;
  }

  private function createGd($img, $options, $srcSets)
  {
    for ($i = 0, $l = count($options->validSizes); $i < $l; $i++) {
      if ($options->scaleUp || ($img->width >= (int) $options->validSizes[$i])) {
        $fileSrc = 'media/cached-resp-images/' . $img->dirname . '/' . $img->filename . $options->separator . trim($options->validSizes[$i]);

        // Load the image
        if ($img->type === 'jpeg') {
          ini_set('gd.jpeg_ignore_warning', 1);
          $sourceImage = \imagecreatefromjpeg(JPATH_ROOT . '/' . $img->dirname . '/' . $img->filename . '.' . $img->extension);
        }
        if ($img->type === 'png') {
          $sourceImage = \imagecreatefrompng(JPATH_ROOT . '/' . $img->dirname . '/' . $img->filename . '.' . $img->extension);
        }
        if (!$sourceImage) return;

        $orgWidth    = \imagesx($sourceImage);
        $orgHeight   = \imagesy($sourceImage);
        $thumbHeight = floor($orgHeight * ((int) $options->validSizes[$i] / $orgWidth));
        $destImage   = \imagecreatetruecolor((int) $options->validSizes[$i], $thumbHeight);
        \imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, (int) $options->validSizes[$i], $thumbHeight, $orgWidth, $orgHeight);
        \imagedestroy($sourceImage);

        // Save the image
        if ($img->type === 'jpeg') {
          \imagejpeg($destImage, JPATH_ROOT . '/' . $fileSrc . '.' . $img->extension);
          $hash = hash_file('md5', JPATH_ROOT . '/' . $fileSrc . '.' . $img->extension, false);
          $srcSets->base->srcset[$options->validSizes[$i]] = str_replace(' ', '%20', $fileSrc) . '.' . $img->extension . '?version=' . $hash . ' ' . $options->validSizes[$i] . 'w';
        } else if ($img->type === 'png') {
          \imagepng($destImage, JPATH_ROOT . '/' . $fileSrc . '.' . $img->extension);
          $hash = hash_file('md5', JPATH_ROOT . '/' . $fileSrc . '.' . $img->extension, false);
          $srcSets->base->srcset[$options->validSizes[$i]] = str_replace(' ', '%20', $fileSrc) . '.' . $img->extension . '?version=' . $hash . ' ' . $options->validSizes[$i] . 'w';
        }

        if ($options->enableWEBP && \imagewebp($destImage, JPATH_ROOT . '/' . $fileSrc . '.webp', $options->qualityWEBP)) {
          $hash = hash_file('md5', JPATH_ROOT . '/' . $fileSrc . '.webp', false);
          if (!isset($srcSets->webp)) $srcSets->webp = (object) ['srcset' => []];
          $srcSets->webp->srcset[$options->validSizes[$i]] = str_replace(' ', '%20', $fileSrc) . '.webp' . '?version=' . $hash . ' ' . $options->validSizes[$i] . 'w';
        }
        if ($options->enableAVIF && \imagewebp($destImage, JPATH_ROOT . '/' . $fileSrc . '.avif', $options->qualityAVIF)) {
          $hash = hash_file('md5', JPATH_ROOT . '/' . $fileSrc . '.avif', false);
          if (!isset($srcSets->avif)) $srcSets->avif = (object) ['srcset' => []];
          $srcSets->avif->srcset[$options->validSizes[$i]] = str_replace(' ', '%20', $fileSrc) . '.avif' . '?version=' . $hash . ' ' . $options->validSizes[$i] . 'w';
        }

        \gc_collect_cycles();
      }
    }

    $this->createJSON($img, $srcSets);
  }

  private function createImagic($img, $options, $srcSets)
  {
    for ($i = 0, $l = count($options->validSizes); $i < $l; $i++) {
      if ($options->scaleUp || ($img->width >= (int) $options->validSizes[$i])) {
        $fileSrc = 'media/cached-resp-images/' . $img->dirname . '/' . $img->filename . $options->separator . trim($options->validSizes[$i]);
        $image = new \Imagick;

        // Load the image
        $image->readImage(JPATH_ROOT . '/' . $img->dirname . '/' . $img->filename . '.' . $img->extension);
        $image->resizeImage((int) $options->validSizes[$i], 0, \Imagick::FILTER_LANCZOS, 1);

        // Save the image
        if ($img->type === 'jpeg') $image->setimageformat('JPEG');
        else $image->setImageFormat('PNG');

        $image->setImageCompressionQuality($options->qualityJPG);
        $image->stripImage();
        $image->writeImage(JPATH_ROOT . '/' . $fileSrc . '.' . $img->extension);
        $hash = hash_file('md5', JPATH_ROOT . '/' . $fileSrc . '.' . $img->extension, false);
        $srcSets->base->srcset[$options->validSizes[$i]] = str_replace(' ', '%20', $fileSrc) . '.' . $img->extension . '?version=' . $hash . ' ' . $options->validSizes[$i] . 'w';

        if ($options->enableWEBP && $image->queryFormats('WEBP')) {
          $image->setImageFormat('WEBP');
          $image->setImageCompressionQuality($options->qualityWEBP);
          $image->writeImage(JPATH_ROOT . '/' . $fileSrc . '.webp');
          $hash = hash_file('md5', JPATH_ROOT . '/' . $fileSrc . '.webp', false);
          if (!isset($srcSets->webp)) $srcSets->webp = (object) ['srcset' => []];
          $srcSets->webp->srcset[$options->validSizes[$i]] = str_replace(' ', '%20', $fileSrc) . '.webp' . '?version=' . $hash . ' ' . $options->validSizes[$i] . 'w';
        }

        if ($options->enableAVIF && $image->queryFormats('AVIF')) {
          $image->setImageFormat('AVIF');
          $image->setImageCompressionQuality($options->qualityAVIF);
          $image->writeImage(JPATH_ROOT . '/' . $fileSrc . '.avif');
          $hash = hash_file('md5', JPATH_ROOT . '/' . $fileSrc . '.avif', false);
          if (!isset($srcSets->avif)) $srcSets->avif = (object)['srcset' => []];
          $srcSets->avif->srcset[$options->validSizes[$i]] = str_replace(' ', '%20', $fileSrc) . '.avif' . '?version=' . $hash . ' ' . $options->validSizes[$i] . 'w';
        }

        $image->destroy();
        \gc_collect_cycles();
      }
    }


    $this->createJSON($img, $srcSets);
  }

  private function createJSON($img, $srcSets) {
    if (!is_dir(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $img->dirname)) mkdir(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $img->dirname, 0755, true);
    file_put_contents(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $img->dirname . '/' . $img->filename . '.json', \json_encode($srcSets));
  }

  private function getGraphicsDriver(string $preferedDriver)
  {
    if (extension_loaded($preferedDriver)) return $preferedDriver;

    if (extension_loaded('imagick')) return 'imagick';
    if (extension_loaded('gd')) return 'imagick';

    throw new \RuntimeException('GD library is required for manipulation of images.');
  }
}

/**
$img = (object) [
    'dirname'   => string,
    'filename'  => string,
    'extension' => string,
    'width'     => int,
    'height'    => int,
    'type'      => jpg||png,
];
$options = (object) [
    'destination' => 'media/cached-resp-images/',
    'enableWEBP'  => bool,
    'enableAVIF'  => bool,
    'qualityJPG'  => int,
    'qualityWEBP' => int,
    'qualityAVIF' => int,
    'scaleUp'     => bool,
    'seperator'   => '_,
    'validSizes'  => array,
]
 */
