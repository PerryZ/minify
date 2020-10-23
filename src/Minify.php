<?php

namespace PerryvanderMeer\Minify;

use PerryvanderMeer\Minify\Exceptions\InvalidArgumentException;
use PerryvanderMeer\Minify\Providers\{JavaScript, StyleSheet};

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Request;

class Minify
{
	protected $config;
	protected $environment;

	protected $provider;
	protected $attributes = [];
	protected $buildPath;
	protected $buildExtension;

	protected $fullUrl = false;
	protected $onlyUrl = false;

	/**
	 * Create a new minifier from the service provider.
	 *
	 * @param array  $config
	 * @param string $environment
	 */
	public function __construct (array $config, string $environment)
	{
		$this->checkConfiguration($config);

		$this->config		= $config;
		$this->environment	= $environment;
	}

	/**
	 * Minify single javascript file(s).
	 *
	 * @param mixed  $file
	 * @param array  $attributes
	 * @return mixed
	 */
	public function javascript ($file, array $attributes = [])
	{
		$this->provider			= new JavaScript(public_path('\\'), ['hash_salt' => $this->config['hash_salt'], 'disable_mtime' => $this->config['disable_mtime']]);
		$this->attributes		= $attributes;
		$this->buildPath		= $this->config['js_build_path'];
		$this->buildExtension	= 'js';

		$this->process($file);

		return $this;
	}

	/**
	 * Minify single stylesheet file(s).
	 *
	 * @param mixed  $file
	 * @param array  $attributes
	 * @return mixed
	 */
	public function stylesheet ($file, array $attributes = [])
	{
		$this->provider			= new StyleSheet(public_path('\\'), ['hash_salt' => $this->config['hash_salt'], 'disable_mtime' => $this->config['disable_mtime']]);
		$this->attributes		= $attributes;
		$this->buildPath		= $this->config['css_build_path'];
		$this->buildExtension	= 'css';

		$this->process($file);

		return $this;
	}

	/**
	 * Minify a full javascript directory.
	 *
	 * @param string  $dir
	 * @param array   $attributes
	 * @return mixed
	 */
	public function javascriptDir (string $dir, array $attributes = [])
	{
		$this->provider 		= new JavaScript(public_path('\\'), ['hash_salt' => $this->config['hash_salt'], 'disable_mtime' => $this->config['disable_mtime']]);
		$this->attributes 		= $attributes;
		$this->buildPath		= $this->config['js_build_path'];
		$this->buildExtension 	= 'js';

		return $this->assetDirHelper('js', $dir);
	}

	/**
	 * Minify a full stylesheet directory.
	 *
	 * @param string  $dir
	 * @param array   $attributes
	 * @return mixed
	 */
	public function stylesheetDir (string $dir, array $attributes = [])
	{
		$this->provider			= new StyleSheet(public_path('\\'), ['hash_salt' => $this->config['hash_salt'], 'disable_mtime' => $this->config['disable_mtime']]);
		$this->attributes 		= $attributes;
		$this->buildPath		= $this->config['css_build_path'];
		$this->buildExtension 	= 'css';

		return $this->assetDirHelper('css', $dir);
	}

	/**
	 * Minify all files in folder.
	 *
	 * @param string  $ext
	 * @param string  $dir
	 * @return mixed
	 */
	private function assetDirHelper (string $ext, string $dir)
	{
		# Create an empty array for storing files
		$files	= [];

		$itr_obj	= new RecursiveDirectoryIterator(public_path('\\') . $dir);
		$itr_obj->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
		$dir_obj	= new RecursiveIteratorIterator($itr_obj);

		# Loop through files in folder and add files to array
		foreach($dir_obj as $fileinfo)
		{
			if(
				!$fileinfo->isDir()
				&& ($filename = $fileinfo->getFilename())
				&& (pathinfo($filename, PATHINFO_EXTENSION) === $ext)
				&& (strlen($fileinfo->getFilename()) < 30)
			)
			{
				$files[] = str_replace(public_path('\\'), '', $fileinfo);
			}
		}

		# Only continue if files in folder are found
		if(count($files) > 0)
		{
			# (Reverse) sort files and continue processing
			$this->config['reverse_sort'] ? rsort($files) : sort($files);

			$this->process($files);
		}

		return $this;
	}

	/**
	 * Process the current files to minify.
	 *
	 * @param mixed  $file
	 */
	private function process ($file)
	{
		# Add current file to minifier
		$this->provider->add($file);

		# Check if minifier is enabled and if buildPath exists
		# and minify the current file(s)
		if($this->minifyForCurrentEnvironment() && $this->provider->make($this->buildPath))
		{
			$this->provider->minify();
		}

		# Only output the full url for this minifier request.
		$this->fullUrl = false;
	}

	/**
	 * Core function: renders minified content.
	 *
	 * @return string
	 */
	protected function render () : string
	{
		# Create an absolute of relative path
		$baseUrl	= $this->fullUrl ? $this->getBaseUrl() : '';

		# Checks if the files should be minified in the current environment.
		# If not: return the HTML tags for including the original files.
		if(!$this->minifyForCurrentEnvironment())
		{
			return $this->provider->tags($baseUrl, $this->attributes);
		}

		# Build the full path to the minified file.
		$filename = $baseUrl . $this->buildPath  . $this->provider->getFilename();

		# Only return the path to the minified file.
		if($this->onlyUrl)
		{
			return $filename;
		}

		# Return the HTML tags for including the files.
		return $this->provider->tag($filename, $this->attributes);
	}

	/**
	 * Checks if the files should be minified in the current environment.
	 *
	 * @return bool
	 */
	protected function minifyForCurrentEnvironment ()
	{
		return !in_array($this->environment, $this->config['ignore_environments']);
	}

	/**
	 * Sets the minifier to include the full absolute path to the minified file.
	 *
	 * @return mixed
	 */
	public function withFullUrl ()
	{
		$this->fullUrl = true;

		return $this;
	}

	/**
	 * Sets the minifier to only return the path to the minified file.
	 *
	 * @return mixed
	 */
	public function onlyUrl ()
	{
		$this->onlyUrl = true;

		return $this;
	}

	/**
	 * Minifies the content by directly accessing the variable.
	 *
	 * @return string
	 */
	public function __toString ()
	{
		return $this->render();
	}

	/**
	 * Check if the essential items exists in the config file
	 * and check if the items has the correct types.
	 *
	 * @param array $config
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function checkConfiguration (array $config)
	{
		# Check if css_build_path exists in config and has the correct type
		if(!isset($config['css_build_path']) || !is_string($config['css_build_path']))
		{
			throw new InvalidArgumentException("Missing css_build_path field");
		}

		# Check if js_build_path exists in config and has the correct type
		if(!isset($config['js_build_path']) || !is_string($config['js_build_path']))
		{
			throw new InvalidArgumentException("Missing js_build_path field");
		}

		# Check if ignore_environments exists in config and has the correct type
		if(!isset($config['ignore_environments']) || !is_array($config['ignore_environments']))
		{
			throw new InvalidArgumentException("Missing ignore_environments field");
		}
	}

	/**
	 * Returns the base_url from the config or returns a default value.
	 *
	 * @return string
	 */
	private function getBaseUrl ()
	{
		# If the base_url isn't given: use the request's root
		if(is_null($this->config['base_url']) || trim($this->config['base_url']) == '')
		{
			return Request::root();
		}

		# Return the base_url from the config
		return $this->config['base_url'];
	}
}
