<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

//30-06-2023
final class WOOF_FRONT_BUILDER_OPTIONS_LAYOUT {
    
    private $db = null;
    private $table = null;
    private $demo = null;
    private $ext_path = null;
    private $is_admin = null;

    public function __construct($db, $table, $ext_path, $demo, $is_admin) {
        $this->db = $db;
        $this->table = $table;
        $this->ext_path = $ext_path;
        $this->demo = $demo;
        $this->is_admin = $is_admin;
        $this->init();
    }

    public function init() {
        add_action('wp_ajax_woof_form_builder_get_layout_options', function () {
			if (!wp_verify_nonce(WOOF_REQUEST::get('woof_front_builder_nonce'), 'front_builder_nonce')) {
				return false;
			}			
            die(json_encode($this->get_options(esc_html($_REQUEST['name']))));
        });
        add_action('wp_ajax_woof_form_builder_save_layout_options', array($this, 'save_options'));

        if ($this->demo) {
            add_action('wp_ajax_nopriv_woof_form_builder_get_layout_options', function () {
				if (!wp_verify_nonce(WOOF_REQUEST::get('woof_front_builder_nonce'), 'front_builder_nonce')) {
					return false;
				}	
                die(json_encode($this->get_options(esc_html($_REQUEST['name']))));
            });
            add_action('wp_ajax_nopriv_woof_form_builder_save_layout_options', array($this, 'save_options'));
        }
    }

    public function save_options() {
		if (!wp_verify_nonce(WOOF_REQUEST::get('woof_front_builder_nonce'), 'front_builder_nonce')) {
            return false;
        }
        if (!$this->is_admin) {
            return false;
        }

        $name = esc_html($_REQUEST['name']);
        $field = esc_html($_REQUEST['field']);
        $value = esc_html($_REQUEST['value']);

        $options = $this->get_options_db($name);
        $options[$field] = $value;

        $this->db->update($this->table, array('layout_options' => json_encode($options)), array('name' => $name));
    }

    public function get_options_db($name) {

        static $cache = [];

        if (!isset($cache[$name])) {
			$prepared_sql = $this->db->prepare("SELECT layout_options FROM %i WHERE name=%s", $this->table, $name);
            $cache[$name] = $this->db->get_row($prepared_sql)->layout_options;
            if (!$cache[$name]) {
                $cache[$name] = '{}';
            }
        }

        return json_decode($cache[$name], true);
    }

    public function get_options($name) {
        $options = require $this->ext_path . 'options/layout_options.php';
        $options_data = $this->get_options_db($name);

        if (!empty($options_data)) {
            foreach ($options as $k => $option) {
                if (array_key_exists($option['field'], $options_data)) {
                    if (is_array($options[$k]['value'])) {
                        //for select (drop-down)
                        $options[$k]['value']['value'] = array_key_exists($option['field'], $options_data) ? $options_data[$option['field']] : $options['value']['value'];
                    } else {
                        $options[$k]['value'] = array_key_exists($option['field'], $options_data) ? $options_data[$option['field']] : $options['value'];
                    }

                    if (woof()->show_notes && isset($options[$k]['is_not_free']) && $options[$k]['is_not_free']) {
                        $options[$k]['description'] .= '. <span class="woof-front-builder-premium">' . esc_html__('Not has effect in free version!', 'woocommerce-products-filter') . '</span>';
                    }
                }
            }
        }

        return $options;
    }

    public function get_options_key_value($name) {
        $res = [];
        $options = $this->get_options($name);

        if (!empty($options)) {
            foreach ($options as $opt) {
                if (is_array($opt['value'])) {
                    $res[$opt['field']] = $opt['value']['value'];
                } else {
                    $res[$opt['field']] = $opt['value'];
                }
            }
        }

        return $res;
    }

}
