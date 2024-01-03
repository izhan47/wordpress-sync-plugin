<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Wp_Blog_Exporter
 * @subpackage Wp_Blog_Exporter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Blog_Exporter
 * @subpackage Wp_Blog_Exporter/admin
 * @author     Izhan <Izhan47@gmail.com>
 */

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPBE_API_LOG_LIST_TABLE extends WP_List_Table {

    // Here we will add our code

    // define $table_data property
    private $table_data;

    // Get table data
    private function get_table_data( $search = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'wpbe_api_log';

        if ( !empty($search) ) {
            return $wpdb->get_results(
                "SELECT * from {$table} WHERE name Like '%{$search}%' OR description Like '%{$search}%' OR status Like '%{$search}%'",
                ARRAY_A
            );
        } else {
            return $wpdb->get_results(
                "SELECT * from {$table} ORDER BY id DESC",
                ARRAY_A
            );
        }
    }

    // Define table columns
    function get_columns()
    {
        $columns = array(
                'cb'            => '<input type="checkbox" />',
                'name'          => __('URL', 'wp-blog-exporter'),
                'description'         => __('Description', 'wp-blog-exporter'),
                'status'   => __('Status', 'wp-blog-exporter'),
        );
        return $columns;
    }

    // Bind table with columns, data and all
    function prepare_items()
    {
        //data
        if ( isset($_POST['s']) ) {
            $this->table_data = $this->get_table_data($_POST['s']);
        } else {
            $this->table_data = $this->get_table_data();
        }

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $primary  = 'name';
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);

        /* pagination */
        $per_page = $this->get_items_per_page('elements_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
                'total_items' => $total_items, // total number of items
                'per_page'    => $per_page, // items to show on a page
                'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
        ));
        
        $this->items = $this->table_data;
        $this->process_bulk_action();
    }

    // set value for each column
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'name':
            case 'description':
            case 'status':
            default:
                return $item[$column_name];
        }
    }

    // Add a checkbox in the first column
    function column_cb($item)
    {
        return sprintf(
                '<input type="checkbox" name="element[]" value="%s" />',
                $item['id']
        );
    }

    // To show bulk action dropdown
    function get_bulk_actions()
    {
            $actions = array(
                    'delete_all'    => __('Delete', 'wp-blog-exporter')
            );
            return $actions;
    }
    public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $action = $this->current_action();

        switch ( $action ) {

            case 'delete_all':
                global $wpdb;
                $table_name = $wpdb->prefix . 'wpbe_api_log';
                $ids = isset($_REQUEST['element']) ? $_REQUEST['element'] : array();
                    if (is_array($ids)) $ids = implode(',', $ids);
                    if (!empty($ids)) {
                        $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
                }

                wp_die( 'You have deleted this succesfully' );

            break;

            default:
                // do nothing or something else
                return;
                break;
        }

        return;
    }

} 

return new WPBE_API_LOG_LIST_TABLE();
