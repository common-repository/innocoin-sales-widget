<?php
/*
Plugin Name: Innocoin Plugin
Description: Innocoin plugin and widget
Version: 1.2.5
Author: Andriy Palamarchuk
*/


add_action("widgets_init", function () {
    register_widget("Innocoin");
});


add_action('init', 'do_output_buffer322328');
function do_output_buffer322328() {
    $date = new DateTime();
    $css = 'https://www.innocoin.com/assets/css/innocoin-v1.0.min.css?v=' . $date->getTimestamp();
    wp_register_style('innocoin', $css);
    ob_start();
}

function innocoin_widget( $atts ) {
    global $wpdb;
    $tableName = $wpdb->prefix . 'innocoin';
    $id = esc_sql($atts['id']);
    $getInnocoin = $wpdb->get_results("SELECT * FROM {$tableName} WHERE `id`={$id}");
    $atts = shortcode_atts( array(
        'id' => '1',
    ), $atts, 'innocoin' );

    $output = "";
    wp_enqueue_style('innocoin');
    foreach ($getInnocoin as $innocoin) {

        $target = 'ETH';
        $currency_pair = explode('_', esc_attr($innocoin->source));
        $source = $currency_pair[0];

        if($currency_pair[1]) {
            $target = $currency_pair[1];
        }

        $output .= '<iframe src="https://swap.innocoin.com/#/?language=' . esc_attr($innocoin->language) . '&partner_email=' . esc_attr($innocoin->partner_email) . '&partner_address=' . esc_attr($innocoin->partner_address) . '&partner_margin=' . esc_attr($innocoin->partner_margin) . '&source=' . $source . '&target=' . $target . '&target_amount=' . esc_attr($innocoin->target_amount) . '&show_info=true&theme=' . esc_attr($innocoin->theme) . '&width=' . esc_attr($innocoin->width) . '&height=' . esc_attr($innocoin->height) . '&rounded_corners=' . esc_attr($innocoin->rounded_corners) . '&width=' . esc_attr($innocoin->width) . '&height=' . esc_attr($innocoin->height) . '&border=false&partner_name=' . esc_attr($_SERVER['HTTP_HOST']) . '&iframe=true" border="0" scrolling="no" class="innocoin w' . esc_attr($innocoin->width) . ' h' . esc_attr($innocoin->height) . '"></iframe>';
    }

    return $output;
}
add_shortcode( 'innocoin', 'innocoin_widget' );

class Innocoin extends WP_Widget
{
    public function __construct() {
        parent::__construct("innocoin_widget", "Innocoin Widget",
            ["description" => "Innocoin widget for installation in the area of widgets"]);
    }
    public function form($instance) {
        global $wpdb;

        $tableName = $wpdb->prefix . 'innocoin';
        $innocoin = $wpdb->get_results( "SELECT * FROM {$tableName}" );

        if (!empty($instance)) {
            $widgetProfile = $instance["widget_profile"];
        }

            echo '<p>';


            $widgetProfileId = $this->get_field_id("widget_profile");
            $widgetProfileName = $this->get_field_name("widget_profile");

            echo '<p><label for="' . $widgetProfileId . '">Select Widget Profile</label><br />';


            echo '<select name="' . $widgetProfileName . '">';
            foreach ($innocoin as $item) {
                if ($item->width <= 300 || $item->width == 'fluid') {
                    if ($widgetProfile == $item->id) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }
                    echo '<option value="' . esc_attr($item->id) . '" ' . $selected . '>' . esc_html($item->name) . '</option>';

                }
            }
            echo '</select></p>';


