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

        $subPath = array_merge($path, [$field]);

        if ($this->fields[$field] === TRUE) {
          $this->data[] = [
            'key' => $subPath,
            'text' => render($element[$field]),
          ];
        } else {
          foreach (Element::children($element[$field], TRUE) as $delta) {
            $this->processRecursive($element[$field][$delta], $subPath);
          }
        }

      }
    }

    return $element;
  }
}