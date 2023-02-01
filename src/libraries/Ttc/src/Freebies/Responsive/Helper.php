<?php

/**
 * @copyright   Copyright (C) 2021 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ttc\Freebies\Responsive;

defined('_JEXEC') || die();

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
  private $params;
  private $enabled        = false;
  private $enableWEBP     = false;
  private $enableAVIF     = false;
  private $excludeFolders = [];
  private $separator      = '_';
  private $qualityJPG     = 75;
  private $qualityWEBP    = 60;
  private $qualityAVIF    = 40;
  private $scaleUp        = false;
  private $driver         = 'gd';
  private $validSizes     = [320, 768, 1200];
  private $validExt       = ['jpg', 'jpeg', 'png']; // 'webp', 'avif'

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
      $this->driver      = $this->params->get('preferedDriver', 'gd');
      $excludeFolders    = preg_split('/[\s,]+/', $this->params->get('excludeFolders', ''));
      $sizes             = preg_split('/[\s,]+/', $this->params->get('sizes', ''));

      if (!is_array($sizes) || count($sizes) < 1) $sizes = [320, 768, 1200];

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
  public function transformImage($image, array $breakpoints): string
  {
    // Bail out early
    if (!is_array($breakpoints) || !$this->enabled || strpos($image, '<img') === false) return $image;

    // creating new document
    $docImage = new \DOMDocument('1.0', 'utf-8');

    // turning off some errors
    libxml_use_internal_errors(true);

    // it loads the content without adding enclosing html/body tags and also the doctype declaration
    $docImage->LoadHTML(htmlspecialchars_decode(iconv('UTF-8', 'ISO-8859-1', htmlentities($image, ENT_COMPAT, 'UTF-8')), ENT_QUOTES), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    if (!isset($docImage->firstElementChild)) {
      $imageEl = $docImage->childNodes->item(0);
    } else {
      $imageEl = $docImage->firstElementChild;
    }

    $src = $imageEl->getAttribute('src') ?? null;

    if ($imageEl && $src) {
      $paths = $this->getPaths($src);
      $imageEl->setAttribute('src', ltrim($paths->path, '/'));
    }

    $image = $docImage->saveHTML();

    // Valid root path and not excluded path
    if (
      empty($paths)
      || strpos($paths->pathReal, JPATH_ROOT) !== 0
      || strpos($paths->pathReal, JPATH_ROOT) === false
      || $this->isExcludedFolder(dirname($paths->pathReal))
    ) return $image;

    $pathInfo = pathinfo($paths->path);

    // Bail out if no images supported
    if (!in_array(mb_strtolower($pathInfo['extension']), $this->validExt) || !file_exists(JPATH_ROOT . $paths->path)) return $image;

    if (!is_dir(JPATH_ROOT . '/media/cached-resp-images/' . str_replace('%20', ' ', $pathInfo['dirname']))) {
      if (
        !@mkdir(JPATH_ROOT . '/media/cached-resp-images/' . str_replace('%20', ' ', $pathInfo['dirname']), 0755, true)
        && !is_dir(JPATH_ROOT . '/media/cached-resp-images/' . str_replace('%20', ' ', $pathInfo['dirname']))
      ) return $image;
    }

    return $this->buildSrcset(
      (object) [
        'dirname'   => str_replace('%20', ' ', $pathInfo['dirname']),
        'filename'  => str_replace('%20', ' ', $pathInfo['filename']),
        'extension' => $pathInfo['extension'],
        'tag'       => $image,
        'dom'       => $docImage,
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
    $sizesAttr = isset($srcSets->base->sizes) && count($srcSets->base->sizes) ? ' sizes="' . implode(', ', array_reverse($srcSets->base->sizes)) . '"' : '';

    if (isset($srcSets->avif) && count(get_object_vars($srcSets->avif->srcset)) > 0) {
      $srcSetAvif = $this->getSrcSets($srcSets->avif->srcset, $breakpoints);
      if ($srcSetAvif !== '') $output .= '<source type="image/avif"  srcset="' . $srcSetAvif . '" ' . $sizesAttr . '>';
    }

    if (isset($srcSets->webp) && count(get_object_vars($srcSets->webp->srcset)) > 0) {
      $srcSetWebp = $this->getSrcSets($srcSets->webp->srcset, $breakpoints);
      if ($srcSetWebp !== '') $output .= '<source type="image/webp" srcset="' . $srcSetWebp . '"' . $sizesAttr . '>';
    }

    $srcSetOrig = $this->getSrcSets($srcSets->base->srcset, $breakpoints);
    if ($srcSetOrig !== '') $output .= '<source type="image/' . $type . '" srcset="' . $srcSetOrig . '"' . $sizesAttr . '>';

    if (!isset($image->dom->childNodes)) {
      return $image->tag;
    }
    if (!isset($image->dom->firstElementChild)) {
      $imageEl = $image->dom->childNodes->item(0);
    } else {
      $imageEl = $image->dom->firstElementChild;
    }

    $imageEl->setAttribute('width', $srcSets->base->width);
    $imageEl->setAttribute('height', $srcSets->base->height);
    if (null !== $imageEl->getAttribute('loading')) $imageEl->setAttribute('loading', 'lazy');
    if (null !== $imageEl->getAttribute('decoding')) $imageEl->setAttribute('decoding', 'async');

    // Create the fallback img
    return  $output . $image->tag . '</picture>';
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
    array_push($srcSets->base->sizes);
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
      $thumbs = new Thumbs($this->driver);
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
    // $memorycheck_text = $memorycheck / (1024 * 1024);
    $memory_limit = self::toByteSize(ini_get('memory_limit'));

    if (isset($memory_limit) && $memorycheck > $memory_limit) {
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

    return '';
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

  private function isExcludedFolder($path)
  {
    $isExcluded = false;

    foreach ($this->excludeFolders as $folder) {
      if (substr($path, 0, strlen($folder)) === $folder) {
        $isExcluded = true;
        break;
      }
    }

    return $isExcluded;
  }

  private static function toByteSize($formated)
  {
    $formated = strtolower(trim($formated));
    $unit = substr($formated, -1, 1);
    $formated = substr($formated, 0, -1);

    if ($unit == 'g') $formated *= 1024 * 1024 * 1024;
    else if ($unit == 'm') $formated *= 1024 * 1024;
    else if ($unit == 'k') $formated *= 1024;
    else return false;
    return $formated;
  }
}
