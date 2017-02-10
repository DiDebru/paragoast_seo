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
            $this->processRecursive($element[$field][$delta], array_merge($subpath, [$delta]));
          }
        }

      }
    }

    return $element;
  }

  public function getFormattedKey($subpath) {
    $str = '';
    $count = 0;
    // edit-field-extension-0-subform-field-report-0-value
    while ($count <= count($subpath)-1) {
      if ($count == 0 && !is_numeric($subpath[$count])) {
        $str .= 'edit-' . str_replace('_', '-', $subpath[$count]) . '-' . (is_numeric($subpath[$count+1]) && array_key_exists($count+1 , $subpath) ? $subpath[$count+1] : 0);
      }
      if ($count != 0 && $count != count($subpath)-1 && !is_numeric($subpath[$count])) {
        $str .= '-subform-' . str_replace('_', '-', $subpath[$count]) . '-' . $subpath[$count-1];
      }
      if ($count == count($subpath)-1) {
        $str .= '-subform-' . str_replace('_', '-', $subpath[$count]) . '-' . $subpath[$count-1] . '-value';
      }
      $count++;
    }
    return $str;
  }

}
