<?php
/**
 * @package     ttc-freebies.plugin-responsive-images
 *
 * @copyright   Copyright (C) 2020 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Freebies\Responsive;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Image\Image;
use Joomla\Registry\Registry;

/**
 * Content responsive images plugin
 */
class Helper {
  private $baseDir     = '';
  private $quality     = '';
  private $scaleUp     = '';
  private $scaleMethod = '';

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
    if ($this->baseDir === '') {
      $this->baseDir = JPATH_ROOT . '/' . Factory::getApplication('site')->getParams('com_media')->get('file_path', 'images');
    }

    $plugin = PluginHelper::getPlugin('content', 'responsive');
    $this->params = new Registry($plugin->params);

    $validExt          = array('jpg', 'jpeg', 'png');
    $sizeSplit         = '_';
    $validSize         = array(200, 320, 480, 768, 992, 1200, 1600, 1920);
    $this->quality     = $this->quality === '' ? (int) $this->params->get('quality', '85') : $this->quality;
    $this->scaleUp     = $this->scaleUp === '' ? (bool) ($this->params->get('scaleUp', '0') == '1') : $this->scaleUp;
    $this->scaleMethod = $this->scaleMethod === '' ? $this->params->get('scaleMethod', 'inside') : $this->scaleUp;

    if (is_array($breakpoints)) {
      $validSize = $breakpoints;
    }

    switch ($this->scaleMethod) {
      case 'SCALE_FILL':
        $scaleMethod = Image::SCALE_FILL;
        break;
      case 'SCALE_OUTSIDE':
        $scaleMethod = Image::SCALE_OUTSIDE;
        break;
      case 'SCALE_FIT':
        $scaleMethod = Image::SCALE_FIT;
        break;
      case 'SCALE_INSIDE':
      default:
        $scaleMethod = Image::SCALE_INSIDE;
        break;
    }

    // Get the original path
    preg_match('/src\s*=\s*"(.+?)"/', $image, $match);
    $originalImagePath = $match[1];
    $originalImagePath = str_replace(\Joomla\CMS\Uri\Uri::base(), '', $originalImagePath);
    $path = realpath(JPATH_ROOT . (substr($originalImagePath, 0, 1) === '/' ? $originalImagePath : '/'. $originalImagePath));

    if (strpos($path, $this->baseDir) !== 0 || strpos($path, $this->baseDir) === false) {
      return '';
    }

    $originalImagePathInfo = pathinfo($originalImagePath);

    // Bail out if no images supported
    if (!in_array(mb_strtolower($originalImagePathInfo['extension']), $validExt) || !file_exists(JPATH_ROOT . '/' . $originalImagePath)) {
      return '';
    }

    if (!is_dir(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'])) {
      if (!@mkdir(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'], 0755, true) && !is_dir(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname']) ) {
        throw new RuntimeException('There was a file permissions problem in folder \'media\'');
      }
    }

    // If responsive image doesn't exist we will create it
    if (!file_exists(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .$originalImagePathInfo['filename'] . $sizeSplit . $validSize[0] . '.' . $originalImagePathInfo['extension'])) {
      self::createImages($validSize, $originalImagePathInfo['dirname'], $originalImagePathInfo['filename'], $originalImagePathInfo['extension'], $this->quality, $this->scaleUp, $scaleMethod, $sizeSplit);
    }

