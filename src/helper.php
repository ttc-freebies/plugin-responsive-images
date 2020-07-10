<?php
/**
 * @package     ttc-freebies.plugin-responsive-images
 *
 * @copyright   Copyright (C) 2020 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Freebies\Responsive;

defined('_JEXEC') || die();

require_once 'vendor/autoload.php';

use Intervention\Image\ImageManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Content responsive images plugin
 */
class Helper {
  private $sizeSplit   = '_';
  private $baseDir     = '';
  private $quality     = 85;
  private $scaleUp     = false;

  public function __construct() {
    if ($this->baseDir === '') {
      $this->baseDir = JPATH_ROOT . '/' .
        Factory::getApplication('site')
          ->getParams('com_media')
          ->get('file_path', 'images');
    }

    $plugin            = PluginHelper::getPlugin('content', 'responsive');
    $this->params      = new Registry($plugin->params);
    $this->quality     = (int) $this->params->get('quality', 85);
    $this->scaleUp     = (bool) $this->params->get('scaleUp', 0);
    $this->sizeSplit   = '_';
  }

  /**
   * Takes an image tag and returns the picture tag
   *
   * @param string  $image        the image tag
   * @param array   $breakpoints  the breakpoints
   *
   * @return string
   *
   * @throws \Exception
   */
  public function transformImage($image, $breakpoints) {
    $validSize = array(200, 320, 480, 768, 992, 1200, 1600, 1920);
    $validExt = array('jpg', 'jpeg', 'png');

    if (!is_array($breakpoints)) {
      return;
    }

    // Get the original path
    preg_match('/src\s*=\s*"(.+?)"/', $image, $match);
    $originalImagePath = $match[1];
    $path = realpath(JPATH_ROOT . (substr($originalImagePath, 0, 1) === '/' ? $originalImagePath : '/'. $originalImagePath));

    if (strpos($path, $this->baseDir) !== 0 || strpos($path, $this->baseDir) === false) {
      return $image;
    }

    $originalImagePathInfo = pathinfo($originalImagePath);

    // Bail out if no images supported
    if (!in_array(mb_strtolower($originalImagePathInfo['extension']), $validExt) || !file_exists(JPATH_ROOT . '/' . $originalImagePath)) {
      return $image;
    }

    if (!is_dir(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'])) {
      if (
        !@mkdir(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'], 0755, true)
        && !is_dir(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'])
      ) {
        throw new \RuntimeException('There was a file permissions problem in folder \'media\'');
      }
    }

    // If the responsive image doesn't exist we will create it
    if (
      !file_exists(
        JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .
        $originalImagePathInfo['filename'] . $this->sizeSplit . $validSize[0] . '.' . $originalImagePathInfo['extension']
      )
    ) {
      self::createImages(
        $validSize,
        $originalImagePathInfo['dirname'],
        $originalImagePathInfo['filename'],
        $originalImagePathInfo['extension']
      );
    }

    // If the responsive image exists use it
    if (
      file_exists(
        JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .
        $originalImagePathInfo['filename'] . $this->sizeSplit . $validSize[0] . '.' . $originalImagePathInfo['extension']
      )
    ) {
      $srcSets = self::buildSrcset(
        $breakpoints,
        $originalImagePathInfo['dirname'],
        $originalImagePathInfo['filename'],
        $originalImagePathInfo['extension'],
        $this->sizeSplit
      );

      if (empty($srcSets)) {
        return $image;
      }

      $srcSets = array_reverse($srcSets);
      $output = '<picture class="responsive-image">';
      $baseType = array('sizes' => array(), 'srcset' => array(), 'type' => '');
      $webpType = array('sizes' => array(), 'srcset' => array(), 'type' => 'image/webp');

      foreach ($srcSets as $srcset) {
        foreach ($srcset as $src => $more) {
          if (in_array($more['type'], ['image/jpeg', 'image/png'])) {
            array_push($baseType['sizes'], '(min-width: ' . $more['media'] . 'px) ' . $more['media'] . 'px');
            array_push($baseType['srcset'], $src . ' ' . $more['media'] . 'w');
            $baseType['type'] = $more['type'];
          }
          if ($more['type'] === 'image/webp') {
            array_push($webpType['sizes'], '(min-width: ' . $more['media'] . 'px) ' . $more['media'] . 'px');
            array_push($webpType['srcset'], $src . ' ' . $more['media'] . 'w');
          }
        }
      }

      if (count($webpType['sizes'])) {
        $output .= '<source type="image/webp" sizes="' . implode(', ', $webpType['sizes']) . '" srcset="' . implode(', ', $webpType['srcset']) . '">';
      }

      $output .= '<source type="' . $baseType['type'] . '" sizes="' . implode(', ', $baseType['sizes']) . '" srcset="' . implode(', ', $baseType['srcset']) . '">';

      // Create the fallback img
      $image = preg_replace(
        '/src\s*=\s*".+?"/',
        'src="/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' . $originalImagePathInfo['filename'] .
        $this->sizeSplit . $validSize[0] . '.' . $originalImagePathInfo['extension'] . '"',
        $image);
      if (strpos($image, ' loading=') === false) {
        $image = str_replace('<img ', '<img loading="lazy" ', $image);
      }
      $output .= $image;
      $output .= '</picture>';

      return $output;
    } else {
      return $image;
    }
  }

  /**
   * Build the srcset string
   *
   * @param  array   $breakpoints  the different breakpoints
   * @param  string  $dirname      the folder name
   * @param  string  $filename     the file name
   * @param  string  $extension    the file extension
   * @param  string  $sizeSplitt   the string used for the notation
   *
   * @return array
   *
   * @since  1.0
   */
  private static function buildSrcset($breakpoints = array(200, 320, 480, 768, 992, 1200, 1600, 1920), $dirname, $filename, $extension, $sizeSplit) {
    $srcset = array();

    if (!empty($breakpoints)) {
      for ($i = 0, $l = count($breakpoints); $i < $l; $i++) {
        $fileSrc = 'media/cached-resp-images/' . $dirname . '/' . $filename . $sizeSplit . $breakpoints[$i];
        $type = in_array(mb_strtolower($extension), ['jpg', 'jpeg']) ? 'jpeg' : $extension;
        if (file_exists(JPATH_ROOT . '/' . $fileSrc . '.' . $extension)) {
          $srcset[$breakpoints[$i]][$fileSrc . '.' . $extension] = array(
            'media' => $breakpoints[$i],
            'type' => 'image/' . $type,
          );
        }
        if (file_exists(JPATH_ROOT . '/' . $fileSrc . '.webp')) {
          $srcset[$breakpoints[$i]][$fileSrc . '.webp'] = array(
            'media' => $breakpoints[$i],
            'type' => 'image/webp',
          );
        }
      }
    }

    return  $srcset;
  }

  /**
   * Create the thumbs
   *
   * @param array    $breakpoints  the different breakpoints
   * @param string   $dirname      the folder name
   * @param string   $filename     the file name
   * @param string   $extension    the file extension
   *
   * @return void
   *
   * @since  1.0
   */
  private function createImages($breakpoints = array(200, 320, 480, 768, 992, 1200, 1600, 1920), $dirname, $filename, $extension) {
    if (!count($breakpoints)) {
      return;
    }

    if (extension_loaded('gd')){
      $driver = 'gd';
    }

    if (extension_loaded('imagick')){
      $driver = 'imagick';
    }

    if (!$driver) {
      return;
    }

    // Create the images with width = breakpoint
    $manager = new ImageManager(array('driver' => $driver));

    // Getting the image info
    $info = @getimagesize(JPATH_ROOT . '/' . $dirname . '/' .$filename . '.' . $extension);

    if (empty($info)) {
      return;
    }

    $imageWidth = $info[0];
    $imageHeight = $info[1];

    // Skip if the width is less or equal to the required
    if ($imageWidth <= (int) $breakpoints[0]) {
      return;
    }

    // Check if we support the given image
    if (!in_array($info['mime'], array('image/jpeg', 'image/jpg', 'image/png'))) {
      return;
    }

    $channels = $info['channels'];

    if ($info['mime'] == 'image/png') {
      $channels = 4;
    }

    if (!isset($info['bits'])) {
      $info['bits'] = 16;
    }

    $imageBits = ($info['bits'] / 8) * $channels;

    // Do some memory checking
    if (!self::checkMemoryLimit(array('width' => $imageWidth, 'height' => $imageHeight, 'bits' => $imageBits), $dirname . '/' .$filename . '.' . $extension)) {
      return;
    }

    for ($i = 0, $l = count($breakpoints); $i < $l; $i++) {
      if ($this->scaleUp || ($imageWidth >= (int) $breakpoints[$i])) {
        // Load the image
        $image = $manager->make(JPATH_ROOT . '/' . $dirname . '/' .$filename . '.' . $extension);
        // Resize the image
        $image->resize($breakpoints[$i], null, function ($constraint) {
          $constraint->aspectRatio();
          $constraint->upsize();
        });

        // Save the image
        $image->save(
          JPATH_ROOT . '/media/cached-resp-images/' . $dirname . '/' . $filename .
          $this->sizeSplit . (int) $breakpoints[$i]. '.' . $extension,
          $this->quality,
          $extension);

        if ($driver === 'gd' && function_exists('imagewebp')) {
          // Save the image as webp
          $image->encode('webp', $this->quality);
          $image->save(
            JPATH_ROOT . '/media/cached-resp-images/' . $dirname . '/' . $filename .
            $this->sizeSplit . (int) $breakpoints[$i]. '.webp',
            $this->quality,
            'webp'
          );
        }

        if ($driver === 'imagick' && \Imagick::queryFormats('WEBP')) {
          // Save the image as webp
          $image->encode('webp', $this->quality);
          $image->save(
            JPATH_ROOT . '/media/cached-resp-images/' . $dirname . '/' . $filename .
            $this->sizeSplit . (int) $breakpoints[$i]. '.webp',
            $this->quality,
            'webp'
          );
        }

        $image->destroy();
      }
    }
  }

  /**
   * Check memory boundaries
   *
   * @param object  $properties   the Image properties object
   * @param string  $imagePath    the image path
   *
   * @return boolean
   *
   * @since  3.0.3
   *
   * @author  Niels Nuebel: https://github.com/nielsnuebel
   */
  protected static function checkMemoryLimit($properties, $imagePath) {
    $memorycheck = ($properties['width'] * $properties['height'] * $properties['bits']);
    $memorycheck_text = $memorycheck / (1024 * 1024);
    $memory_limit = ini_get('memory_limit');

    if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
      if ($matches[2] == 'M') {
        $memory_limit_value = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
      } else if ($matches[2] == 'K') {
        $memory_limit_value = $matches[1] * 1024; // nnnK -> nnn KB
      }
    }

    if (isset($memory_limit_value) && $memorycheck > $memory_limit_value) {
      $app = Factory::getApplication();
      $app->enqueueMessage(Text::sprintf('Image too big to be processed' ,$imagePath, $memorycheck_text, $memory_limit), 'error');

      return false;
    }

    return true;
  }
}
