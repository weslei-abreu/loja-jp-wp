<?php

namespace Tickera;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

if ( ! class_exists( 'Tickera\TC_Tickets_Search' ) ) {

    class TC_Tickets_Search {

        var $per_page = 10;
        var $args = array();
        var $post_type = '';
        var $page_name = 'tc_ticket_types';
        var $items_title = 'tickets';
        var $search_term;
        var $raw_page;
        var $page_num;

        function __construct( $search_term = '', $page_num = '', $per_page = 10, $post_status = 'any', $orderby = 'post_date', $order = 'DESC', $search_columns = [], $post_type = 'tc_tickets' ) {

            global $tc;

            $args = [];
            $this->per_page = $per_page == '' ? tickera_global_admin_per_page( $this->per_page ) : $per_page;
            $this->page_name = $tc->name . '_ticket_types';
            $this->search_term = $search_term;
            $this->raw_page = ( '' == $page_num ) ? false : (int) $page_num;
            $this->page_num = (int) ( '' == $page_num ) ? 1 : $page_num;
            $this->post_type = $post_type;

            if ( $this->search_term ) {
                $args[ 's' ] = $this->search_term;
            }

            $args = array_merge( $args, [
                'posts_per_page' => $this->per_page,
                'offset' => ( $this->page_num - 1 ) * $this->per_page,
                'category' => '',
                'orderby' => $orderby,
                'order' => $order,
                'include' => '',
                'exclude' => '',
                'meta_key' => '',
                'meta_value' => '',
                'post_type' => $this->post_type,
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => $post_status
            ] );

            if ( $search_columns ) {
                $args[ 'search_columns' ] = $search_columns;
            }

            $this->args = $args;
        }

        function TC_Tickets_Search( $search_term = '', $page_num = '' ) {
            $this->__construct( $search_term, $page_num );
        }

        function get_args() {
            return $this->args;
        }

        function get_results() {
            return get_posts( $this->args );
        }

        function get_count_of_all() {

            $args = [];

            if ( $this->search_term ) {
                $args[ 's' ] = $this->search_term;
            }

            $args = array_merge( $args, [
                'posts_per_page' => -1,
                'category' => '',
                'orderby' => 'post_date',
                'order' => 'DESC',
                'include' => '',
                'exclude' => '',
                'meta_key' => '',
                'meta_value' => '',
                'post_type' => $this->post_type,
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => 'any'
            ] );

            return count( get_posts( $args ) );
        }

        function page_links() {
            $pagination = new TC_Pagination();
            $pagination->Items( $this->get_count_of_all() );
            $pagination->limit( $this->per_page );
            $pagination->parameterName = 'page_num';
            if ( $this->search_term != '' ) {
                $pagination->target( "edit.php?post_type=tc_events&page=" . $this->page_name . "&s=" . $this->search_term );
            } else {
                $pagination->target( "edit.php?post_type=tc_events&page=" . $this->page_name );
            }
            $pagination->currentPage( $this->page_num );
            $pagination->nextIcon( '&#9658;' );
            $pagination->prevIcon( '&#9668;' );
            $pagination->items_title = $this->items_title;
            $pagination->show();
        }
    }
}