    // If responsive image exists use it
    if (file_exists(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .$originalImagePathInfo['filename'] . $sizeSplit . $validSize[0] . '.' . $originalImagePathInfo['extension'])) {
      $srcSets = self::buildSrcset($validSize, $originalImagePathInfo['dirname'], $originalImagePathInfo['filename'], $originalImagePathInfo['extension'], $sizeSplit);

      if (empty($srcSets)) {
        return '';
      }

      $srcSets = array_reverse($srcSets);
      $output = '<picture class="responsive-image">';

      foreach ($srcSets as $srcset) {
        foreach ($srcset as $src => $more) {
          $type = $more['type'] === 'jpeg' ? 'jpg' : $more['type'];
          $output .= '<source type="' . $type . '" media="' . $more['media'] . '" srcset="' . $src . '">';
        }
      }

      // Create the fallback img
      $image = preg_replace('/src\s*=\s*".+?"/', 'src="/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' . $originalImagePathInfo['filename'] . $sizeSplit . $validSize[0] . '.' . $originalImagePathInfo['extension'] . '"', $image);
      if (strpos($image, ' loading=') === false) {
        $image = str_replace('<img ', '<img loading="lazy" ', $image);
      }
      $output .= $image;
      $output .= '</picture>';

      return $output;
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
  private static function buildSrcset($breakpoints = array(200, 320, 480, 768, 992, 1200, 1600, 1920), $dirname, $filename, $extension, $sizeSplitt) {
    $srcset = [];

    if (!empty($breakpoints)) {
      for ($i = 0, $l = count($breakpoints); $i < $l; $i++) {
        $fileSrc = 'media/cached-resp-images/' . $dirname . '/' . $filename . $sizeSplitt . $breakpoints[$i];
        $type = in_array($extension, ['jpg', 'jpeg']) ? 'jpeg' : $extension;
        if (file_exists(JPATH_ROOT . '/' . $fileSrc . '.' . $extension)) {
          $srcset[$breakpoints[$i]][$fileSrc . '.' . $extension] = array(
            'media' => '(min-width: ' . $breakpoints[$i]. 'px)',
            'type' => 'image/' . $type,
          );
        }
        if (file_exists(JPATH_ROOT . '/' . $fileSrc . '.webp')) {
          $srcset[$breakpoints[$i]][$fileSrc . '.webp'] = array(
            'media' => '(max-width: ' . $breakpoints[$i]. 'px)',
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
   * @param integer  $quality      the quality of the generated image
   * @param boolean  $scaleUp      switch for upscalling or not an image
   * @param string   $scaleMethod  the method for the scale up
   * @param string   $sizeSplitt   the string used for the notation
   *
   * @return void
   *
   * @since  1.0
   */
  private static function createImages($breakpoints = array(200, 320, 480, 768, 992, 1200, 1600, 1920), $dirname, $filename, $extension, $quality, $scaleUp,  $scaleMethod, $sizeSplitt) {
    if (!empty($breakpoints)) {
      // Create the images with width = breakpoint
      $image = new Image;

      // Load the file
      $image->loadFile(JPATH_ROOT . '/' . $dirname . '/' .$filename . '.' . $extension);

      // Get the properties
      $properties = $image->getImageFileProperties(JPATH_ROOT . '/' . $dirname . '/' . $filename . '.' . $extension);

      // Skip if the width is less or equal to the required
      if ($properties->width <= (int) $breakpoints[0]) {
        return;
      }

      // Do some memory checking
      if (!self::checkMemoryLimit($properties, $dirname . '/' .$filename . '.' . $extension)) {
        return;
      }

      // Get the image type
      $type = str_replace('image/','', mb_strtolower($properties->mime));

      switch ($type) {
        case 'jpeg':
        case 'jpg':
          $imageType = 'IMAGETYPE_JPEG';
          break;
        case 'png':
          $imageType = 'IMAGETYPE_PNG';
          break;
        default:
          $imageType = '';
          break;
      }

      if (!in_array($imageType, array('IMAGETYPE_JPEG', 'IMAGETYPE_PNG'))) {
        return;
      }

      $aspectRatio = $properties->width / $properties->height;

      for ($i = 0, $l = count($breakpoints); $i < $l; $i++) {
        if ($scaleUp or ($properties->width >= (int) $breakpoints[$i])) {
          // Resize the image
          $newImg = $image->resize((int) $breakpoints[$i], (int) $breakpoints[$i] / $aspectRatio, true, $scaleMethod);

          $newImg->toFile(
            JPATH_ROOT . '/media/cached-resp-images/' . $dirname . '/' . $filename . $sizeSplitt . (int) $breakpoints[$i] . '.' . $extension,
            $imageType,
            array('quality' => (int) $quality)
          );

          if (function_exists('imagewebp')) {
            if (function_exists('imagecreatefromjpeg') && $imageType === 'IMAGETYPE_JPEG') {
              $webp_resource = @imagecreatefromjpeg(JPATH_ROOT . '/media/cached-resp-images/' . $dirname . '/' . $filename . $sizeSplitt . (int) $breakpoints[$i] . '.' . $extension);
            } elseif (function_exists('imagecreatefrompng') && $imageType === 'IMAGETYPE_PNG') {
              $webp_resource = @imagecreatefrompng(JPATH_ROOT . '/media/cached-resp-images/' . $dirname . '/' . $filename . $sizeSplitt . (int) $breakpoints[$i] . '.' . $extension);
            }
            if ($webp_resource) {
              @imagewebp($webp_resource, JPATH_ROOT . '/media/cached-resp-images/' . $dirname . '/' . $filename . $sizeSplitt . (int) $breakpoints[$i] . '.' . 'webp', (int) $quality);
            }
          }
        }
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
    $memorycheck = ($properties->width * $properties->height * $properties->bits);
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
      $app =JFactory::getApplication();
      $app->enqueueMessage(JText::sprintf('Image too big to be processed' ,$imagePath, $memorycheck_text, $memory_limit), 'error');

      return false;
    }

    return true;
  }
}
