<?php

use DokanPro\Modules\Subscription\SubscriptionPack;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Dokan_Subscription_Cancelled' ) ) :

    /**
     * New Product Published Email to vendor.
     *
     * An email sent to the vendor when a pending Product is published by admin.
     *
     * @class       Dokan_Subscription_Cancelled
     * @version     2.9.4
     * @author      weDevs
     * @extends     WC_Email
     */
    class Dokan_Subscription_Cancelled extends WC_Email {

        /**
         * Subscription Object
         *
         * @var null
         */
        public $subscription = null;

        /**
         * Constructor Method
         */
        public function __construct() {
            $this->id             = 'Dokan_Subscription_Cancelled';
            $this->title          = __( 'Dokan Subscription Cancelled', 'dokan' );
            $this->description    = __( 'This email is sent to admin when vendors cancel their subscriptions', 'dokan' );
            $this->template_base  = DPS_PATH . '/templates/';
            $this->template_html  = 'emails/dokan-subscription-cancelled.php';
            $this->template_plain = 'emails/plain/dokan-subscription-cancelled.php';
            $this->placeholders   = [
                '{vendor_name}' => '',
                // only for backwards compatibility
                '{site_name}'   => $this->get_from_name(),
            ];

            // Triggers for this email
            add_action( 'dokan_subscription_cancelled', array( $this, 'trigger' ), 30, 2 );

            // Call parent constructor
            parent::__construct();

            $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
        }

        /**
         * Get email subject.
         *
         * @since  3.1.0
         * @return string
         */
        public function get_default_subject() {
            return __( '[{site_title}] Subscription Cancelled', 'dokan' );
        }

        /**
         * Get email heading.
         *
         * @since  3.1.0
         * @return string
         */
        public function get_default_heading() {
            return __( 'A Subscription is cancelled by {vendor_name}', 'dokan' );
        }

        /**
         * Trigger the sending of this email.
         *
         * @param int $customer_id The customer ID.
         * @param int $product_id The product ID.
         */
        public function trigger( $customer_id, $product_id ) {
            if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
                return;
            }

            $this->setup_locale();
            $vendor = dokan()->vendor->get( $customer_id );
            if ( ! $vendor->get_id() ) {
                return;
            }
            $this->subscription = dokan()->subscription->get( $product_id );

            $this->object                        = $vendor;
            $this->placeholders['{vendor_name}'] = $vendor->get_store_name();

            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
            $this->restore_locale();
        }


        /**
         * Get content html.
         *
         * @access public
         * @return string
         */
        public function get_content_html() {
            return wc_get_template_html(
                $this->template_html,
                array(
                    'vendor'             => $this->object,
                    'email_heading'      => $this->get_heading(),
                    'additional_content' => $this->get_additional_content(),
                    'sent_to_admin'      => true,
                    'plain_text'         => false,
                    'email'              => $this,
                    'subscription'       => $this->subscription,
                ),
                'dokan/',
                $this->template_base
            );
        }

        /**
         * Get content plain.
         *
         * @access public
         * @return string
         */
        public function get_content_plain() {
            return wc_get_template_html(
                $this->template_plain,
                array(
                    'vendor'             => $this->object,
                    'email_heading'      => $this->get_heading(),
                    'additional_content' => $this->get_additional_content(),
                    'sent_to_admin'      => true,
                    'plain_text'         => true,
                    'email'              => $this,
                    'subscription'       => $this->subscription,
                ),
                'dokan/',
                $this->template_base
            );
        }


        /**
         * Initialise settings form fields.
         */
        public function init_form_fields() {
            $placeholders = $this->placeholders;
            unset( $placeholders['{site_name}'] );
            /* translators: %s: list of placeholders */
            $placeholder_text  = sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>' . implode( '</code>, <code>', array_keys( $placeholders ) ) . '</code>' );
            $this->form_fields = array(
                'enabled'            => array(
                    'title'   => __( 'Enable/Disable', 'dokan' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable this email notification', 'dokan' ),
                    'default' => 'yes',
                ),
                'recipient'          => array(
                    'title'       => __( 'Recipient(s)', 'dokan' ),
                    'type'        => 'text',
                    /* translators: %s: default to email address. */
                    'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'dokan' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                    'placeholder' => '',
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'subject'            => array(
                    'title'       => __( 'Subject', 'dokan' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => $placeholder_text,
                    'placeholder' => $this->get_default_subject(),
                    'default'     => '',
                ),
                'heading'            => array(
                    'title'       => __( 'Email heading', 'dokan' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => $placeholder_text,
                    'placeholder' => $this->get_default_heading(),
                    'default'     => '',
                ),
                'additional_content' => array(
                    'title'       => __( 'Additional content', 'dokan' ),
                    'description' => __( 'Text to appear below the main email content.', 'dokan' ) . ' ' . $placeholder_text,
                    'css'         => 'width:400px; height: 75px;',
                    'placeholder' => __( 'N/A', 'dokan' ),
                    'type'        => 'textarea',
                    'default'     => $this->get_default_additional_content(),
                    'desc_tip'    => true,
                ),
                'email_type'         => array(
                    'title'       => __( 'Email type', 'dokan' ),
                    'type'        => 'select',
                    'description' => __( 'Choose which format of email to send.', 'dokan' ),
                    'default'     => 'html',
                    'class'       => 'email_type wc-enhanced-select',
                    'options'     => $this->get_email_type_options(),
                    'desc_tip'    => true,
                ),
            );
        }
    }

endif;

return new Dokan_Subscription_Cancelled();
