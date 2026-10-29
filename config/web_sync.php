<?php
// config/web_sync.php

return [
    /*
    |--------------------------------------------------------------------------
    | Definisi "Completed"
    |--------------------------------------------------------------------------
    | Hanya order dengan shipping_status = 'delivered' yang dianggap completed.
    */
    'completed_shipping_status' => ['delivered'],

    // Default: ambil 90 hari terakhir
    'default_since_days' => 90,
];