        echo '</p><p><i>Only fluid-width widgets or widgets with the width 300px and smaller will be displayed here</i></p>';


    }
    public function update($newInstance, $oldInstance) {
        return $newInstance;
    }

    public function widget($args, $instance)
    {
            global $wpdb;

            $tableName = $wpdb->prefix . 'innocoin';
            $widgetProfile = esc_sql($instance['widget_profile']);
            $getInnocoin = $wpdb->get_results("SELECT * FROM {$tableName} WHERE `id`={$widgetProfile}");


            wp_enqueue_style('innocoin');
            foreach ($getInnocoin as $innocoin) {


            $target = 'ETH';
            $currency_pair = explode('_', esc_attr($innocoin->source));
            $source = $currency_pair[0];

            if($currency_pair[1]) {
                $target = $currency_pair[1];
            }

            echo '<aside class="widget"><iframe src="https://swap.innocoin.com/#/?language=' . esc_attr($innocoin->language) . '&partner_email=' . esc_attr($innocoin->partner_email) . '&partner_address=' . esc_attr($innocoin->partner_address) . '&partner_margin=' . esc_attr($innocoin->partner_margin) . '&source=' . $source . '&target=' . $target . '&target_amount=' . esc_attr($innocoin->target_amount) . '&show_info=false&theme=' . esc_attr($innocoin->theme) . '&width=' . esc_attr($innocoin->width) . '&height=' . esc_attr($innocoin->height) . '&rounded_corners=' . esc_attr($innocoin->rounded_corners) . '&width=' . esc_attr($innocoin->width) . '&height=' . esc_attr($innocoin->height) . '&border=false&partner_name=' . esc_attr($_SERVER['HTTP_HOST']) . '&iframe=true" border="0" scrolling="no" class="innocoin w' . esc_attr($innocoin->width) . ' h' . esc_attr($innocoin->height) . '"></iframe></aside>';
        }
    }
}

add_action('admin_menu', 'innocoin_menu');
function innocoin_menu() {
    add_menu_page('Innocoin', 'Innocoin', 'manage_options', 'innocoin', 'innocoin_config', plugin_dir_url( __FILE__ ) . 'innocoin-menu-icon.png');
    add_submenu_page(null, 'Innocoin Add Profile', 'Innocoin Add Profile', 'manage_options','innocoin/add', 'innocoin_add' );
    add_submenu_page(null, 'Innocoin Remove Profile', 'Innocoin Remove Profile', 'manage_options','innocoin/remove', 'innocoin_remove' );
    add_submenu_page(null, 'Innocoin Edit Profile', 'Innocoin Edit Profile', 'manage_options','innocoin/edit', 'innocoin_edit' );
}

if(!class_exists( 'WP_List_Table' )) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


function onActivate() {
    global $wpdb;
    $tableName = $wpdb->prefix . 'innocoin';
    if($wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName) {
        $sql = "CREATE TABLE " . $tableName . " (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  name varchar(120) NOT NULL,
                  language varchar(120) NOT NULL,
                  partner_email varchar(120) NOT NULL,
                  partner_address varchar(120) NOT NULL,
                  partner_margin varchar(120) NOT NULL,
                  source varchar(120) NOT NULL,
                  target_amount varchar(120) NOT NULL,
                  theme varchar(120) NOT NULL,
                  rounded_corners varchar(120) NOT NULL,
                  width varchar(120) NOT NULL,
                  height varchar(120) NOT NULL,
                  PRIMARY KEY  (id),
                  KEY (name)
                  );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'onActivate');

class Tasks_List_Table extends WP_List_Table {
    function get_columns(){
        $columns = [
            'name' => 'Name',
            'size'    => 'Size',
            'source' => 'Currency Pair',
            'language'      => 'Language',
            'theme' => 'Theme',
            'shortcode' => 'Shortcode',
            'edit' => 'Edit',
            'remove' => 'Remove'
        ];
        return $columns;
    }
    function get_sortable_columns() {
        $sortable_columns = [
            'name'  => ['name', false],
            'size' => ['size', false],
            'language'   => ['language', false],
            'theme' => ['theme', false],
        ];
        return $sortable_columns;
    }

