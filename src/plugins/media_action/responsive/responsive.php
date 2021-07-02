<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Media Manager Responsive Action
 */
class PlgMediaActionResponsive extends \Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin
{
  private   $separator  = '_';
  private   $validSizes = [200, 320, 480, 768, 992, 1200, 1600, 1920];
  private   $validExt   = ['jpg', 'jpeg', 'png']; // 'webp', 'avif'
  protected $enabled    = false;

  public function __construct()
  {
    // Bail out if the helper isn't loaded
    if (\Joomla\CMS\Plugin\PluginHelper::isEnabled('content', 'responsive') && !class_exists('\Ttc\Freebies\Responsive\Helper') && is_dir(JPATH_LIBRARIES . '/ttc')) {
      JLoader::registerNamespace('Ttc', JPATH_LIBRARIES . '/ttc');
      if (!class_exists('\Ttc\Freebies\Responsive\Helper')) {
        return;
      }

      $plugin          = PluginHelper::getPlugin('content', 'responsive');
      $this->params    = new Registry($plugin->params);
      $this->enabled   = true;
      $this->separator = $this->params->get('separator', '_');
      $sizes           = explode(',', $this->params->get('sizes'));

      if (!is_array($sizes)) {
        $sizes = [200, 320, 480, 768, 992, 1200, 1600, 1920];
      }

      asort($sizes);
      $this->validSizes = $sizes;
    }
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
  public function onContentAfterSave($context, $item)
  {
    if ($context !== 'com_media.file' || !in_array($item->extension, $this->validExt) || strpos($item->adapter, 'local-') === false || !$this->enabled) {
      return;
    }

    if (is_file(JPATH_ROOT . '/' . str_replace('local-', '', $item->adapter) . $item->path . '/' . $item->name)) {
      (new \Ttc\Freebies\Responsive\Helper)
        ->transformImage(
          '<img src="' . str_replace('local-', '', $item->adapter) . $item->path . '/' . $item->name . '">',
          $this->validSizes
        );
    }
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
  public function onContentAfterDelete($context, $item)
  {
    if ($context !== 'com_media.file' || strpos($item->adapter, 'local-') === false || !$this->enabled) {
      return;
    }

    $originalImagePathInfo = pathinfo(str_replace('local-', '', $item->adapter) . $item->path . '/' . $item->name);
    for ($i = 0, $l = count($this->validSizes); $i < $l; $i++) {
      if (is_file(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .
        $originalImagePathInfo['filename'] . $this->separator . $this->validSizes[$i] . '.avif')) {
        @unlink(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .
          $originalImagePathInfo['filename'] . $this->separator . $this->validSizes[$i] . '.avif');
      }
      if (is_file(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .
        $originalImagePathInfo['filename'] . $this->separator . $this->validSizes[$i] . '.webp')) {
        @unlink(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .
          $originalImagePathInfo['filename'] . $this->separator . $this->validSizes[$i] . '.webp');
      }
      if (is_file(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .
        $originalImagePathInfo['filename'] . $this->separator . $this->validSizes[$i] . '.' . $originalImagePathInfo['extension'])) {
        @unlink(JPATH_ROOT . '/media/cached-resp-images/' . $originalImagePathInfo['dirname'] . '/' .
          $originalImagePathInfo['filename'] . $this->separator . $this->validSizes[$i] . '.' . $originalImagePathInfo['extension']);
      }
    }
    if (is_file(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $originalImagePathInfo['dirname'] . '/' .
      $originalImagePathInfo['filename'] . '.json')) {
      @unlink(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $originalImagePathInfo['dirname'] . '/' .
        $originalImagePathInfo['filename'] . '.json');
      if ($this->isFolderEmpty(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $originalImagePathInfo['dirname'])) {
        rmdir(JPATH_ROOT . '/media/cached-resp-images/___data___/' . $originalImagePathInfo['dirname']);
      }
    }
  }

  /**
   * Check if a folder is empty
   *
   * @param   string   $path  The path
   *
   * @return  bool
   *
   * @since   4.0.0
   */
  private function isFolderEmpty($path)
  {
    if (glob($path . "/*")) {
      return false;
    }
    return true;
  }
}
