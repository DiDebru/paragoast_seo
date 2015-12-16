/**
 * @file
 * Drupal Yoast SEO form utility.
 *
 * This library will help developers to interacts with drupal form
 * on client side
 *
 * @ignore
 */

(function ($, Drupal) {

  'use strict';

  /**
   * @namespace
   */
  var DrupalForm = {

    /**
     * Based on a form item HTMLElement wrapper, get the FormItem view class
     * to use to control the form item HTMLElement field.
     * @param el
     * @returns {function}
     */
    getFormItemClass: function (el_wrapper) {
      var field_item_class = DrupalForm.views.Textfield;
      var field_types_map = {
        'js-form-type-textfield': 'Textfield',
        'js-form-type-textarea': 'Textarea'
      };

      // If the element carries a CKEDITOR.
      var $textarea = $('textarea', $(el_wrapper));
      if ($textarea.length && CKEDITOR.dom.element.get($textarea[0]).getEditor()) {
        field_item_class = DrupalForm.views.Ckeditor;
      }
      else {
        // Else define the FormItem class regarding the element wrapper classes.
        for (var field_type in field_types_map) {
          if ($(el_wrapper).hasClass(field_type)) {
            field_item_class = DrupalForm.views[field_types_map[field_type]];
          }
        }
      }

      return field_item_class;
    },

    /**
     * Factory to instantiate or retrieve a Form Item view based on a HTMLElement
     * wrapper.
     * @param el_wrapper
     * @returns {Backbone.View}
     */
    getFormItemView: function (el_wrapper) {
      // Get the FormItem view class based on the HTMLElement wrapper.
      var FormItemViewClass = DrupalForm.getFormItemClass(el_wrapper),
      // The HTMLElement to bind the FieldItem view onto.
        el = null;

      // Based on the FormItem view tag, retrieve the HTMLElement to bind the View onto.
      el = $(FormItemViewClass.tag, el_wrapper);

      // Instantiate the FormItem view.
      return new FormItemViewClass({
        el: el
      });
    }
  };

  /**
   * @namespace
   */
  DrupalForm.views = {};

  /**
   * Abstract class (kind of) which as for aim to control Drupal Form Item field.
   * @type {DrupalForm.views.FormItem}
   */
  DrupalForm.views.FormItem = Backbone.View.extend({
    /**
     * {@inheritdoc}
     */
    events: {
      'input': 'onInput'
    },
    /**
     * {@inheritdoc}
     */
    initialize: function () {
      // Initialize your component.
    },
    /**
     * Listen to the input event.
     * @param evt
     */
    onInput: function (evt) {
      this.change($(this.el).val());
    },
    /**
     *
     * @param val
     */
    change: function (val) {
      console.log(val);
    }
  }, {
    /**
     * The tag of the HTMLElement that carries the form item field.
     */
    tag: 'input'
  });

  /**
   * FormItem view that has for aim to control textfield form item.
   * @type {DrupalForm.views.Textfield}
   */
  DrupalForm.views.Textfield = DrupalForm.views.FormItem.extend({}, {
    tag: 'input'
  });

  /**
   * FormItem view that has for aim to control textfield form item.
   * @type {DrupalForm.views.Textarea}
   */
  DrupalForm.views.Textarea = DrupalForm.views.FormItem.extend({}, {
    tag: 'textarea'
  });

  /**
   * FormItem view that has for aim to control textarea ckeditor form item.
   * @type {DrupalForm.views.Ckeditor}
   */
  DrupalForm.views.Ckeditor = DrupalForm.views.Textarea.extend({
    /**
     * {@inheritdoc}
     */
    events: {},
    /**
     * {@inheritdoc}
     */
    initialize: function () {
      var self = this;
      // Listen to any change on the CKEDITOR component.
      Drupal.editors.ckeditor.onChange(this.el, function (val) {
        self.change(val);
      });
    }
  }, {
    tag: 'textarea'
  });

  Drupal.behaviors.drupalForm = {
    attach: function (context) {
      var $context = $(context);
      $('.js-form-item', $('#node-page-edit-form')).each(function () {
        DrupalForm.getFormItemView(this)
      });
    }
  };

})(jQuery, Drupal);