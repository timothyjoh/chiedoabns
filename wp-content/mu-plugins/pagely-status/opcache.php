<?php
if (isset($_POST['PAGELY_RESET_OPCACHE']) && $_SERVER['REMOTE_ADDR'] == '127.0.0.1' && function_exists('opcache_reset'))
{
    $file = __DIR__.'/../../../../info.json';
    $info = json_decode(file_get_contents($file));
    if ($_POST['PAGELY_RESET_OPCACHE'] == $info->apiKey)
    {
        opcache_reset();
        clearstatcache(true);
        echo "RESET\n";
    }
}

