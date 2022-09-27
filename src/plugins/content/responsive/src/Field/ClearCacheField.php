<?php
/**
 * @copyright   (C) 2022 Dimitrios Grammatikogiannis
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\Content\Responsive\Field;

defined('_JEXEC') || die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Layout\LayoutHelper;

class ClearCacheField extends FormField
{
  protected $type = 'clearcache';
  protected $layout = 'clearcache';

  public function setup(\SimpleXMLElement $element, $value, $group = null)
  {
    if ((string) $element->getName() !== 'field') return false;

    $this->input = null;
    $this->label = null;
    $this->element = $element;
    $this->group = $group;
    $this->hidden = ($this->hidden || strtolower((string) $this->element['type']) === 'hidden');
    $this->layout = !empty($this->element['layout']) ? (string) $this->element['layout'] : $this->layout;
    $this->parentclass = isset($this->element['parentclass']) ? (string) $this->element['parentclass'] : $this->parentclass;
    $this->hiddenLabel = true;
    $this->hidden = true;

    return true;
  }

  protected function getInput(): string
  {
    return rtrim(LayoutHelper::render($this->layout, [], JPATH_PLUGINS . '/content/responsive/layouts'), PHP_EOL);
  }
}
