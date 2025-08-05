<?php
if (!defined('ABSPATH') || function_exists('Zota_Elementor_Responsive_Base') ) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;

abstract class Zota_Elementor_Responsive_Base extends Zota_Elementor_Widget_Base {

    public function get_name() {
        return 'tbay-responsive';
    }

    /**
     * Retrieve available column options.
     *
     * @return array Column options.
     */
    private function get_columns() {
        $transient_key = 'zota_elementor_columns';
        $columns = get_transient($transient_key);

        if (false === $columns) {
            $columns = apply_filters('zota_admin_elementor_columns', [
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6,
                7 => 7,
                8 => 8,
            ]);
            set_transient($transient_key, $columns, WEEK_IN_SECONDS);
        }

        return $columns;
    }

    protected function add_control_responsive($condition = array()) {

        $this->start_controls_section(
            'section_responsive',
            [
                'label' => esc_html__( 'Responsive Settings', 'zota' ),
                'type' => Controls_Manager::SECTION,
                'condition' => $condition,
            ]
        );
   

        $this->add_responsive_control(
            'column',
            [
                'label'     => esc_html__('Columns', 'zota'),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 4,
                'options'   => $this->get_columns(),
                'devices' => [ 'desktop', 'tablet', 'mobile' ],
                'desktop_default' => 4,
                'tablet_default' => 3,
                'mobile_default' => 2,
            ]
        );

        $controls = [
            'col_desktop' => ['description' => esc_html__('Column apply when the width is between 1200px and 1600px', 'zota'), 'default' => 4],
            'col_desktopsmall' => ['description' => esc_html__('Column apply when the width is between 992px and 1199px', 'zota'), 'default' => 2],
            'col_landscape' => ['description' => esc_html__('Column apply when the width is between 576px and 767px', 'zota'), 'default' => 2],
        ];

        foreach ($controls as $id => $args) {
            $this->add_control($id, array_merge([
                'label' => ucwords(str_replace('_', ' ', $id)),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_columns(),
            ], $args));
        }

        $this->end_controls_section();
    }
}