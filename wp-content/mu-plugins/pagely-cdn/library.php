<?php
require_once __DIR__.'/jmespath.php';
/**
 * Hold the configuration for rewriting
 */
class Pagely_CDNConfig
{
	public $inited = 2;
	public $enabled = 1;
	public $cdn_url;
	public $https_cdn_url;
	public $zone_id;
	public $rootrelative = 0;
	public $include_dirs = array('wp-includes', 'wp-content');
	public $exclude = array('/wp-includes/js/tinymce');
	public $exclude_ext = array('php');
	public $exclude_override = array('timthumb.php');
	public $blog_url_aliases = array();
    public $api_url_paths = [
        'featured_image.attachment_meta.sizes.*.url[]',
        '[].featured_image.attachment_meta.sizes.*.url[]',
        ];
    public $api_html_paths = [
        'content', // individual page/post
        '[].content', // list of pages/post
        'featured_image.content', // individual page/post
        '[].featured_image.content', // list of pages/post
        ];

	protected static $instance;
	protected $ssl = false;
	protected $wpoptions = false;

	protected $options = array(
		'inited',
		'enabled',
		'cdn_url',
		'https_cdn_url',
		'zone_id',
		'rootrelative',
		'include_dirs',
		'exclude',
		'exclude_ext',
		'exclude_override',
		'blog_url_aliases',
        'api_url_paths',
        'api_html_paths',
	);

	protected $userMap = array(
		'include_dirs' => 'listToArray',
		'exclude' => 'listToArray',
		'exclude_ext' => 'listToArray',
		'exclude_override' => 'listToArray',
		'blog_url_aliases' => 'listToArray',
		'api_url_paths' => 'listToArray',
		'api_html_paths' => 'listToArray',
	);
	protected $displayMap = array(
		'include_dirs' => 'arrayToList',
		'exclude' => 'arrayToList',
		'exclude_ext' => 'arrayToList',
		'exclude_override' => 'arrayToList',
		'blog_url_aliases' => 'arrayToList',
		'api_url_paths' => 'arrayToList',
		'api_html_paths' => 'arrayToList',
	);
	protected $loadMap = array(
		'include_dirs' => 'listToArray',
		'exclude' => 'listToArray',
		'exclude_ext' => 'listToArray',
		'exclude_override' => 'listToArray',
		'blog_url_aliases' => 'listToArray',
		'api_url_paths' => 'listToArray',
		'api_html_paths' => 'listToArray',
	);
	protected $saveMap = array(
		'include_dirs' => 'arrayToList',
		'exclude' => 'arrayToList',
		'exclude_ext' => 'arrayToList',
		'exclude_override' => 'arrayToList',
		'blog_url_aliases' => 'arrayToList',
		'api_url_paths' => 'arrayToList',
		'api_html_paths' => 'arrayToList',
	);

	public static function instance($ms = false)
	{
		if (empty(self::$instance))
			self::$instance = new Pagely_CDNConfig(true, $ms);

		return self::$instance;
	}

	public function __construct($load = true)
	{
		$this->wpoptions = new PagelyGlobalOptions(pagely_cdn_network_wide());

		if ($load)
			$this->load();
	}

	public function ssl($on = null)
	{
		if ($on !== null)
			$this->ssl = $on;

		return $this->ssl;
	}

	public function cdnUrl()
	{
		if ($this->ssl)
			return $this->https_cdn_url;

		return $this->cdn_url;
	}
	
	public function zone_id()
	{
		
		return $this->zone_id;
	}

	public function load()
	{
		// when we are an mu-plugin init doesn't get run, so we do this check to do it
		$inited = $this->wpoptions->get('pagely_cdn_inited');
		if ($inited != 2)
			$this->registerOptions();
			
		foreach($this->options as $option)
		{
			$key = "pagely_cdn_$option";
			$raw_value = $this->wpoptions->get($key);
			$value = $this->fromSaveFormat($option, $raw_value);
			if ($value !== null)
				$this->$option = $value;
		}

        $api = new PagelyApi();
        $config = $api->config();
		
		// we can pull the right value of info.json
		if (empty($this->cdn_url) || preg_match('@c\.presscdn\.com$@', $this->cdn_url ))
		{
			if (!empty($config->cdn->ext_url))
			{
				$this->cdn_url = "http://".$config->cdn->ext_url;
			}
		}
		
		if (empty($this->zone_id) )
		{
			if (!empty($config->cdn->id))
			{
				$this->zone_id = $config->cdn->id;
			}
		}
	}

	
	public function save()
	{
		foreach($this->options as $option)
		{
			$key = "pagely_cdn_$option";
			$value = $this->toSaveFormat($option, $this->$option);
			$this->wpoptions->update($key, $value);
		}
				
	}

