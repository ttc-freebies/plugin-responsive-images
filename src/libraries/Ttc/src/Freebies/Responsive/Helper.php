<?php

/**
 * @copyright   Copyright (C) 2021 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Freebies\Responsive;

defined('_JEXEC') || die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Ttc\Freebies\Responsive\Thumbs;

/**
 * Content responsive images plugin
 */
class Helper
{
  private $separator    = '_';
  private $qualityJPG   = 75;
  private $qualityWEBP  = 60;
  private $qualityAVIF  = 40;
  private $scaleUp      = false;
  private $validSizes   = [320, 768, 1200];
  private $validExt     = ['jpg', 'jpeg', 'png']; // 'webp', 'avif'

  public function __construct()
  {
    $this->enabled = PluginHelper::isEnabled('content', 'responsive');
    if ($this->enabled) {
      $plugin            = PluginHelper::getPlugin('content', 'responsive');
      $this->params      = new Registry($plugin->params);
      $this->enableWEBP  = (bool) $this->params->get('enableWEBP', 1);
      $this->enableAVIF  = (bool) $this->params->get('enableAVIF', 0);
      $this->qualityJPG  = (int) $this->params->get('qualityJPG', 75);
      $this->qualityWEBP = (int) $this->params->get('qualityWEBP', 60);
      $this->qualityAVIF = (int) $this->params->get('qualityAVIF', 40);
      $this->scaleUp     = (bool) $this->params->get('scaleUp', false);
      $this->separator   = $this->params->get('separator', '_');
      $preferedDriver    = $this->params->get('preferedDriver', 'gd');
      $excludeFolders    = preg_split('/[\s,]+/', $this->params->get('excludeFolders'));
      $sizes             = preg_split('/[\s,]+/', $this->params->get('sizes'));

      if (!is_array($sizes) || count($sizes) < 1) $sizes = [200, 320, 480, 768, 992, 1200, 1600, 1920];

      asort($sizes);
      $this->validSizes = $sizes;
      $this->excludeFolders = array_map(function ($folder) {
        return JPATH_ROOT . '/' . $folder;
      }, $excludeFolders);
    }
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
  public function transformImage($image, array $breakpoints, $jsonReturn = false): string
  {
    // Bail out early
    if (!is_array($breakpoints) || !$this->enabled || strpos($image, '<img') === false) return $image;

    // creating new document
    $docImage = new \DOMDocument('1.0', 'utf-8');

    //turning off some errors
    libxml_use_internal_errors(true);

    // it loads the content without adding enclosing html/body tags and also the doctype declaration
    $docImage->LoadHTML($image, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $src = $docImage->firstElementChild->getAttribute('src') ?? null;

    if ($src) {
      $paths = $this->getPaths($src);
      $docImage->firstElementChild->setAttribute('src', ltrim($paths->path, '/'));
    }

    $image = $docImage->saveHTML();

    // Valid root path and not excluded path
    if (empty($paths)
        || strpos($paths->pathReal, JPATH_ROOT) !== 0
        || strpos($paths->pathReal, JPATH_ROOT) === false
        || in_array(dirname($paths->pathReal), $this->excludeFolders)) return $image;

    $pathInfo = pathinfo($paths->path);

    // Bail out if no images supported
    if (!in_array(mb_strtolower($pathInfo['extension']), $this->validExt) || !file_exists(JPATH_ROOT . $paths->path)) return $image;

    if (!is_dir(JPATH_ROOT . '/media/cached-resp-images/' . str_replace('%20', ' ', $pathInfo['dirname']))) {
      if (
        !@mkdir(JPATH_ROOT . '/media/cached-resp-images/' . str_replace('%20', ' ', $pathInfo['dirname']), 0755, true)
        && !is_dir(JPATH_ROOT . '/media/cached-resp-images/' . str_replace('%20', ' ', $pathInfo['dirname']))
      ) return $image;
    }

    if (!$jsonReturn) return $this->buildSrcset(
      (object) [
        'dirname'   => str_replace('%20', ' ', $pathInfo['dirname']),
        'filename'  => str_replace('%20', ' ', $pathInfo['filename']),
        'extension' => $pathInfo['extension'],
        'tag'       => $image,
        'dom'       => $docImage,
      ],
      $breakpoints,
    );
    else $this->buildSrcsetJSON(
      (object) [
        'dirname'   => str_replace('%20', ' ', $pathInfo['dirname']),
        'filename'  => str_replace('%20', ' ', $pathInfo['filename']),
        'extension' => $pathInfo['extension'],
        'tag'       => $image,
      ],
      $breakpoints,
    );
  }

  /**
   * Build the srcset string
   *
   * @param  array   $breakpoints  the different breakpoints
   * @param  object   $image        the image attributes, expects dirname, filename, extension
   *
   * @return string
   *
   * @since  1.0
   */
  private function buildSrcset(object $image, array $breakpoints = [320, 768, 1200]): string
  {
    if (empty($breakpoints) || !is_file(JPATH_ROOT . '/' . $image->dirname . '/' . $image->filename . '.' . $image->extension)) return $image->tag;
    if (!is_file(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $image->dirname . '/' . $image->filename . '.json')) $this->createImages(str_replace('%20', ' ', $image->dirname), $image->filename, $image->extension);

    try {
      $srcSets = \json_decode(
        @file_get_contents(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $image->dirname . '/' . $image->filename . '.json')
      );
    } catch (\Exception $e) {
      return $image->tag;
    }

    if (null === $srcSets || $srcSets === false) return $image->tag;

    $type   = in_array(mb_strtolower($image->extension), ['jpg', 'jpeg']) ? 'jpeg' : mb_strtolower($image->extension);
    $output = '<picture class="responsive-image">';

    if (isset($srcSets->avif) && count(get_object_vars($srcSets->avif->srcset)) > 0) {
      $srcSetAvif = $this->getSrcSets($srcSets->avif->srcset, $breakpoints);
      if ($srcSetAvif !== '') $output .= '<source type="image/avif"  srcset="' . $srcSetAvif . '" sizes="' . implode(', ', array_reverse($srcSets->base->sizes)) . '">';
    }

    if (isset($srcSets->webp) && count(get_object_vars($srcSets->webp->srcset)) > 0) {
      $srcSetWebp = $this->getSrcSets($srcSets->webp->srcset, $breakpoints);
      if ($srcSetWebp !== '') $output .= '<source type="image/webp" srcset="' . $srcSetWebp . '" sizes="' . implode(', ', array_reverse($srcSets->base->sizes)) . '">';
    }

    $srcSetOrig = $this->getSrcSets($srcSets->base->srcset, $breakpoints);
    if ($srcSetOrig !== '') $output .= '<source type="image/' . $type . '" srcset="' . $srcSetOrig . '" sizes = "' . implode(', ', array_reverse($srcSets->base->sizes)) . '">';

    $image->dom->firstElementChild->setAttribute('width', $srcSets->base->width);
    $image->dom->firstElementChild->setAttribute('height', $srcSets->base->height);
    if (null !== $image->dom->firstElementChild->getAttribute('loading')) $image->dom->firstElementChild->setAttribute('loading', 'lazy');
    if (null !== $image->dom->firstElementChild->getAttribute('decoding')) $image->dom->firstElementChild->setAttribute('decoding', 'async');

    // Create the fallback img
    return  $output . $image->tag . '</picture>';
  }

  /**
   * Build the srcset string
   *
   * @param  array   $breakpoints  the different breakpoints
   * @param  object   $image        the image attributes, expects dirname, filename, extension
   *
   * @return string|object
   *
   * @since  1.0
   */
  private function buildSrcsetJSON(object $image, array $breakpoints = [320, 768, 1200])
  {
        if (empty($breakpoints) || !is_file(JPATH_ROOT . '/' . $image->dirname . '/' . $image->filename . '.' . $image->extension)) return $image->tag;
        if (!is_file(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $image->dirname . '/' . $image->filename . '.json')) $this->createImages(str_replace('%20', ' ', $image->dirname), $image->filename, $image->extension);

        try {
            $srcSets = \json_decode(@file_get_contents(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $image->dirname . '/' . $image->filename . '.json'));
        } catch (\Exception $e) {
            return $image->tag;
        }

        if (null === $srcSets || $srcSets === false) return $image->tag;

        $type   = in_array(mb_strtolower($image->extension), ['jpg', 'jpeg']) ? 'jpeg' : mb_strtolower($image->extension);
        $output = (object) [];

        if (null !== $srcSets->avif && count(get_object_vars($srcSets->avif->srcset)) > 0) {
            $srcSetAvif = $this->getSrcSets($srcSets->avif->srcset, $breakpoints);
            if ($srcSetAvif !== '') $output->avif = [ 'srcset' => $srcSetAvif, 'sizes' => implode(', ', array_reverse($srcSets->base->sizes))];
        }

        if (null !== $srcSets->webp && count(get_object_vars($srcSets->webp->srcset)) > 0) {
            $srcSetWebp = $this->getSrcSets($srcSets->webp->srcset, $breakpoints);
            if ($srcSetWebp !== '') $output->webp = ['srcset' => $srcSetWebp, 'sizes' => implode(', ', array_reverse($srcSets->base->sizes))];
        }

        $srcSetOrig = $this->getSrcSets($srcSets->base->srcset, $breakpoints);
        if ($srcSetOrig !== '') $output->{$type} = ['srcset' => $srcSetOrig, 'sizes' => implode(', ', array_reverse($srcSets->base->sizes))];

        $image->dom->firstElementChild->setAttribute('width', $srcSets->base->width);
        $image->dom->firstElementChild->setAttribute('height', $srcSets->base->height);
        if (null !== $image->dom->firstElementChild->getAttribute('loading')) $image->dom->firstElementChild->setAttribute('loading', 'lazy');
        if (null !== $image->dom->firstElementChild->getAttribute('decoding')) $image->dom->firstElementChild->setAttribute('decoding', 'async');

        // Create the fallback img
        $output->fallback = $image->tag;

        return  $output;
  }

  /**
   * Create the thumbs
   *
   * @param string   $dirname      the folder name
   * @param string   $filename     the file name
   * @param string   $extension    the file extension
   *
   * @return void
   *
   * @since  1.0
   */
  private function createImages($dirname, $filename, $extension)
  {
    // remove the first slash
    $dirname = ltrim($dirname, '/');

    // Getting the image info
    $info = @getimagesize(JPATH_ROOT . '/' . $dirname . '/' . $filename . '.' . $extension);
    $hash = hash_file('md5', JPATH_ROOT . '/' . $dirname . '/' . $filename . '.' . $extension);

    if (empty($info)) return;

    $imageWidth = $info[0];
    $imageHeight = $info[1];

    // Skip if the width is less or equal to the required
    if ($imageWidth <= (int) $this->validSizes[0]) return;

    // Check if we support the given image
    if (!in_array($info['mime'], ['image/jpeg', 'image/webp', 'image/png', 'image/avif'])) return;

    $sourceType = str_replace('image/', '', $info['mime']);
    $channels = isset($info['channels']) ? $info['channels'] : 4;

    if (!isset($info['bits'])) $info['bits'] = 16;

    $imageBits = ($info['bits'] / 8) * $channels;

    // Do some memory checking
    if (!self::checkMemoryLimit(['width' => $imageWidth, 'height' => $imageHeight, 'bits' => $imageBits], $dirname . '/' . $filename . '.' . $extension)) return;

    $srcSets = (object) [
      'base' => (object) [
        'srcset' => [],
        'sizes' => [],
        'width' => $info[0],
        'height' => $info[1],
        'version' => $hash,
      ]
    ];
    // (max-width: 300px) 100vw, (max-width: 600px) 50vw, (max-width: 900px) 33vw, 900px 320, 768, 1200
    // array_push($srcSets->base->sizes, '(max-width: 320px) 100vw, (max-width: 768px) 50vw, (max-width: 1200px) 33vw, 1200px');
    array_push($srcSets->base->sizes, '(min-width: 22.5em) 50vw, 100vw');
    $img = (object) [
      'dirname'   => $dirname,
      'filename'  => $filename,
      'extension' => $extension,
      'width'     => $imageWidth,
      'height'    => $imageHeight,
      'type'      => $sourceType,
    ];
    $options = (object) [
      'destination' => 'media/cached-resp-images/',
      'enableWEBP'  => $this->enableWEBP,
      'enableAVIF'  => $this->enableAVIF,
      'qualityJPG'  => $this->qualityJPG,
      'qualityWEBP' => $this->qualityWEBP,
      'qualityAVIF' => $this->qualityAVIF,
      'scaleUp'     => $this->scaleUp,
      'separator'   => trim($this->separator),
      'validSizes'  => $this->validSizes,
    ];

    try {
      $thumbs = new Thumbs('gd');
      $thumbs->create($img, $options, $srcSets);
    } catch (Exception $e) {

    }
  }

  /**
   * Check memory boundaries
   *
   * @param object  $properties   the Image properties object
   * @param string  $imagePath    the image path
   *
   * @return bool
   *
   * @since  3.0.3
   *
   * @author  Niels Nuebel: https://github.com/nielsnuebel
   */
  private static function checkMemoryLimit($properties, $imagePath): bool
  {
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
      Factory::getApplication()->enqueueMessage('Image too big to be processed', 'error'); //, $imagePath, $memorycheck_text, $memory_limit

      return false;
    }

    return true;
  }

  /**
   * Helper, returns the srcsets from the object for the given breakpoints
   *
   * @param object $obj
   * @param array  $breakpoints
   */
  private function getSrcSets($obj, $breakpoints): string
  {
    $retArr = [];
    foreach ($obj as $key => $val) {
      if (in_array($key, $breakpoints)) $retArr[] = $val;
    }

    if (count($retArr) > 0) return implode(', ', array_reverse($retArr));

    return null;
  }

  private function getPaths($path)
  {
    $path = MediaHelper::getCleanMediaFieldValue(str_replace(Uri::base(), '', $path));
    $path = (substr($path, 0, 1) === '/' ? $path : '/' . $path);

    return (object) [
      'path' => str_replace('%20', ' ', $path),
      'pathReal' => realpath(JPATH_ROOT . str_replace('%20', ' ', $path)),
    ];
  }
}
