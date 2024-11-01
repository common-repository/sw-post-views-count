<?php
/*
 * Add metabox 
 * using this metabox admin can decide on which post he wants to show page views count.
 * check if metabox funtions not exist
 */

if (!function_exists("swpvc_add_meta_box") &&
        !function_exists("swpvc_meta_box_callback") &&
        !function_exists("swpvc_save_meta_box")) {

    /*
     * add/create metabox function 
     */

    function swpvc_add_meta_box() {
        add_meta_box("swpvc-metabox", "Views count option", "swpvc_meta_box_callback", "post", "side", "high", null);
    }

    /*
     * callback function to show content / view count checkbox setting in metabox 
     */

    function swpvc_meta_box_callback($object) {
        ?>
        <div class="swpvc_input_group">
            <label class="swpvc_switch">
                <input type="checkbox" name="swpvc-checkbox" class="swpvc-checkbox"
                <?php echo (get_post_meta($object->ID, "_swpvc_views_count_status", true) != "") ? 'checked="checked"' : ''; ?>
                       > 
                <span class="swpvc_slider swpvc_round"></span>
            </label>
            <span class="input_label">Enable views</span>
        </div>
        <div class="clear-fix"></div>
        <div class="swpvc_input_group">
            <label class="swpvc_view_labels">
                <?php
                $today_views = swpcv_get_daily_view_count($object->ID, $object->post_type, date('Y-m-d'));
                echo $today_views['daily_count_format'];
                ?>
            </label>
        </div>
        <div class="swpvc_input_group">
            <label class="swpvc_view_labels">
                <?php
                $total_views = swpcv_get_total_view_count($object->ID, $object->post_type, date('Y-m-d'));
                echo $total_views['total_count_format'];
                ?>
            </label>
        </div>
        <?php
    }

    /*
     * save metabox value function
     */

    function swpvc_save_meta_box($post_id) {
        $swpvc_checkbox = "";

        if (isset($_POST["swpvc-checkbox"])) {
            $swpvc_checkbox = $_POST["swpvc-checkbox"];
        }
        update_post_meta($post_id, "_swpvc_views_count_status", $swpvc_checkbox);
    }

    add_action("add_meta_boxes", "swpvc_add_meta_box");
    add_action("save_post", "swpvc_save_meta_box");
}

/*
 * append views count html to post
 */

if (!function_exists("swpcv_append_views_count_html")) {

    add_filter('the_content', 'swpcv_append_views_count_html');

    function swpcv_append_views_count_html($content) {
        $post_id = get_the_ID();
        $post_type = get_post_type();
        $view_count_status = get_post_meta($post_id, '_swpvc_views_count_status', true);
        if ($view_count_status != "") {
            $daily_count = swpcv_get_daily_view_count($post_id, $post_type, date('Y-m-d'));
            $total_count = swpcv_get_total_view_count($post_id, $post_type, date('Y-m-d'));
            switch ($post_type) {
                case 'post':
                    $content .= $daily_count['daily_count_format'] . ", " . $total_count['total_count_format'];
                    break;
                default:
                    $content = '';
                    break;
            }
            return $content;
        } else {
            return $content;
        }
    }

}

/*
 * add daily view count of current post by this function on page
 */

if (!function_exists("swpcv_add_daily_view_count")) {

    function swpcv_add_daily_view_count() {
        if (is_single() && get_post_type() == 'post') {
            $check_status = get_post_meta(get_the_ID(), '_swpvc_views_count_status', true);
            if ($check_status == 'on') {
                global $wpdb;
                $table_name = $wpdb->prefix . 'swpcv_daily_views';
                $date = date('Y-m-d');
                $result = $wpdb->get_row(
                        "SELECT * FROM $table_name 
 					WHERE view_date = '" . $date . "' 
 					AND post_id=" . get_the_ID());
                if (empty($result)) {
                    $data = array(
                        'post_id' => get_the_ID(),
                        'post_type' => get_post_type(),
                        'view_date' => $date,
                        'view_count' => 1
                    );
                    $wpdb->insert($table_name, $data);
                } else {
                    $cnt = $result->view_count + 1;
                    $data = array(
                        'view_count' => $cnt,
                    );
                    $where = array(
                        'id' => $result->id,
                    );
                    $wpdb->update($table_name, $data, $where);
                }
            }
        }
    }

    add_action('wp_head', 'swpcv_add_daily_view_count');
}

/*
 * get daily view count of current post by this function
 */

if (!function_exists("swpcv_get_daily_view_count")) {

    function swpcv_get_daily_view_count($post_id, $post_type, $date) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'swpcv_daily_views';
        $result = $wpdb->get_row(
                "SELECT * FROM $table_name 
			WHERE view_date = '" . $date . "' 
			AND post_id=" . get_the_ID() . " 
			AND post_type='" . $post_type . "'");
        if (!empty($result)) {
            $return_data['daily_count'] = $result->view_count;
            $return_data['daily_count_format'] = $result->view_count . " Today views";
        } else {
            $return_data['daily_count'] = 0;
            $return_data['daily_count_format'] = "No views today";
        }
        return $return_data;
    }

}

/*
 * add total view count of current post by this function on page
 */

if (!function_exists("swpcv_get_total_view_count")) {

    function swpcv_get_total_view_count($post_id, $post_type = "", $date = "") {
        global $wpdb;
        $table_name = $wpdb->prefix . 'swpcv_daily_views';
        $result = $wpdb->get_results(
                "SELECT * FROM $table_name 
 			WHERE post_id=" . get_the_ID());
        $total_count = 0;
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $total_count += $value->view_count;
            }
            $return_data['total_count'] = $total_count;
            $return_data['total_count_format'] = $total_count . " Total views";
        } else {
            $return_data['total_count'] = 0;
            $return_data['total_count_format'] = "No total views";
        }
        return $return_data;
    }

}

/*
 * show post views in posts list table.
 * Add the custom column to the post table.
 */

if (!function_exists("swpvc_set_views_column_in_postlist") &&
        !function_exists("swpvc_show_views_in_postlist")) {

    add_filter('manage_post_posts_columns', 'swpvc_set_views_column_in_postlist');

    function swpvc_set_views_column_in_postlist($columns) {
        $columns['views'] = __('Views', 'swpvc');
        return $columns;
    }

    /*
     * show views to the views column for the post
     */

    add_action('manage_post_posts_custom_column', 'swpvc_show_views_in_postlist', 10, 2);

    function swpvc_show_views_in_postlist($column, $post_id) {
        switch ($column) {
            case 'views' :
                $daily_count = swpcv_get_daily_view_count($post_id, 'post', date('Y-m-d'));
                $total_count = swpcv_get_total_view_count($post_id, 'post', date('Y-m-d'));
                echo $content .= $daily_count['daily_count_format'] . ", " . $total_count['total_count_format'];
                break;
        }
    }

}