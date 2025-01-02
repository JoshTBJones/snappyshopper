<?php

if (!function_exists('str_putcsv')) {
    function str_putcsv($fields)
    {
        $fh = fopen('php://temp', 'rw');
        fputcsv($fh, $fields);
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return trim($csv);
    }
}
