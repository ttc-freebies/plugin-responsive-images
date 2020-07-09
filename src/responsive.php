<?php
/**
 * @package     ttc-freebies.plugin-responsive-images
 *
 * @copyright   Copyright (C) 2020 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content responsive images plugin
 */
class PlgContentResponsive extends \Joomla\CMS\Plugin\CMSPlugin {
  /**
   * Plugin that adds srcset to all content images, also creates all the image sizes on the fly
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
   * @since   1.0
   */
  public function onContentPrepare($context, &$row, &$params, $page) {
    // We care only for articles and category
    if ($context === 'com_content.article' || $context === 'com_content.category') {
      $canProceed = true;
    } else {
      $canProceed = false;
    }

    if (!$canProceed) {
      return;
    }

    $matches = array();
    if ($context === 'com_content.article' && !empty($row->text)) {
      if (strpos($row->text, '<img') === false) {
        return;
      }
      if (!preg_match_all('#<img\s[^>]+>#', $row->text, $matches)) {
        return;
      }
    } else if ($context === 'com_content.category' && !empty($row->introtext)) {
      if (strpos($row->introtext, '<img') === false) {
        return;
      }
      if (!preg_match_all('#<img\s[^>]+>#', $row->introtext, $matches)) {
        return;
      }
    }

    if (count($matches)) {
      JLoader::register('Ttc\Freebies\Responsive\Helper', __DIR__ . '/helper.php');

      foreach ($matches[0] as $img) {
        // Make sure we have a src
        if (strpos($img, ' src=') !== false && strpos($img, '//') === false) {
          $processed = (new Ttc\Freebies\Responsive\Helper)->transformImage($img, array(200, 320, 480, 768, 992, 1200, 1600, 1920));

          if ($img !== $processed) {
            $row->text = str_replace($img, $processed, $row->text);
          }
        }
      }
      return true;
    }
  }
}
