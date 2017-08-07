Public API
---

`pagely_cache_purge_path` filter can be used to change the paths that will be purged.
 - To disable purging `return null;` from the filter (useful during batch imports, etc)
 - The `pagely-cache-purge.php:purgePath()` function is the main hook which checks & stops all subsequent PURGE requests.

Example code: (for customer to put in their automated import function.)
```php
<?php
// disable cache purge
add_filter('pagely_cache_purge_path', function($url) { return null; });
?>
```
