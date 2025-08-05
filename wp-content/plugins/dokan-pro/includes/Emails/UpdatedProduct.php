<?php

namespace WeDevs\DokanPro\Emails;

use WC_Email;
use WeDevs\Dokan\Vendor\Vendor;

class UpdatedProduct extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'updated_product_pending';
        $this->title          = __( 'Dokan Updated Pending Product', 'dokan' );
        $this->description    = __( 'Pending Product emails are sent to chosen recipient(s) when a published product is updated by vendors.', 'dokan' );
        $this->template_html  = 'emails/product-updated-pending.php';
        $this->template_plain = 'emails/plain/product-updated-pending.php';
        $this->template_base  = DOKAN_PRO_DIR . '/templates/';
        $this->placeholders   = [
            '{product_title}' => '',
            '{price}'         => '',
            '{seller_name}'   => '',
            '{seller_url}'    => '',
            '{category}'      => '',
            '{product_link}'  => '',
            // Only for backward compatibility.
            '{site_name}'     => $this->get_from_name(),
        ];

        // Triggers for this email
        add_action( 'dokan_edited_product_pending_notification', [ $this, 'trigger' ], 30, 3 );

        // Call parent constructor
        parent::__construct();

        // Other settings
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_title}] A product update is pending from ({seller_name}) - {product_title}', 'dokan' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( '{product_title} updated by Vendor {seller_name}', 'dokan' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param \WC_Product $product  The product object.
     * @param Vendor      $seller   The seller ID.
     * @param string[]    $category Category Name.
     */
    public function trigger( $product, $seller, $category ) {
        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            return;
        }

        $this->setup_locale();
        $category_name = $category ? implode( ', ', $category ) : 'N/A';

        if ( ! is_a( $product, 'WC_Product' ) ) {
            return;
        }
        $this->object                          = $product;
        $this->placeholders['{product_title}'] = $product->get_title();
        $this->placeholders['{price}']         = $product->get_price();
        $this->placeholders['{seller_name}']   = $seller->get_shop_name() ? $seller->get_shop_name() : $seller->get_name();
        $this->placeholders['{seller_url}']    = dokan_get_store_url( $seller->get_id() );
        $this->placeholders['{category}']      = $category_name;
        $this->placeholders['{product_link}']  = admin_url( 'post.php?action=edit&post=' . $product->get_id() );

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
            $this->template_html, [
                'product'            => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => true,
                'plain_text'         => false,
                'email'              => $this,
                'data'               => $this->placeholders,
            ], 'dokan/', $this->template_base
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
            $this->template_plain, [
                'product'            => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => true,
                'plain_text'         => true,
                'email'              => $this,
                'data'               => $this->placeholders,
            ], 'dokan/', $this->template_base
        );
    }

    /**
     * Initialize settings form fields.
     */
    public function init_form_fields() {

        $placeholders = $this->placeholders;
        // remove {site_name} placeholder
        unset( $placeholders['{site_name}'] );
        /* translators: %s: list of placeholders */
        $placeholder_text  = sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>' . implode( '</code>, <code>', array_keys( $placeholders ) ) . '</code>' );
        $this->form_fields = [
            'enabled'    => [
                'title'   => __( 'Enable/Disable', 'dokan' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'dokan' ),
                'default' => 'yes',
            ],
            'recipient'  => [
                'title'       => __( 'Recipient(s)', 'dokan' ),
                'type'        => 'text',
                /* translators: %s: list of placeholders */
                'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'dokan' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ],
            'subject'    => [
                'title'       => __( 'Subject', 'dokan' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ],
            'heading'    => [
                'title'       => __( 'Email heading', 'dokan' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ],
            'additional_content' => [
                'title'       => __( 'Additional content', 'dokan' ),
                'description' => __( 'Text to appear below the main email content.', 'dokan' ) . ' ' . $placeholder_text,
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'dokan' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ],
            'email_type' => [
                'title'       => __( 'Email type', 'dokan' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'dokan' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ],
        ];
    }
}