    function prepare_items() {
        global $wpdb;
        $tableName = $wpdb->prefix . 'innocoin';
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];
        $per_page = 15;

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM `$tableName`");

        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderBy = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], ['asc', 'desc'])) ? $_REQUEST['order'] : 'desc';

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages'   => ceil($total_items / $per_page),
        ]);

        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$tableName}` ORDER BY $orderBy $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
    }
    function column_default( $item, $column_name ) {
        switch($column_name) {
            case 'name':
                return '<a href="admin.php?page=innocoin/edit&id=' . esc_attr($item['id']) . '">' . esc_html($item['name']) . '</a>';
                break;
            case 'edit':
                $nonce = wp_create_nonce( 'edit_'. esc_attr($item['id']));
                return '<a href="admin.php?page=innocoin/edit&id=' . esc_attr($item['id']) . '&_wpnonce=' . $nonce. '">Edit</a>';
                break;
            case 'remove':
                $nonce = wp_create_nonce( 'remove_'. esc_attr($item['id']));
                return '<a href="admin.php?page=innocoin/remove&id=' . esc_attr($item['id']) . '&_wpnonce=' . $nonce. '">Remove</a>';
                break;
            case 'shortcode':
                return "<input type='text' value='[innocoin id=\"{$item['id']}\"]' style='width:100%' onClick='this.select();' />";
                break;
            case 'size':
                return esc_attr($item['width']) . 'x' . esc_attr($item['height']);
            break;
            default:
                return $item[$column_name];

        }
    }
}

function innocoin_config() {
    if( current_user_can('manage_options') ) {
        $myListTable = new Tasks_List_Table();
        echo '<div class="wrap"><img src="' . plugin_dir_url( __FILE__ ) . 'logo.png" style="width:300px; clear:both; margin-bottom:1em;"/><h2>Innocoin Widget Profiles <a href="admin.php?page=innocoin/add" class="page-title-action">Add new Widget</a></h2>Create your innocoin widgets and start generating revenue. 50% of the profit goes directly to your bitcoin address. Additional info can be found on <a href="https://www.innocoin.com">https://www.innocoin.com</a>.';
        $myListTable->prepare_items();
        $myListTable->display();
        echo '</div>';
    }
}


function innocoin_remove() {
    if( current_user_can('manage_options') ) {

        $id = (int)$_GET['id'];

        if (!empty($id) &&  check_admin_referer( 'remove_'.$id)) {
            global $wpdb;
            if ($wpdb->delete(
                $wpdb->prefix . 'innocoin',
                ['id' => $id])
            ) {
                wp_redirect(admin_url('admin.php?page=innocoin'));

            }
        }
    }
}

function innocoin_add() {
    if( current_user_can('manage_options') ) {
        global $wpdb;
        if (isset($_POST) && !empty($_POST) && check_admin_referer('add', 'addSec')) {

            if ($_POST['widget_size']) {
                $widgetSize = explode('_', sanitize_text_field($_POST['widget_size']));
                $width = $widgetSize[0];
                $height = $widgetSize[1];
            } else {
                $width = 250;
                $height = 250;
            }

            $widgetName = $width . 'x' . $height . '-' . sanitize_text_field($_POST['theme']) . '-' . date('YmdHi');
            if ($wpdb->insert(
                $wpdb->prefix . 'innocoin',
                [
                    'name' => sanitize_text_field($widgetName),
                    'language' => sanitize_text_field($_POST['language']),
                    'partner_email' => sanitize_text_field($_POST['partner_email']),
                    'partner_address' => sanitize_text_field($_POST['partner_address']),
                    'partner_margin' => sanitize_text_field($_POST['partner_margin']),
                    'source' => sanitize_text_field($_POST['default_currency_pair']),
                    'target_amount' => sanitize_text_field($_POST['target_amount']),
                    'theme' => sanitize_text_field($_POST['theme']),
                    'rounded_corners' => sanitize_text_field($_POST['rounded_corners']),
                    'width' => $width,
                    'height' => $height,
                ],
                [
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                ]
            )
            ) {
                wp_redirect(admin_url('admin.php?page=innocoin'));
            }
        }

        echo '<div class="wrap"><h2>Innocoin Add New Widget Profile</h2>';
        wp_enqueue_script('jquery');

        global $wpdb;

        $tableName = $wpdb->prefix . 'innocoin';
        $getInnocoin = $wpdb->get_results("SELECT * FROM {$tableName} ORDER BY `id` DESC LIMIT 1");

        foreach ($getInnocoin as $innocoin) {
            $partnerAddress = $innocoin->partner_address;
            $partnerEmail = $innocoin->partner_email;
        }
        wp_enqueue_style('innocoin');
        ?>
        <script>
            jQuery(document).ready(function ($) {

                var rounded_corners = true;
                $("input:radio[name=rounded_corners]").click(function () {
                    rounded_corners = $(this).val();
                });

                $('#partner_address').keypress(function () {
                    $(this).change();
                });
                $('#partner_email').keypress(function () {
                    $(this).change();
                });

                $('.checkChange').change(function () {
                    var partner_address = $('#partner_address').val();
                    var partner_email = $('#partner_email').val();
                    var partner_margin = $('#partner_margin').val();
                    var language = $('#language').val();
                    var target_amount = $('#target_amount').val();
                    var widget_size = $('#widget_size').val();
                    var theme = $('#theme').val();

                    var explodedSize = widget_size.split('_');

                    var width = explodedSize[0];
                    var height = explodedSize[1];

                    var source = 'BTC';
                    var target = 'ETH';

                    try {
                        var default_currency_pair = $('#default_currency_pair').val();
                        source = default_currency_pair.split('_')[0];
                        target = default_currency_pair.split('_')[1];
                     } catch(e) {
                     }

                    $('#preview').html('<aside class="widget"><iframe src="https://swap.innocoin.com/#/?language=' + language + '&partner_email=' + partner_email + '&partner_address=' + partner_address + '&partner_margin=' + partner_margin + '&source=' + source + '&target=' + target + '&target_amount=' + target_amount + '&show_info=true&theme=' + theme + '&rounded_corners=' + rounded_corners + '&width=' + width + '&height=' + height + '&border=false&iframe=true&partner_name=' + location.hostname + '" border="0" scrolling="no" class="innocoin w' + width + ' h' + height + '"></iframe></aside>');

                });
                $('.checkChange').change();
            });
        </script>
        <style>
            .left-block {
                display: inline-block
            }

            .right-block {
                display: inline-block;
                vertical-align: top;
                background-color:#eee;
                padding:1em 0;
                width:600px;
            }
        </style>

        <div class="left-block">
            <form method="post" action="">
                <div class="wrap" style="width: 400px;">

                    <p></p>

                    <p><label for="partner_address">Payout Bitcoin Address</label><br><input id="partner_address"
                                                                                             class="widefat checkChange"
                                                                                             type="text"
                                                                                             name="partner_address"
                                                                                             value="<?= esc_attr($partnerAddress) ?>">
                    </p>

                    <p><label for="partner_email">Your Email Address</label><br><input id="partner_email"
                                                                                       class="widefat checkChange"
                                                                                       type="text" name="partner_email"
                                                                                       value="<?= esc_attr($partnerEmail) ?>">
                    </p>

                    <p>
                        <label for="partner_margin">Your Profit Margin (%)</label><br>
                        <select name="partner_margin" id="partner_margin" class="checkChange">
                            <option value="0.25">0.25</option>
                            <option value="0.50" selected>0.5</option>
                            <option value="0.75">0.75</option>
                            <option value="1">1</option>
                            <option value="1.25">1.25</option>
                        </select>
                    </p>
                    <p>
                        <label for="language">Widget Language</label><br>
                        <select name="language" id="language" class="checkChange">
                            <option value="english" selected="">English</option>
                            <option value="chinese">中文 (Chinese)</option>
                            <option value="nederlands">Nederlands</option>
                            <option value="francais">Français</option>
                            <option value="italiano">Italiano</option>
                            <option value="portugues">Português</option>
                            <option value="deutsch">Deutsch</option>
                            <option value="espagnol">Español</option>
                            <option value="korean">한국어 (Korean)</option>
                            <option value="japanese">日本語 (Japanese)</option>
                            <option value="vietnamese">Tiếng Việt</option>
                            <option value="polish">Polskie</option>
                            <option value="russian">Pусский</option>
                        </select>
                    </p>
                    <p>
                        <label for="default_currency_pair">Default Currency Pair</label><br>
                        <select name="default_currency_pair" id="default_currency_pair" class="checkChange">
                            <option value="BTC_ETH">BTC_ETH</option>
                            <option value="ETH_BTC">ETH_BTC</option>
                            <option value="BTC_ZEC">BTC_ZEC</option>
                            <option value="BTC_DASH">BTC_DASH</option>
                            <option value="BTC_SJCX">BTC_SJCX</option>
                            <option value="BTC_XCP">BTC_XCP</option>
                            <option value="BTC_XMR">BTC_XMR</option>
                            <option value="BTC_ETC">BTC_ETC</option>
                        </select>
                    </p>
                    <p>
                        <label for="target_amount">Prefilled Amount</label><br>
                        <select name="target_amount" id="target_amount" class="checkChange">
                            <option value="0">0</option>
                            <option value="2.5" selected>2.5</option>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                        </select>
                    </p>
                    <p>
                        <label for="widget_size">Widget Size</label><br>
                        <select name="widget_size" id="widget_size" class="checkChange">
                            <option value="250_250">250_250</option>
                            <option value="300_300">300_300</option>
                            <option value="300_440">300_440</option>
                            <option value="350_440">350_440</option>
                            <option value="400_467">400_467</option>
                            <option value="500_470" selected>500_470</option>
                            <option value="fluid_440">fluid_440</option>
                            <option value="fluid_470">fluid_440</option>
                            <option value="fluid_500">fluid_500</option>
                        </select>
                    </p>
                    <p>
                        <label for="theme">Theme</label><br>
                        <select name="theme" id="theme" class="checkChange">
                            <option value="none">none</option>
                            <option value="white">white</option>
                            <option value="45degree_fabric">45degree_fabric</option>
                            <option value="exclusive_paper">exclusive_paper</option>
                            <option value="grunge_wall" selected="">grunge_wall</option>
                            <option value="white_carbon">white_carbon</option>
                            <option value="little_pluses">little_pluses</option>
                            <option value="old_mathematics">old_mathematics</option>
                            <option value="random_grey_variations">random_grey_variations</option>
                            <option value="black_linen">black_linen</option>
                            <option value="bright_squares">bright_squares</option>
                            <option value="brushed_alu">brushed_alu</option>
                            <option value="carbon_fibre">carbon_fibre</option>
                            <option value="concrete_wall">concrete_wall</option>
                            <option value="wood_1">wood_1</option>
                            <option value="60degree_gray">60degree_gray</option>
                            <option value="white_sand">white_sand</option>
                            <option value="subtle_freckles">subtle_freckles</option>
                            <option value="paper1">paper_1</option>
                            <option value="orange">orange</option>
                            <option value="darkblue">darkblue</option>
                        </select>
                    </p>
                    <p><label for="rounded_corners">Rounded Corners</label><br><label><input type="radio"
                                                                                             name="rounded_corners"
                                                                                             value="true"
                                                                                             class="checkChange"
                                                                                             checked> Yes </label>
                        <label><input type="radio" name="rounded_corners" class="checkChange" value="false"> No </label>
                    </p>

                    <p></p>
                    <?php wp_nonce_field('add', 'addSec'); ?>
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                             value="Save Changes"></p></div>
            </form>
        </div>
        <div class="right-block">
            <div id="preview"></div>
        </div>
        <?

    }
}
function innocoin_edit()
{
    global $wpdb;
    $widgetId = (int)($_GET['id']);
    if (current_user_can('manage_options') && check_admin_referer( 'edit_'.$widgetId)) {

        if (isset($_POST) && !empty($_POST) && check_admin_referer('edit', 'editSec')) {

            if ($_POST['widget_size']) {
                $widgetSize = explode('_', sanitize_text_field($_POST['widget_size']));
                $width = $widgetSize[0];
                $height = $widgetSize[1];
            } else {
                $width = 250;
                $height = 250;
            }

            $widgetName = $width . 'x' . $height . '-' . sanitize_text_field($_POST['theme']) . '-' . date('YmdHi');
            if ($wpdb->update(
                $wpdb->prefix . 'innocoin',
                array(
                    'name' => sanitize_text_field($widgetName),
                    'language' => sanitize_text_field($_POST['language']),
                    'partner_email' => sanitize_text_field($_POST['partner_email']),
                    'partner_address' => sanitize_text_field($_POST['partner_address']),
                    'partner_margin' => sanitize_text_field($_POST['partner_margin']),
                    'source' => sanitize_text_field($_POST['default_currency_pair']),
                    'target_amount' => sanitize_text_field($_POST['target_amount']),
                    'theme' => sanitize_text_field($_POST['theme']),
                    'rounded_corners' => sanitize_text_field($_POST['rounded_corners']),
                    'width' => $width,
                    'height' => $height,
                ),
                array('ID' => $widgetId),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                ),
                array('%s')
            )
            ) {
                wp_redirect(admin_url('admin.php?page=innocoin'));
            }
        }

        echo '<div class="wrap"><h2>Innocoin Edit Widget Profile</h2>';
        wp_enqueue_script('jquery');

        global $wpdb;

        $tableName = $wpdb->prefix . 'innocoin';
        $getInnocoin = $wpdb->get_results("SELECT * FROM {$tableName} WHERE `id` = '$widgetId'");

        foreach ($getInnocoin as $innocoin) {
            $partnerAddress = $innocoin->partner_address;
            $partnerEmail = $innocoin->partner_email;
            $partnerMargin = $innocoin->partner_margin;
            $width = $innocoin->width;
            $source = $innocoin->source;
            $height = $innocoin->height;
            $language = $innocoin->language;
            $targetAmount = $innocoin->target_amount;
            $theme = $innocoin->theme;
            $roundedCorners = $innocoin->rounded_corners;
        }
        $widgetSize = $width . '_' . $height;
        wp_enqueue_style('innocoin');
        ?>
        <script>
            jQuery(document).ready(function ($) {

                var rounded_corners = true;
                $("input:radio[name=rounded_corners]").click(function () {
                    rounded_corners = $(this).val();
                });

                $('#partner_address').keypress(function () {
                    $(this).change();
                });
                $('#partner_email').keypress(function () {
                    $(this).change();
                });

                $('.checkChange').change(function () {
                    var partner_address = $('#partner_address').val();
                    var partner_email = $('#partner_email').val();
                    var partner_margin = $('#partner_margin').val();
                    var language = $('#language').val();
                    var target_amount = $('#target_amount').val();
                    var widget_size = $('#widget_size').val();
                    var theme = $('#theme').val();

                    var explodedSize = widget_size.split('_');

                    var width = explodedSize[0];
                    var height = explodedSize[1];

                    var source = 'BTC';
                    var target = 'ETH';

                    try {
                        var default_currency_pair = $('#default_currency_pair').val();
                        source = default_currency_pair.split('_')[0];
                        target = default_currency_pair.split('_')[1];
                     } catch(e) {
                     }


                    $('#preview').html('<aside class="widget"><iframe src="https://swap.innocoin.com/#/?language=' + language + '&partner_email=' + partner_email + '&partner_address=' + partner_address + '&partner_margin=' + partner_margin + '&source=' + source + '&target=' + target + '&target_amount=' + target_amount + '&show_info=true&theme=' + theme + '&rounded_corners=' + rounded_corners + '&width=' + width + '&height=' + height + '&border=false&iframe=true&partner_name=' + location.hostname + '" border="0" scrolling="no" class="innocoin w' + width + ' h' + height + '"></iframe></aside>');
                });
                $('.checkChange').change();
            });
        </script>
        <style>
            .left-block {
                display: inline-block
            }

            .right-block {
                display: inline-block;
                vertical-align: top;
                background-color:#eee;
                padding:1em 0;
                width:600px;
            }
        </style>

        <div class="left-block">
            <form method="post" action="">
                <div class="wrap" style="width: 400px;">

                    <p></p>

                    <p><label for="partner_address">Payout Bitcoin Address</label><br><input id="partner_address"
                                                                                             class="widefat checkChange"
                                                                                             type="text"
                                                                                             name="partner_address"
                                                                                             value="<?= esc_attr($partnerAddress) ?>">
                    </p>

                    <p><label for="partner_email">Your Email Address</label><br><input id="partner_email"
                                                                                       class="widefat checkChange"
                                                                                       type="text" name="partner_email"
                                                                                       value="<?= esc_attr($partnerEmail) ?>">
                    </p>

                    <p>
                        <label for="partner_margin">Your Profit Margin (%)</label><br>
                        <select name="partner_margin" id="partner_margin" class="checkChange">
                            <option value="0.25" <? if ($partnerMargin == '0.25') echo "selected"; ?>>0.25</option>
                            <option value="0.50" <? if ($partnerMargin == '0.50') echo "selected"; ?>>0.5</option>
                            <option value="0.75" <? if ($partnerMargin == '0.75') echo "selected"; ?>>0.75</option>
                            <option value="1" <? if ($partnerMargin == '1') echo "selected"; ?>>1</option>
                            <option value="1.25" <? if ($partnerMargin == '1.25') echo "selected"; ?>>1.25</option>
                        </select>
                    </p>
                    <p>
                        <label for="language">Widget Language</label><br>
                        <select name="language" id="language" class="checkChange">
                            <option value="english" <? if ($language == 'english') echo "selected"; ?>>English</option>
                            <option value="chinese" <? if ($language == 'chinese') echo "selected"; ?>>中文 (Chinese)</option>
                            <option value="nederlands" <? if ($language == 'nederlands') echo "selected"; ?>>Nederlands</option>
                            <option value="italiano" <? if ($language == 'italiano') echo "selected"; ?>>Italiano</option>
                            <option value="francais" <? if ($language == 'francais') echo "selected"; ?>>Français</option>
                            <option value="deutsch" <? if ($language == 'deutsch') echo "selected"; ?>>Deutsch</option>
                            <option value="espagnol" <? if ($language == 'espagnol') echo "selected"; ?>>Español</option>
                            <option value="portugues" <? if ($language == 'portugues') echo "selected"; ?>>Português</option>
                            <option value="korean" <? if ($language == 'korean') echo "selected"; ?>>한국어 (Korean)</option>
                            <option value="japanese" <? if ($language == 'japanese') echo "selected"; ?>>日本語 (Japanese)</option>
                            <option value="vietnamese" <? if ($language == 'vietnamese') echo "selected"; ?>>Tiếng Việt</option>
                            <option value="polish" <? if ($language == 'polish') echo "selected"; ?>>Polskie</option>
                            <option value="russian" <? if ($language == 'russian') echo "selected"; ?>>Pусский</option>
                        </select>
                    </p>
                    <p>
                        <label for="default_currency_pair">Default Currency Pair</label><br>
                        <select name="default_currency_pair" id="default_currency_pair" class="checkChange">
                            <option value="BTC_ETH" <? if ($source == 'BTC_ETH' || $source == 'BTC') echo "selected"; ?>>BTC_ETH</option>
                            <option value="ETH_BTC" <? if ($source == 'ETH_BTC') echo "selected"; ?>>ETH_BTC</option>
                            <option value="BTC_ZEC" <? if ($source == 'BTC_ZEC') echo "selected"; ?>>BTC_ZEC</option>
                            <option value="BTC_DASH" <? if ($source == 'BTC_DASH') echo "selected"; ?>>BTC_DASH</option>
                            <option value="BTC_SJCX" <? if ($source == 'BTC_SJCX') echo "selected"; ?>>BTC_SJCX</option>
                            <option value="BTC_XCP" <? if ($source == 'BTC_XCP') echo "selected"; ?>>BTC_XCP</option>
                            <option value="BTC_XMR" <? if ($source == 'BTC_XMR') echo "selected"; ?>>BTC_XMR</option>
                            <option value="BTC_ETC" <? if ($source == 'BTC_ETC') echo "selected"; ?>>BTC_ETC</option>
                        </select>
                    </p>
                    <p>
                        <label for="target_amount">Prefilled Amount</label><br>
                        <select name="target_amount" id="target_amount" class="checkChange">
                            <option value="0" <? if ($targetAmount == '0') echo "selected"; ?>>0</option>
                            <option value="2.5" <? if ($targetAmount == '2.5') echo "selected"; ?>>2.5</option>
                            <option value="5" <? if ($targetAmount == '5') echo "selected"; ?>>5</option>
                            <option value="10" <? if ($targetAmount == '10') echo "selected"; ?>>10</option>
                            <option value="15" <? if ($targetAmount == '15') echo "selected"; ?>>15</option>
                        </select>
                    </p>
                    <p>
                        <label for="widget_size">Widget Size</label><br>
                        <select name="widget_size" id="widget_size" class="checkChange">
                            <option value="250_250" <? if ($widgetSize == '250_250') echo "selected"; ?>>250_250</option>
                            <option value="300_300" <? if ($widgetSize == '300_300') echo "selected"; ?>>300_300</option>
                            <option value="300_440" <? if ($widgetSize == '300_440') echo "selected"; ?>>300_440</option>
                            <option value="350_440" <? if ($widgetSize == '350_440') echo "selected"; ?>>350_440</option>
                            <option value="400_467" <? if ($widgetSize == '400_467') echo "selected"; ?>>400_467</option>
                            <option value="500_470" <? if ($widgetSize == '500_470') echo "selected"; ?>>500_470</option>
                            <option value="fluid_440" <? if ($widgetSize == 'fluid_440') echo "selected"; ?>>fluid_440</option>
                            <option value="fluid_470" <? if ($widgetSize == 'fluid_470') echo "selected"; ?>>fluid_440</option>
                            <option value="fluid_500" <? if ($widgetSize == 'fluid_500') echo "selected"; ?>>fluid_500</option>
                        </select>
                    </p>
                    <p>
                        <label for="theme">Theme</label><br>
                        <select name="theme" id="theme" class="checkChange">
                            <option value="none" <? if ($theme == 'none') echo "selected" ?>>none</option>
                            <option value="white" <? if ($theme == 'white') echo "selected" ?>>white</option>
                            <option value="45degree_fabric" <? if ($theme == '45degree_fabric') echo "selected" ?>>
                                45degree_fabric
                            </option>
                            <option value="exclusive_paper" <? if ($theme == 'exclusive_paper') echo "selected" ?>>
                                exclusive_paper
                            </option>
                            <option value="grunge_wall" <? if ($theme == 'grunge_wall') echo "selected" ?>>grunge_wall
                            </option>
                            <option value="white_carbon" <? if ($theme == 'white_carbon') echo "selected" ?>>
                                white_carbon
                            </option>
                            <option value="little_pluses" <? if ($theme == 'little_pluses') echo "selected" ?>>
                                little_pluses
                            </option>
                            <option value="old_mathematics" <? if ($theme == 'old_mathematics') echo "selected" ?>>
                                old_mathematics
                            </option>
                            <option
                                value="random_grey_variations" <? if ($theme == 'random_grey_variations') echo "selected" ?>>
                                random_grey_variations
                            </option>
                            <option value="black_linen" <? if ($theme == 'black_linen') echo "selected" ?>>black_linen
                            </option>
                            <option value="bright_squares" <? if ($theme == 'bright_squares') echo "selected" ?>>
                                bright_squares
                            </option>
                            <option value="brushed_alu" <? if ($theme == 'brushed_alu') echo "selected" ?>>brushed_alu
                            </option>
                            <option value="carbon_fibre" <? if ($theme == 'carbon_fibre') echo "selected" ?>>
                                carbon_fibre
                            </option>
                            <option value="concrete_wall" <? if ($theme == 'concrete_wall') echo "selected" ?>>
                                concrete_wall
                            </option>
                            <option value="wood_1" <? if ($theme == 'wood_1') echo "selected" ?>>wood_1</option>
                            <option value="60degree_gray" <? if ($theme == '60degree_gray') echo "selected" ?>>
                                60degree_gray
                            </option>
                            <option value="white_sand" <? if ($theme == 'white_sand') echo "selected" ?>>white_sand
                            </option>
                            <option value="subtle_freckles" <? if ($theme == 'subtle_freckles') echo "selected" ?>>
                                subtle_freckles
                            </option>
                            <option value="paper1" <? if ($theme == 'paper1') echo "selected" ?>>paper_1</option>
                            <option value="orange" <? if ($theme == 'orange') echo "selected" ?>>orange</option>
                            <option value="darkblue" <? if ($theme == 'darkblue') echo "selected" ?>>darkblue</option>
                        </select>
                    </p>
                    <p><label for="rounded_corners">Rounded Corners</label><br><label><input type="radio"
                                                                                             name="rounded_corners"
                                                                                             value="true"
                                                                                             class="checkChange" <? if ($roundedCorners == 'true') echo 'checked' ?>>
                            Yes </label>
                        <label><input type="radio" name="rounded_corners" class="checkChange"
                                      value="false" <? if ($roundedCorners == 'false') echo 'checked' ?>> No </label>
                    </p>

                    <p></p>
                    <?php wp_nonce_field('edit', 'editSec'); ?>
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                             value="Save Changes"></p></div>
            </form>
        </div>
        <div class="right-block">
            <div id="preview"></div>
        </div>
        <?


    }
}