<?php

return array(
    /** set your paypal credential **/
    'client_id' =>'AbGUpBItXgRDaZNsyrMSCLD7v90ue5vqOVehUWu2MmPqaKimjcWPmGl4z_0I73vFcq5VplSgZbGOTt2Q',
    'secret' => 'EKS8drNqkVuy3hiMrOdI-nk9YTiHvpq1IEdJHPuoFZ4tGfQ0jOyUbIZjorL5Ug9KkEDb-inSSLjcqAFC',
    /**
     * SDK configuration
     */
    'settings' => array(
        /**
         * Available option 'sandbox' or 'live'
         */
        'mode' => 'sandbox',
        /**
         * Specify the max request time in seconds
         */
        'http.ConnectionTimeOut' => 1000,
        /**
         * Whether want to log to a file
         */
        'log.LogEnabled' => true,
        /**
         * Specify the file that want to write on
         */
        'log.FileName' => storage_path() . '/logs/paypal.log',
        /**
         * Available option 'FINE', 'INFO', 'WARN' or 'ERROR'
         *
         * Logging is most verbose in the 'FINE' level and decreases as you
         * proceed towards ERROR
         */
        'log.LogLevel' => 'FINE'
    ),
);