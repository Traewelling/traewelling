<?php

return [
    /*
     * Set the table to be used for monitoring data.
     */
    'table'                           => 'queue_monitor',
    'connection'                      => null,

    /*
     * Set the model used for monitoring.
     * If using a custom model, be sure to implement the
     *   romanzipp\QueueMonitor\Models\Contracts\MonitorContract
     * interface or extend the base model.
     */
    'model'                           => \Traewelling\QueueMonitor\Models\Monitor::class,

    /*
     * Specify the max character length to use for storing exception backtraces.
     */
    'db_max_length_exception'         => 4294967295,
    'db_max_length_exception_message' => 65535,

    /*
     * Set the retention time of the monitoring logs. To create accurate aggregations, this number
     * should at least be double the `metrics_time_frame`.
     */
    'delete_old_items_after_days'     => 28,

    /*
     * The optional UI settings.
     */
    'ui'                              => [
        /*
         * Set the monitored jobs count to be displayed per page.
         */
        'per_page'           => 35,

        /*
         *  Show custom data stored on model
         */
        'show_custom_data'   => true,

        /**
         * Allow the deletion of single monitor items.
         */
        'allow_deletion'     => true,

        /**
         * Allow purging all monitor entries.
         */
        'allow_purge'        => true,

        /**
         * Show the aggregation metrics in the dashboard.
         */
        'show_metrics'       => true,

        /**
         * Time frame used to calculate metrics values (in days).
         */
        'metrics_time_frame' => 14,
    ],
];
