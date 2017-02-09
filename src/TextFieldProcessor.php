<?php
namespace Drupal\yoast_seo;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Render\Element;

class TextFieldProcessor
{
  protected $entity;
  protected $fields;
  protected $data;
  protected $entity_type_manager;

  public function __construct(EntityTypeManager $entity_type_manager)
  {
    $this->entity_type_manager = $entity_type_manager;
    $this->data = [];
  }

  public function process(EntityInterface $entity, $fields)
  {
    $this->entity = $entity;
    $this->fields = $fields;
    $view = $this->entity_type_manager->getViewBuilder($this->entity->getEntityTypeId())->view($this->entity);
    $this->processRecursive($view, []);

    return $this->data;
  }

  protected function processRecursive($element, $path)
  {
    unset($element['#cache']);
    $element['#pre_render'][] = [$this, 'preRenderCallback'];
    $element['#tfp_path'] = $path;

    $result = render($element);
    $x = 42;
  }

  public function preRenderCallback($element)
  {
    $path = $element['#tfp_path'];
    $element['#printed'] = TRUE;
    if (!array_key_exists('delta', $path)) {
      $path['delta'] = 0;
    }
    foreach (Element::children($element, TRUE) as $field) {
      if (isset($this->fields[$field])) {
        $subpath = array_merge($path, [$field]);
        $key = $this->getFormattedKey($subpath);
        if ($this->fields[$field] === TRUE) {
          $this->data[] = [
            $key => strip_tags(render($element[$field]), '<p><a><img><h1><h2><h3><h4>'),
          ];
        } else {
          foreach (Element::children($element[$field], TRUE) as $delta) {
            $subpath['delta'] = $delta;
            $this->moveToTop($subpath, 'delta');
            $this->processRecursive($element[$field][$delta], $subpath);
          }
        }

      }
    }

    return $element;
  }

  public function getFormattedKey($subpath) {
    $str = '';
    $count = 0;
    $counter = count($subpath)-2;
    // edit-field-extension-0-subform-field-report-0-value
    while ($count <= count($subpath)-2) {
      if ($count == 0) {
        $str .= 'edit-' . str_replace('_', '-', $subpath[$count]) . '-' . $subpath['delta'];
      }
      if ($count != 0 && $count != count($subpath)-2) {
        $str .= '-subform-' . str_replace('_', '-', $subpath[$count]) . '-' . $subpath['delta'];
      }
      if ($count == count($subpath)-2) {
        $str .= '-subform-' . str_replace('_', '-', $subpath[$count]) . '-' . $subpath['delta'] . '-value';
      }
      $count++;
    }
    return $str;
  }

  private function moveToTop(&$array, $key) {
    $temp = array($key => $array[$key]);
    unset($array[$key]);
    $array = $temp + $array;
  }

}
