<?php
/**
 * Better Ticket Types
 * Better ticket types presentation for Tickera
 */

namespace Tickera\Addons;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( ! class_exists( 'Tickera\Addons\TC_Better_Ticket_Types' ) ) {

    class TC_Better_Ticket_Types {

        var $version = '1.0';
        var $title = 'Better Ticket Types';
        var $name = 'better-ticket-types';

        function __construct() {
            global $post;

            if ( ! isset( $post ) ) {
                $post_id = isset( $_GET[ 'post' ] ) ? (int) $_GET[ 'post' ] : '';
                $post_type = get_post_type( $post_id );

            } else {
                $post_type = get_post_type( $post );
            }

            if ( empty( $post_type ) ) {
                $post_type = isset( $_GET[ 'post_type' ] ) ? sanitize_text_field( $_GET[ 'post_type' ] ) : '';
            }

            add_filter( 'manage_tc_tickets_posts_columns', array( $this, 'manage_tc_tickets_columns' ) );
            add_action( 'manage_tc_tickets_posts_custom_column', array( $this, 'manage_tc_tickets_posts_custom_column' ) );
            add_filter( "manage_edit-tc_tickets_sortable_columns", array( $this, 'manage_edit_tc_tickets_sortable_columns' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_ticket_types_metaboxes' ), 10, 2 );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_and_styles' ) );

            if ( $post_type == 'tc_tickets' ) {
                add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );
                add_filter( 'page_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
                add_filter( 'wp_editor_settings', array( $this, 'wp_editor_settings' ), 10, 2 );
                add_action( 'edit_form_after_editor', array( $this, 'edit_form_after_editor' ), 10, 1 );
                add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 10, 2 );
            }

            add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
            add_action( 'save_post', array( $this, 'save_metabox_values' ) );

            if ( apply_filters( 'tc_is_woo', false ) == false ) {//make sure to duplicate ticket types for standaline version only
                add_action( 'tc_after_event_duplication', array( $this, 'duplicate_event_ticket_types' ), 10, 5 );
            }
        }

        function duplicate_event_ticket_types( $new_event_id, $old_event_id, $caller, $caller_id, $old_caller_id ) {
            global $wpdb;

            /*
             * Backward Compatibility
             * PHP Deprecated: Required parameter follows optional parameter
             */
            $caller = $caller ? $caller : 'standard';

            $new_post_author = wp_get_current_user();
            $new_post_date = current_time( 'mysql' );
            $new_post_date_gmt = get_gmt_from_date( $new_post_date );

            $old_event = new \Tickera\TC_Event( $old_event_id );
            $old_ticket_types = $old_event->get_event_ticket_types( array( 'publish', 'draft', 'pending', 'private' ) );
            $old_and_new_ticket_types = array();

            foreach ( $old_ticket_types as $old_ticket_type_id ) {

                $post_id = (int) $old_ticket_type_id;
                $post = get_post( $post_id );

                $post_title = $post->post_title;
                $post_status = $post->post_status;

                /*
                 * new post data array
                 */
                $args = apply_filters( 'tc_duplicate_event_ticket_types_args', array(
                    'post_author'               => (int) $new_post_author->ID,
                    'post_date'                 => $new_post_date,
                    'post_date_gmt'             => $new_post_date_gmt,
                    'comment_status'            => $post->comment_status,
                    'ping_status'               => $post->ping_status,
                    'pinged'                    => $post->pinged,
                    'to_ping'                   => $post->to_ping,
                    'post_content'              => $post->post_content,
                    'post_content_filtered'     => $post->post_content_filtered,
                    'post_excerpt'              => $post->post_excerpt,
                    'post_name'                 => $post->post_name,
                    'post_parent'               => (int) $post->post_parent,
                    'post_password'             => $post->post_password,
                    'post_status'               => $post->post_status,
                    'post_title'                => $post->post_title,
                    'post_type'                 => $post->post_type,
                    'post_modified'             => $new_post_date,
                    'post_modified_gmt'         => $new_post_date_gmt,
                    'menu_order'                => (int) $post->menu_order,
                    'post_mime_type'            => $post->post_mime_type,
                ), $post_id );

                // Insert the post by wp_insert_post() function
                $new_post_id = wp_insert_post( tickera_sanitize_array( $args, true ) );
                $old_and_new_ticket_types[] = array( $old_ticket_type_id, $new_post_id );

                $wpdb->update(
                    $wpdb->posts, array(
                        'post_name' => wp_unique_post_slug( sanitize_title( $post_title, $new_post_id ), $new_post_id, $post_status, $post->post_type, 0 ),
                        'guid' => get_permalink( $new_post_id ),
                    ), array( 'ID' => $new_post_id )
                );

                /*
                 * Get all current post terms ad set them to the new post draft
                 * Returns array of taxonomy names for post type, ex array("category", "post_tag");
                 */
                $taxonomies = get_object_taxonomies( $post->post_type );

                foreach ( $taxonomies as $taxonomy ) {
                    $post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
                    wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
                }

                // Duplicate all post meta just in two SQL queries
                $this->duplicate_post_meta( $post_id, (int) $new_post_id );

                // Replace event ids
                update_post_meta( (int) $new_post_id, apply_filters( 'tc_event_name_field_name', 'event_name' ), (int) $new_event_id );
            }

            do_action( 'tc_after_ticket_type_duplication', $new_event_id, $old_event_id, $caller, $caller_id, $old_caller_id, $old_and_new_ticket_types );
        }

