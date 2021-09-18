<?php
/**
 * @package     ttc-freebies.plugin-responsive-images
 *
 * @copyright   Copyright (C) 2017 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Content responsive images plugin
 */
class PlgContentResponsive extends CMSPlugin {
  /**
   * System std Event Prepare
   *
   * @param string   $context  The context of the content being passed to the plugin.
   * @param object   &$row     The article object.  Note $article->text is also available
   * @param mixed    &$params  The article params
   * @param integer  $page     The 'page' number
   *
   * @return  mixed  Always returns void or true
   *
   * @throws Exception
   *
   * @since   4.0.0
   */
  public function onContentPrepare($context, &$row, &$params, $page) {
      $this->mainLogic($context, $row, true);
  }

  /**
   * System std Event After Save
   *
   * @param string   $context   The context of the content being passed to the plugin.
   * @param object   &$article  The article object.  Note $article->text is also available
   * @param mixed    &$params  The article params
   * @param integer  $page     The 'page' number
   *
   * @return  mixed  Always returns void or true
   *
   * @throws Exception
   *
   * @since   4.0.0
   */
  public function onContentAfterSave($context, &$article, &$params) {
    $this->mainLogic($context, $article, false);
  }

  /**
   * Creates all the image sizes on the fly on the save and optionally changes markup
   *
   * @param string   $context   The context of the content being passed to the plugin.
   * @param object   &$article  The article object.  Note $article->text is also available
   * @param mixed    &$params  The article params
   * @param integer  $page     The 'page' number
   *
   * @return  mixed  Always returns void or true
   *
   * @throws Exception
   *
   * @since   4.0.0
   */
  private function mainLogic($context, &$row, $replaceTags = false) {
    // Bail out if the helper isn't loaded
    if (!class_exists('\Ttc\Freebies\Responsive\Helper') && is_dir(JPATH_LIBRARIES . '/ttc')) {
      JLoader::registerNamespace('Ttc', JPATH_LIBRARIES . '/ttc');
      if (!class_exists('\Ttc\Freebies\Responsive\Helper')) {
        return;
      }
    }

    try {
      $pluginComponents = json_decode($this->params->get('components'));
    } catch (\Exception $e) {
      return;
    }

    if ($pluginComponents === false) {
      return;
    }

    foreach ($pluginComponents as $key => $component) {
      $views = preg_split('/[\s,]+/', $component->component_view);
      if ($views === '') {
        continue;
      }

      foreach ($views as $view) {
        if ($context !== $component->component_name . '.' . $view) {
          continue;
        }

        $columns = preg_split('/[\s,]+/', $component->component_db_column);
        if ($columns === '') {
          continue;
        }

        foreach ($columns as $currentNeedle) {
          // $currentNeedle = $component->component_db_columnn;
          $matches = [];
          if (!empty($row->{$currentNeedle})) {
            if (!preg_match_all('#<img\s[^>]+>#', $row->{$currentNeedle}, $matches)) {
              continue;
            }

            if (count($matches)) {
              foreach ($matches[0] as $img) {
                // Make sure we have a src
                if (strpos($img, ' src=') !== false && strpos($img, '//') === false) {
                  $processed = (new \Ttc\Freebies\Responsive\Helper)->transformImage($img, [200, 320, 480, 768, 992, 1200, 1600, 1920]);
                  if ($replaceTags && $processed !== $img) {
                      $row->{$currentNeedle} = str_replace($img, $processed, $row->{$currentNeedle});
                  }
                }
              }
            }
          }
        };
      };
    }
    return true;
  }
}
