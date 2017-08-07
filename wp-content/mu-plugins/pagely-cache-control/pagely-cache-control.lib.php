<?php
/*
 * There are a couple of approaches to a system like this
 *
 * You can run a bunch of regex rules and if you match don't cache
 * You can compare just base paths
 *
 * We are comparing base paths right now because its quick and has fixed cost (finding the base
 * directory)
 */
class PagelyCacheControl
{
    protected $config;
    protected $options;

    public static $instance = null;

    public static function instance()
    {
        if (empty(static::$instance))
            static::$instance = new static();
        return static::$instance;
    }

    // make this a singleton because this setup is expensive, sigh
    public function __construct()
    {
        $this->options = new PagelyGlobalOptions();
        $this->config = $this->options->get('pagely-cache-control');
        if (!is_object($this->config))
        {
            $this->options->delete('pagely-cache-control');
            $this->options->add('pagely-cache-control', $this->configDefaults());
            $this->config = $this->options->get('pagely-cache-control');
        }
        $this->config->dev_mode = !empty($_COOKIE['pagely_dev_mode']);

        //echo "<pre>";
        //var_dump($this->config);
        //echo "</pre>";
    }

    public function configDefaults()
    {
        $config = new stdClass;
        $config->all_caching_disabled = false;

        return $config;
    }

    public function config()
    {
        return $this->config;
    }

    public function updateConfig($POST)
    {
        check_admin_referer( 'update_pagely_cachecontrol' );

        foreach(array_keys(get_object_vars($this->config)) as $key)
        {
            if (isset($POST[$key]))
                $this->config->$key = $POST[$key];
        }

        // dev mode actually depends on the cookie, so it has custom support here
        if (!empty($POST['dev_mode']))
        {
            setcookie('pagely_dev_mode', 1, 0, '/');
        }
        else
        {
            setcookie('pagely_dev_mode', 1, 1, '/');
        }

        $this->options->update('pagely-cache-control', $this->config);
    }

    public function isNoCachePath($path)
    {
        $base = $path;
        if (preg_match('@/([^/]+)/@', $path, $match))
            $base = $match[1];

        if (isset($this->noCachePaths[$base]))
            return true;

        return false;
    }

    public function sendNoCacheHeaders($wp)
    {
        //if ($cc->isNoCachePath($wp->request))
        if ($this->config->all_caching_disabled)
        {
            header('Cache-Control: private, max-age=0, no-cache');
            header('X-Pagely-Cache: all_caching_disabled');
        }
    }
}