	public function display($key)
	{
		return $this->valueFromMap($this->displayMap, $key, $this->$key);
	}

	public function setFromArray($array)
	{
		foreach($array as $k => $v)
		{
			if (in_array($k, $this->options))
				$this->setFromUser($k, $v);
		}
	}
	
	public function setFromUser($key, $value)
	{
		$this->$key = $this->valueFromMap($this->userMap, $key, $value);
	}


	protected function arrayToList($array)
	{
		return implode("\n", $array);
	}

	protected function listToArray($list)
	{
		if (strstr($list, ','))
			$list = str_replace(',', "\n", $list);
		$array = explode("\n", $list);
		return array_map('trim', $array);
	}

	protected function toSaveFormat($key, $value)
	{
		return $this->valueFromMap($this->saveMap, $key, $value);
	}

	protected function fromSaveFormat($key, $value)
	{
		return $this->valueFromMap($this->loadMap, $key, $value);
	}

	protected function valueFromMap($map, $key, $value)
	{
		if (isset($map[$key]))
		{
			$method = $map[$key];
			$value = $this->$method($value);
		}

		return $value;
	}

	public function registerOptions()
	{
		foreach($this->options as $option)
		{
			$key = "pagely_cdn_$option";
			if ($option == 'enabled' && is_multisite())
				$this->$option = false;

			$this->wpoptions->add($key, $this->toSaveFormat($option, $this->$option));
		}
        $this->wpoptions->update("pagely_cdn_inited", 2);
	}

	public function deleteOptions($networkWide)
	{
		foreach($this->options as $option)
		{
			$key = "pagely_cdn_$option";
			$this->wpoptions->delete($key);
		}
	}
}

/**
 * Reperesents the CDN Linker's rewrite logic.
 *
 * 'rewrite' gets the raw HTML as input and returns the final result.
 * It finds all links and runs them through 'rewrite_singe', which prepends the CDN domain.
 *
 * 'Pagely_CDNLinksRewriter' contains no WP related function calls and can thus be used in testing or in other software.
 *
 */
class Pagely_CDNLinksRewriter
{
	protected $config;
	public $cdnUrl;
	public $blogUrl;
	public $blogUrlAliases = [];
    protected $fullCdnUrl;

	function __construct(Pagely_CDNConfig $config)
	{
		$this->config = $config;
	}