        function duplicate_post_meta( $id, $new_id ) {
            global $wpdb;

            $sql = $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", absint( $id ) );

            /*
             * $exclude = array_map('esc_sql', array('_edit_lock', '_edit_last'));
             * if (sizeof($exclude)) {
             *  $sql .= " AND meta_key NOT IN ( '" . implode("','", $exclude) . "' )";
             * }
             */

            $post_meta = $wpdb->get_results( $sql );

            if ( sizeof( $post_meta ) ) {
                $sql_query_sel = [];
                $table_columns = [ 'post_id', 'meta_key', 'meta_value' ];
                $prepare_table_columns_placeholder = implode( ',', array_fill( 0, count( $table_columns ), '%1s' ) );
                $sql_query = $wpdb->prepare( "INSERT INTO {$wpdb->postmeta} ($prepare_table_columns_placeholder) ", $table_columns );

                foreach ( $post_meta as $post_meta_row ) {
                    $sql_query_sel[] = $wpdb->prepare( "SELECT %d, %s, %s", $new_id, $post_meta_row->meta_key, $post_meta_row->meta_value );
                }

                $sql_query .= implode( " UNION ALL ", $sql_query_sel );
                $wpdb->query( $sql_query );
            }
        }

        function post_updated_messages( $messages ) {

            $post = get_post();
            $post_type = get_post_type( $post );
            $post_type_object = get_post_type_object( $post_type );

            $messages[ 'tc_tickets' ] = array(
                0 => '', // Unused. Messages start at index 1.
                1 => __( 'Ticket Type updated.', 'tickera-event-ticketing-system' ),
                2 => __( 'Custom field updated.', 'tickera-event-ticketing-system' ),
                3 => __( 'Custom field deleted.', 'tickera-event-ticketing-system' ),
                4 => __( 'Ticket Type updated.', 'tickera-event-ticketing-system' ),
                /* translators: %s: date and time of the revision */
                5 => isset( $_GET[ 'revision' ] )
                    ? sprintf(
                        /* translators: %s: Formatted datetime timestamp of a revision. */
                        __( 'Ticket Type restored to revision from %s', 'tickera-event-ticketing-system' ),
                        wp_post_revision_title( (int) $_GET[ 'revision' ], false )
                    )
                    : false,
                6 => __( 'Ticket Type published.', 'tickera-event-ticketing-system' ),
                7 => __( 'Ticket Type saved.', 'tickera-event-ticketing-system' ),
                8 => __( 'Ticket Type submitted.', 'tickera-event-ticketing-system' ),
                9 => sprintf(
                    /* translators: 1: Formatted datetime timestamp of a scheduled ticket type. */
                    __( 'Ticket Type scheduled for: <strong>%1$s</strong>.', 'tickera-event-ticketing-system' ),
                    date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) )
                ),
                10 => __( 'Ticket Type draft updated.', 'tickera-event-ticketing-system' )
            );
            return $messages;
        }

        function edit_form_after_editor( $post ) {

            $description = sprintf(
                /* translators: %s: Url that links to Tickera > Ticket Templates page. */
                __( 'Short description of the ticket type. You can display the content of this field on the ticket itself if you place <i>Ticket Description</i> element in the selected <a href="%s" target="_blank">ticket template</a>.', 'tickera-event-ticketing-system' ),
                esc_url( admin_url( 'edit.php?post_type=tc_events&page=tc_ticket_templates' ) )
            );

            echo wp_kses_post( '<span class="description">' . wp_kses_post( $description ) . '</span>' );
        }

        function wp_editor_settings( $settings, $editor_id ) {
            $settings[ 'editor_height' ] = 20;
            $settings[ 'textarea_rows' ] = 5;
            return $settings;
        }

        function enter_title_here( $enter_title_here, $post ) {
            if ( get_post_type( $post ) == 'tc_tickets' ) {
                $enter_title_here = __( 'Enter ticket type title here. VIP, Standard, Early Bird etc', 'tickera-event-ticketing-system' );
            }
            return $enter_title_here;
        }

        function post_row_actions( $actions, $post ) {
            unset( $actions[ 'view' ] );
            unset( $actions[ 'inline hide-if-no-js' ] );
            return $actions;
        }

        /**
         * Save post meta values
         *
         * @param $post_id
         * @throws \Exception
         */
        function save_metabox_values( $post_id ) {

            if ( 'tc_tickets' == get_post_type( $post_id ) ) {

                $metas = [];

                $post_data = tickera_sanitize_array( $_POST, true, true );
                $post_data = $post_data ? $post_data : [];

                foreach ( $post_data as $field_name => $field_value ) {

                    if ( preg_match( '/_post_meta/', $field_name ) ) {
                        $metas[ sanitize_key( str_replace( '_post_meta', '', $field_name ) ) ] = sanitize_text_field( $field_value );
                    }
                }

                $metas = apply_filters( 'tc_ticket_type_metas', $metas );

                foreach ( $metas as $key => $value ) {
                    update_post_meta( (int) $post_id, $key, tickera_sanitize_array( $value, true, true ) );
                }
            }
        }

        /**
         * Enqueue scripts and styles
         */
        function admin_enqueue_scripts_and_styles() {
            global $post, $post_type;
            if ( 'tc_tickets' == $post_type ) {
                wp_enqueue_style( 'tc-better-ticket-types', plugins_url( 'css/admin.css', __FILE__ ) );
            }
        }

        /**
         * Add table column titles
         *
         * @param $columns
         * @return mixed
         */
        function manage_tc_tickets_columns( $columns ) {
            $ticket_types_columns = \Tickera\TC_Tickets::get_ticket_fields();
            foreach ( $ticket_types_columns as $ticket_types_column ) {
                if ( isset( $ticket_types_column[ 'table_visibility' ] ) && true == $ticket_types_column[ 'table_visibility' ] && $ticket_types_column[ 'field_name' ] !== 'post_title' ) {
                    $columns[ $ticket_types_column[ 'field_name' ] ] = $ticket_types_column[ 'field_title' ];
                }
            }
            unset( $columns[ 'date' ] );
            return $columns;
        }

        /**
         * Add table column values
         *
         * @param $name
         */
        function manage_tc_tickets_posts_custom_column( $name ) {

            global $post, $tc;

            $ticket_types_columns = \Tickera\TC_Tickets::get_ticket_fields();

            foreach ( $ticket_types_columns as $ticket_types_column ) {

                if ( isset( $ticket_types_column[ 'table_visibility' ] ) && $ticket_types_column[ 'table_visibility' ] == true && $ticket_types_column[ 'field_name' ] !== 'post_title' ) {

                    if ( $ticket_types_column[ 'field_name' ] == $name ) {

                        if ( isset( $ticket_types_column[ 'post_field_type' ] ) && $ticket_types_column[ 'post_field_type' ] == 'post_meta' ) {

                            $value = get_post_meta( $post->ID, $ticket_types_column[ 'field_name' ], true );
                            $value = ( '' != $value ) ? $value : '-';

                            switch ( $ticket_types_column[ 'field_name' ] ) {

                                case 'price_per_ticket':
                                    $value = $tc->get_cart_currency_and_format( $value );
                                    break;

                                case 'event_name':
                                    $event = new \Tickera\TC_Event( $value );
                                    $value = $event->details->post_title;
                                    break;

                                case 'quantity_available':

                                    $event_id = get_post_meta( $post->ID, 'event_name', true );
                                    $event_metas = get_post_meta( $event_id );
                                    $limit_on_event_level = ( isset( $event_metas[ 'limit_level' ] ) && $event_metas[ 'limit_level' ][ 0 ] ) ? true : false;

                                    if ( $limit_on_event_level ) {
                                        $limit_level_value = ( isset( $event_metas[ 'limit_level_value' ] ) && '' !== $event_metas[ 'limit_level_value' ][ 0 ] ) ? (int) $event_metas[ 'limit_level_value' ][ 0 ] : __( 'Unlimited', 'tickera-event-ticketing-system' );
                                        $value = $limit_level_value . '<br><a href="' . esc_url( admin_url( "post.php?post=" . esc_attr( $event_id ) . "&action=edit#limit_level-tc-metabox-wrapper", ( is_ssl() ? 'https' : 'http' ) ) ) . '"><small>' . esc_html__( 'Event level', 'tickera-event-ticketing-system' ) . '</small></a>';

                                    } else {
                                        $value = ( '-' == $value ) ? __( 'Unlimited', 'tickera-event-ticketing-system' ) : $value;
                                    }
                                    break;

                                case 'available_checkins_per_ticket':
                                    $value = ( '-' == $value ) ? __( 'Unlimited', 'tickera-event-ticketing-system' ) : $value;
                                    break;

                                case 'checkins_time_basis':

                                    if ( in_array( $value, [ '-', 'no' ] ) ) {
                                        $value = __( '-', 'tickera-event-ticketing-system' );

                                    } else {

                                        $allowed_checkins = get_post_meta( $post->ID, 'allowed_checkins_per_time_basis', true );
                                        $allowed_checkins = ( '' == $allowed_checkins ) ? __( 'Unlimited', 'tickera-event-ticketing-system' ) : $allowed_checkins;
                                        $type = get_post_meta( $post->ID, 'checkins_time_basis_type', true );
                                        $calendar = get_post_meta( $post->ID, 'checkins_time_calendar_basis', true );

                                        $value = sprintf(
                                                /* translators: 1: The number of allowed checkins 2: The type of checkins per time basis (e.g hour, day, week, month) */
                                                __( '%1$s per %2$s', 'tickera-event-ticketing-system' ),
                                                $allowed_checkins,
                                                $type
                                            );

                                        if ( $type && 'hour' != $type && $calendar ) {
                                            $calendar_label = ( 'yes' == $calendar ) ? __( 'calendar day', 'tickera-event-ticketing-system' ) : __( '24 hours', 'tickera-event-ticketing-system' );
                                            $value .= sprintf(
                                                    /* translators: %s: Calendar basis label (e.g Calendar day or 24 hours). */
                                                    __( " (%s)", 'tickera-event-ticketing-system' ),
                                                    $calendar_label
                                                );
                                        }
                                    }
                                    break;

                                case 'quantity_sold':
                                    $sold_count = tickera_get_tickets_count_sold( $post->ID );
                                    $value = ( $sold_count > 0 ) ? $sold_count : '-';
                                    break;

                                case 'ticket_fee':
                                    $ticket_fee_type = get_post_meta( $post->ID, 'ticket_fee_type', true );

                                    if ( ! empty( $value ) && $value !== '0' && $value !== 0 && $value !== '-' )
                                        $value = ( 'fixed' == $ticket_fee_type ) ? $tc->get_cart_currency_and_format( $value ) : $value . '%';
                                    else
                                        $value = '-';
                                    break;
                            }

                            echo wp_kses( $value, [
                                'br' => [],
                                'small' => [],
                                'a' => [ 'href' => [] ],
                            ] );

                        } elseif ( 'ticket_active' == $ticket_types_column[ 'field_name' ] ) {
                            $ticket_type_status = get_post_status( $post->ID );
                            $on = $ticket_type_status == 'publish' ? 'tc-on' : '';
                            echo wp_kses( '<div class="tc-control ' . esc_attr( $on ) . '" ticket_id="' . esc_attr( $post->ID ) . '"><div class="tc-toggle"></div></div>', wp_kses_allowed_html( 'tickera_toggle' ) );

                        } elseif ( 'ticket_shortcode' == $ticket_types_column[ 'field_name' ] ) {
                            echo wp_kses( '[tc_ticket id="' . esc_attr( $post->ID ) . '"]', wp_kses_allowed_html( 'tickera_add_to_cart' ) );
                        }
                    }
                }
            }
        }

        function manage_edit_tc_tickets_sortable_columns( $columns ) {
            $custom = array(
                /* 'quantity_available' => 'quantity_available',
                'quantity_sold'		 => 'quantity_sold', */
            );
            return wp_parse_args( $custom, $columns );
        }

        /**
         * Add control for setting an event as active or inactive
         */
        function post_submitbox_misc_actions() {
            global $post, $post_type;

            $ticket_type_columns = \Tickera\TC_Tickets::get_ticket_fields();

            foreach ( $ticket_type_columns as $ticket_type_column ) {
                if ( isset( $ticket_type_column[ 'show_in_post_type' ] ) && $ticket_type_column[ 'show_in_post_type' ] == true && isset( $ticket_type_column[ 'post_type_position' ] ) && $ticket_type_column[ 'post_type_position' ] == 'publish_box' ) { ?>
                    <div class="misc-pub-section <?php echo esc_attr( $ticket_type_column[ 'field_name' ] ); ?>">
                        <?php echo wp_kses( \Tickera\TC_Fields::render_post_type_field( '\Tickera\TC_Ticket', $ticket_type_column, $post->ID, false ), wp_kses_allowed_html( 'tickera_setting' ) ); ?>
                    </div><?php
                }
            }

            $ticket_type_status = get_post_status( $post->ID );
            $on = $ticket_type_status == 'publish' ? 'tc-on' : '';
            ?>
            <div class="misc-pub-section misc-pub-visibility-activity" id="visibility">
                <?php if ( current_user_can( apply_filters( 'tc_ticket_type_activation_capability', 'edit_others_ticket_types' ) ) || current_user_can( 'manage_options' ) ) { ?>
                    <span id="post-visibility-display"><?php echo wp_kses( '<div class="tc-control ' . esc_attr( $on ) . '" ticket_id="' . esc_attr( $post->ID ) . '"><div class="tc-toggle"></div></div>', wp_kses_allowed_html( 'tickera_toggle' ) ); ?></span>
                <?php }
                if ( isset( $_GET[ 'post' ] ) ) {
                    $ticket = new \Tickera\TC_Ticket( (int) $_GET[ 'post' ] );
                    $template_id = $ticket->details->ticket_template; ?>
                    <a class="ticket_preview_link" target="_blank" href="<?php echo esc_url( apply_filters( 'tc_ticket_preview_link', admin_url( 'edit.php?post_type=tc_events&page=tc_ticket_templates&action=preview&ticket_type_id=' . (int) $_GET[ 'post' ] ) . '&template_id=' . $template_id ) ); ?>"><?php esc_html_e( 'Preview', 'tickera-event-ticketing-system' ); ?></a>
                <?php } ?>
            </div>
            <?php
        }

        function non_visible_fields() {
            return array(
                'ID',
                'ticket_type_name',
                'quantity_sold',
                'ticket_active',
                'ticket_shortcode'
            );
        }

        /**
         * Tickera > Ticket Types Metaboxes
         *
         * @param $post_type
         * @param $post
         */
        function add_ticket_types_metaboxes( $post_type, $post ) {

            global $pagenow, $typenow;

            if ( ! $post || ! isset( $post->ID ) ) {
                return;
            }

            $tc_general_settings = get_option( 'tickera_general_setting', false );
            $force_login = ( isset( $tc_general_settings[ 'force_login' ] ) ) ? $tc_general_settings[ 'force_login' ] : 'no';

            if ( ( 'edit.php' == $pagenow ) || ( $post->post_type !== 'tc_tickets' ) ) {
                return;
            }

            $post_id = isset( $_GET[ 'post' ] ) ? (int) $_GET[ 'post' ] : 0;
            $ticket_types_columns = \Tickera\TC_Tickets::get_ticket_fields();

            foreach ( $ticket_types_columns as $ticket_types_column ) {
                if ( ! in_array( $ticket_types_column[ 'field_name' ], $this->non_visible_fields() ) ) {

                    $args = array(
                        'post_id' => $post_id,
                        'field_name' => $ticket_types_column[ 'field_name' ]
                    );

                    if ( 'max_tickets_per_user' == $ticket_types_column[ 'field_name' ] && 'no' == $force_login ) {
                        // Do not add metabox
                    } else {
                        add_meta_box( $ticket_types_column[ 'field_name' ] . '-tc-metabox-wrapper', $ticket_types_column[ 'field_title' ] . ( ( isset( $ticket_types_column[ 'tooltip' ] ) && $ticket_types_column[ 'tooltip' ] ) ? wp_kses_post( tickera_tooltip( $ticket_types_column[ 'tooltip' ] ) ) : '' ), function () use ( $args ) {
                            tickera_render_ticket_type_metabox( $args[ 'post_id' ], $args[ 'field_name' ] );
                        }, 'tc_tickets', isset( $ticket_types_column[ 'metabox_context' ] ) ? $ticket_types_column[ 'metabox_context' ] : 'normal' );
                    }
                }
            }
        }
    }

    global $better_ticket_types;
    $better_ticket_types = new TC_Better_Ticket_Types();
}

/**
 * Deprecated function "tc_render_ticket_type_metabox".
 * @since 3.5.3.0
 */
if ( ! function_exists( 'tickera_render_ticket_type_metabox' ) ) {

    function tickera_render_ticket_type_metabox( $post_id, $field_name ) {
        $ticket_types_columns = \Tickera\TC_Tickets::get_ticket_fields();
        foreach ( $ticket_types_columns as $ticket_types_column ) {
            if ( $ticket_types_column[ 'field_name' ] == $field_name ) {
                echo wp_kses( \Tickera\TC_Fields::render_post_type_field( '\Tickera\TC_Ticket', $ticket_types_column, $post_id, false ), wp_kses_allowed_html( 'tickera_setting' ) );
            }
        }
    }
}
