<?php
/**
 * @package     ttc-freebies.plugin-responsive-images
 *
 * @copyright   Copyright (C) 2017 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Media\Action\Responsive\Extension;

defined('_JEXEC') || die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * Media Manager Responsive Action
 */
final class Responsive extends MediaActionPlugin implements SubscriberInterface
{
  private   $separator  = '_';
  private   $validSizes = [320, 768, 1200];
  private   $validExt   = ['jpg', 'jpeg', 'png']; // 'webp', 'avif'

  /**
   * Returns an array of CMS events this plugin will listen to and the respective handlers.
   *
   * @return  array
   */
  public static function getSubscribedEvents(): array
  {
    return [
      'onContentAfterDelete' => 'onAfterDelete',
      'onContentAfterSave'   => 'onAfterSave',
    ];
  }

  /**
   * The save event.
   *
   * @param   string   $context  The context
   * @param   object   $item     The item
   *
   * @return  void
   *
   * @since   4.0.0
   */
  public function onAfterSave($event)
  {
    if (
      $event[0] !== 'com_media.file'
      || !PluginHelper::isEnabled('content', 'responsive')
      || !class_exists('\Ttc\Freebies\Responsive\Helper')
      || !in_array($event[1]->extension, $this->validExt)
      || strpos($event[1]->adapter, 'local-') === false
      || !is_file(JPATH_ROOT . '/' . str_replace('local-', '', $event[1]->adapter) . $event[1]->path . $event[1]->name)
    ) return;

    $plugin          = PluginHelper::getPlugin('content', 'responsive');
    $this->params    = new Registry($plugin->params);
    $this->separator = $this->params->get('separator', '_');
    $sizes           = array_map('trim', array_filter(explode(',', $this->params->get('sizes')), 'trim'));

    if (!is_array($sizes)) $sizes = [320, 768, 1200];

    asort($sizes);
    $this->validSizes = $sizes;

    (new \Ttc\Freebies\Responsive\Helper)
      ->transformImage(
        '<img src="' . str_replace('local-', '', $event[1]->adapter) . $event[1]->path . $event[1]->name . '">',
        $this->validSizes
      );
  }

  /**
   * The save event.
   *
   * @param   string   $context  The context
   * @param   object   $item     The item
   *
   * @return  void
   */
  public function onAfterDelete($event): void
  {
    if (
      $event[0] !== 'com_media.file'
      || !PluginHelper::isEnabled('content', 'responsive')
      || strpos($event[1]->adapter, 'local-') === false
    ) return;

    $plugin          = PluginHelper::getPlugin('content', 'responsive');
    $this->params    = new Registry($plugin->params);
    $this->separator = $this->params->get('separator', '_');
    $sizes           = array_map('trim', array_filter(explode(',', $this->params->get('sizes')), 'trim'));

    if (!is_array($sizes)) $sizes = [320, 768, 1200];

    asort($sizes);
    $this->validSizes = $sizes;

    $originalImagePathInfo = pathinfo(str_replace('local-', '', $event[1]->adapter) . $event[1]->path . $event[1]->name);

    for ($i = 0, $l = count($this->validSizes); $i < $l; $i++) {
      $this->deleteFiles($originalImagePathInfo['dirname'], $originalImagePathInfo['filename'], $this->separator, $this->validSizes[$i], $originalImagePathInfo['extension']);
    }

    if (is_file(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $originalImagePathInfo['dirname'] . '/' .
      $originalImagePathInfo['filename'] . '.json')) {
      @unlink(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $originalImagePathInfo['dirname'] . '/' .
        $originalImagePathInfo['filename'] . '.json');
      $this->removeEmptyFolder(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $originalImagePathInfo['dirname']);
    }
  }

  /**
   * Check if a folder is empty and if so delete it
   *
   * @param   string   $path  The path
   *
   * @return  void
   */
  private function removeEmptyFolder($path): void
  {
    if (glob($path . "/*")) return;

    @rmdir($path);
  }

  /**
   * Check if a folder is empty and if so delete it
   *
   * @param   string   $dirname  The directory
   * @param   string   $filename  The flename
   * @param   string   $separator  The separator string
   * @param   int      $size  The size
   * @param   string   $_ext  The extension
   *
   * @return  void
   */
  private function deleteFiles($dirname, $filename, $separator, $size, $_ext)
  {
    $exts = ['avif', 'webp', $_ext];
    foreach ($exts as $ext) {
      if (is_file(JPATH_ROOT . '/media/cached-resp-images/' . $dirname . '/' . $filename . $separator . $size . '.' . $ext)) {
        @unlink(JPATH_ROOT . '/media/cached-resp-images/' . $dirname . '/' . $filename . $separator . $size . '.' . $ext);
      }
    }
  }
}