	/**
	 * Determines whether to exclude a match.
	 *
	 * @param String $match URI to examine
	 * @return Boolean true if to exclude given match from rewriting
	 */
	protected function exclude_single($match)
	{
		$ext = '';
		$posq = strrpos($match, '?');
		$path = @parse_url($match, PHP_URL_PATH);

        foreach ($this->config->exclude_override as $override)
        {
			if (empty($override))
				continue;
			if ($override[0] == '/')
			{
				// @todo make a combined regex for all of these, and maybe the stristrs too
				$r = '#^(https?://[^/]+)?';
				$r .= preg_quote($this->blogDir, '#');
				$r .= preg_quote($override,'#').'.*$#';
				if (preg_match($r, $path))
					return false;
			}
			else if (stristr($path, $override) != false)
			{
				return false;
			}
        }

		$pos = strrpos($path, '.');
		if ($pos)
			$ext = substr($path, $pos+1);

		foreach($this->config->exclude_ext as $badext)
		{
			if ($ext === $badext)
				return true;
		}
		foreach ($this->config->exclude as $badword) 
		{
			if (empty($badword))
				continue;
			if ($badword[0] == '/')
			{
				// @todo make a combined regex for all of
				// these, and maybe the stristrs too
				$r = '#(?:(?:https?:)*'.$this->buildBlogUrlRegexSnippet().')'; // match the blog url with an optional http: https:
				if ($this->config->rootrelative) {
					$r .= '?'; // make the hostname block optional
				}
				$r .= preg_quote($this->blogDir, '#');
				$r .= preg_quote($badword,'#').'.*$#';
				if (preg_match($r, $match))
					return true;
			
			}
			else if (stristr($match, $badword) != false)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Rewriter of URLs, used as callback for rewriting in {@link pagely_cdn_filter}.
	 *
	 * @param String $match An URI as candidate for rewriting
	 * @return String the unmodified URI if it is not to be rewritten, otherwise a modified one pointing to CDN
	 */
	protected function rewrite_single($match)
	{
        $url = $match[0];
    
		if ($this->exclude_single($url))
		{
			return $url;
		}
		else
		{
            $aliasMatch = false;
            if (count($this->blogUrlAliases) > 0)
            {
                foreach($this->blogUrlAliases as $alias)
                {
                    if (strstr($url, $alias))
                    {
                        $aliasMatch = true;
                        break;
                    }
                }
            }

            $cdnUrl = $this->cdnUrl;
            if (!strstr($url, 'http'))
            {
                $cdnUrl = str_replace(['http:', 'https:'], '', $cdnUrl);
            }

            if ($aliasMatch)
            {
				return preg_replace("@((https?:)?".preg_quote($alias, '@').")@", $cdnUrl, $url);
            }
            else if (!$this->config->rootrelative || strstr($url, $this->blogUrl))
			{
				return preg_replace("@((https?:)?".preg_quote($this->blogUrl, '@').")@", $cdnUrl, $url);
			}
            // make sure we don't have an unmatched url like //foo.com/bar
			else if ($this->config->rootrelative && substr($url, 0, 2) != '//') 
			{
				return $this->fullCdnUrl . $url;
			}

            return $url;
		}
	}

	/**
	 * Creates a regexp compatible pattern from the directories to be included in matching.
	 *
	 * @return String regexp pattern for those directories, or empty if none are given
	 */
	protected function dirPattern()
	{
		$dirs = array();
		foreach($this->config->include_dirs as $dir)
		{
			if (empty($dir))
				continue;
			if ($dir[0] == '/')
			{
				$d = substr($dir, 1);
				$dirs[] = preg_quote($d,'#');
			}
			else
			{
				$dirs[] = '(?:[^\"\')]*?'.preg_quote($dir,'#').'[^\"\')]*?)';
			}
		}
		return implode('|',$dirs);
	}

	/**
	 * Output filter which runs the actual plugin logic.
	 *
	 * @param String $content the raw HTML of the page from Wordpress, meant to be returned to the requester but intercepted here
	 * @return String modified HTML with replaced links - will be served by the HTTP server to the requester
	 */
	public function rewrite($content)
	{
        // this cleanup of urls supports //foo.com/ style urls
        $this->fullCdnUrl = $this->cdnUrl;
        $this->blogUrl = str_replace(array("http:","https:"), '', $this->blogUrl);
        $this->blogUrlAliases = array_map(function($alias) { return str_replace(array("http:","https:"), '', $alias); }, $this->blogUrlAliases);
        //$this->cdnUrl = str_replace(array("http:","https:"), '', $this->cdnUrl);

        // @todo decide if we should check that are are inside a tag (regex isn't great at that)

        // we are being pretty agressive on our matching, matching any url inside of 'single quotes' "double quotes" (parens)
        // if we are in relativeroot mode, we just need to start with a / and include directories
        // if we are in standard mode we also need to match the blogUrl with an optional (http: or https:)

		$dirs = $this->dirPattern();
        $regex = '#(?<=[(\"\']|&\#039;)'; // look behind, we start the patter with a ( " or '
        $regex .= '(?:(?:https?:)*'.$this->buildBlogUrlRegexSnippet().')'; // match the blog url with an optional http: https:
        if ($this->config->rootrelative)
        {
			$regex .= '?'; // make the hostname block optional
        }

        //$regex .= '/(?:((?:'.$dirs.')[^\"\')]+)|([^/\"\']+\.[^/\"\')]+))   in our original regex we had this second match section that doesn't make sense to me, looks like it should match
        //any url not just those in our directories
        $regex .= preg_quote($this->blogDir, '#');
        $regex .= '/((?:'.$dirs.')[^\"\')]+?)'; // match from the first / after the hostname, limited to the directory pattern build by dirPattern(), until we get a " ' or )

        // why )
        $regex .= '(?=[\"\')]|&\#039;)#'; // look ahead and make sure we have a " ' or
		return preg_replace_callback($regex, array($this, 'rewrite_single'), $content);
	}


    protected function buildBlogUrlRegexSnippet()
    {
        $blogUrl = preg_quote($this->blogUrl,'#');
        if (count($this->blogUrlAliases) > 0)
        {
            foreach($this->blogUrlAliases as $url)
                $blogUrl .= '|'.preg_quote($url,'#');

            $blogUrl = "($blogUrl)";
        }

        return $blogUrl;
    }

    public function setBlogUrlDirFromHomeUrl($homeUrl, $contentUrl = false)
    {
        $url = parse_url($homeUrl);
        if (empty($url["path"])) {
            $url["path"] = "";
        }
        $this->blogDir = rtrim($url["path"], '/');

        // if wp-content content isn't in a dir but home_url is, assume hackery is going on and clear out blogDir
        $contentDir = rtrim(parse_url($contentUrl, PHP_URL_PATH), '/');
        if (dirname($contentDir) == '/')
            $this->blogDir = '';

        $this->blogUrl = rtrim(
            str_replace(
                $url["host"] . $url["path"], 
                $url["host"], 
                $homeUrl),
            '/'
        );
    }
}

/**
 * The rewrite logic with calls to Wordpress.
 */
class Pagely_CDNLinksRewriterWordpress extends Pagely_CDNLinksRewriter
{
	function __construct($config)
	{
		if (is_ssl())
			$config->ssl(true);
		parent::__construct($config);

        if (!empty($_COOKIE['pagely_dev_mode']))
            $this->config->enabled = false;

        $this->setBlogUrlDirFromHomeUrl(home_url(), content_url());
        $this->cdnUrl = $config->cdnUrl();
	}

	/**
	 * Registers the output buffer, if needed.
	 *
	 * This function is called by Wordpress if the plugin was enabled.
	 */
	public function registerOutputBuffer()
	{
		$cdnUrl = $this->config->cdnUrl();
		if ($this->config->enabled && !empty($cdnUrl) && $this->blogUrl != $cdnUrl)
			ob_start(array(&$this, 'rewrite'));
	}

    // we are attaching into the final hook before serving to make sure any other additions have already been made
    public function rewriteJson($served, $response, $path, $method, $server)
    {
        global $wp_json_server;
        if (preg_match('@^/(posts|pages)@', $path))
        {
            // this is the worst, but we need a normalized response, to use JmesPath
            $json = json_decode(json_encode($wp_json_server->prepare_response($response)));


            // standard chroot
            if (file_exists('/user/pagely-vendor/autoload.php'))
            {
                require_once '/user/pagely-vendor/autoload.php';
            }
            // non chroot standard file layout
            elseif (file_exists( __DIR__.'/../../../../user/pagely-vendor/autoload.php'))
            {
                require_once  __DIR__.'/../../../../user/pagely-vendor/autoload.php';
            }
            // non chroot deep webroot
            elseif (file_exists( __DIR__.'/../../../../../user/pagely-vendor/autoload.php'))
            {
                require_once  __DIR__.'/../../../../../user/pagely-vendor/autoload.php';
            }
            // oh noes
            else
            {
                if (preg_match('@^(/data/s[0-9]+/dom[0-9]+)/@', __DIR__, $match))
                {
                    require_once $match[1].'/user/pagely-vendor/autoload.php';

                }
                else
                {
                    throw new Exception("Could not bootstrap composer libs: ".__DIR__);
                }
            }


            $jmes = new EditableJmesPath();
            foreach($this->config->api_html_paths as $path)
            {
                $matches =& $jmes->search($path, $json);
                if ($matches !== null)
                {
                    if (is_array($matches))
                    {
                        foreach($matches as $index => $match)
                        {
                            $matches[$index] = $this->rewrite($matches[$index]);
                        }
                    }
                    else
                    {
                        $matches = $this->rewrite($matches); // handles html
                    }
                }
            }

            foreach($this->config->api_url_paths as $path)
            {
                $matches =& $jmes->search($path, $json);
                if ($matches !== null)
                {
                    if (is_array($matches))
                    {
                        foreach($matches as $index => $match)
                        {
                            $matches[$index] = $this->rewrite_single([$matches[$index]]);
                        }
                    }
                    else
                    {
                        $matches = $this->rewrite_single([$matches]);
                    }
                }
            }

            $response->data = $json;
            return $served;
        }
    }
}
