<?php

/**
 * @package     ttc-freebies.plugin-responsive-images
 *
 * @copyright   Copyright (C) 2017 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Responsive\Extension;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Event\SubscriberInterface;
use stdClass;

/**
 * Content responsive images plugin
 */
final class Responsive extends CMSPlugin implements SubscriberInterface
{
  /**
   * Returns an array of CMS events this plugin will listen to and the respective handlers.
   *
   * @return  array
   */
  public static function getSubscribedEvents(): array
  {
    return [
      'onContentPrepare'   => 'onPrepare',
      'onContentAfterSave' => 'onAfterSave',
      'onAjaxResponsive'   => 'responsiveAjax',
    ];
  }

  /**
   * System std Event Prepare
   *
   * @param  array  $event
   *
   * @return  void|true
   *
   * @throws Exception
   */
  public function onPrepare($event)
  {
    $this->mainLogic($event[0], $event[1], true);
  }

  /**
   * System std Event After Save
   *
   * @param  array  $event
   *
   * @return  void|true
   *
   * @throws Exception
   */
  public function onAfterSave($event)
  {
    $this->mainLogic($event[0], $event[1], false);
  }

  /**
   * System std Event Prepare
   *
   * @param  array  $event
   *
   * @return  void|true
   *
   * @throws Exception
   */
  public function responsiveAjax($event)
  {
    if (!Session::checkToken('request') || !(Factory::getUser())->authorise('core.edit', 'com_plugins')) throw new \Exception('Not Allowed');
    if (is_dir(JPATH_ROOT . '/media/cached-resp-images')) return Folder::delete(JPATH_ROOT . '/media/cached-resp-images');

    return true;
  }

  /**
   * Creates all the image sizes on the fly on the save and optionally changes markup
   *
   * @param string   $context      The context of the content being passed to the plugin.
   * @param object   &$row         The article object.  Note $article->text is also available
   * @param bool     $replaceTags  Replace or not the image tags
   *
   * @return  void|true
   *
   * @throws Exception
   */
  private function mainLogic($context, &$row, $replaceTags = false)
  {
    // Bail out if the library isn't loaded
    if (!class_exists('\Ttc\Freebies\Responsive\Helper')) return;

    $pluginComponents = $this->getExtensions();

    if (count((array) $pluginComponents) === 0) return;

    $activeContexts = $this->getActiveContext($pluginComponents, $context);

    foreach ($activeContexts as $ext) {
      if (empty($row->{$ext})) continue;
      if (!preg_match_all('#<img\s[^>]+>#', $row->{$ext}, $matches)) continue;

      foreach ($matches[0] as $img) {
        // Make sure we have a src
        if (strpos($img, ' src=') === false || strpos($img, '//') !== false) continue;
        $sizes = array_map('trim', array_filter(explode(',', $this->params->get('sizes', '320,768,1200')), 'trim'));
        $processed = (new \Ttc\Freebies\Responsive\Helper)->transformImage($img, $sizes);
        if ($replaceTags && $processed !== $img) {
          $row->{$ext} = str_replace($img, $processed, $row->{$ext});
        }
      }
    }

    return true;
  }

  /**
   * Get the plugin enabled extensions
   */
  private function getExtensions(): stdClass
  {
    $pluginComponents = $this->params->get('components', (object) []);

    if (!is_object($pluginComponents)) {
      try {
        $pluginComponents = \json_decode($this->params->get('components', ''));
      } catch (\Exception $e) {
        return true;
      }

      if ($pluginComponents === false) return (object) [];
    }

    return $pluginComponents;
  }

  /**
   * Get the array of object parts that could contain img tags
   */
  private function getActiveContext($pluginComponents, $context)
  {
    $return = [];
    foreach ($pluginComponents as $component) {
      $contextExt = explode('.', $context);
      if ($contextExt[0] !== $component->component_name) continue;
      $views = array_map('trim', array_filter(explode(',', $component->component_view), 'trim'));
      if (count($views) === 0) continue;

      foreach ($views as $view) {
        if ($context !== $component->component_name . '.' . $view) continue;

        $columns = array_map('trim', array_filter(explode(',', $component->component_db_column), 'trim'));
        if (count($columns) === 0) continue;

        foreach ($columns as $currentNeedle) {
          $return[] = $currentNeedle;
        }
      }
    }
    return $return;
  }
}
