<?php

final class KomtetKassa_Report {

    const REPORT_TABLE_NAME = 'komtetkassa_reports';

    public function create($order_id, $request_check_data, $response_data, $error="")
    {
        global $wpdb;

        $wpdb->insert( 
            $wpdb->prefix . self::REPORT_TABLE_NAME,
            array(
                'order_id' => $order_id,
                'request_data' => json_encode($request_check_data), 
                'response_data' => $response_data != null ? json_encode($response_data) : null,
                'error' => empty($error) ? null : $error 
            ), 
            array('%d', '%s', '%s')
        );
    }

    public function update($order_id, $state, $report_data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::REPORT_TABLE_NAME;
        
        $report = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE `order_id` = %d LIMIT 1;", $order_id ) );
        
        if ($report === null) {
            status_header(422);
            header('Content-Type: text/plain');
            echo "Order by external_id {$order_id} not found\n";
            exit;
        }

        $wpdb->update($table_name,
            array( 
                'status' => $state, 
                'report_data' => json_encode($report_data),
                'error' => $state == "error" ? $report_data['error_description'] : null
            ),
            array('order_id' => $order_id),
            array('%s', '%s', '%s'),
            array('%d')
        );
    }
}