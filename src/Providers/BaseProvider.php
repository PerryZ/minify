<?php

namespace PerryvanderMeer\Minify\Providers;

use PerryvanderMeer\Minify\Exceptions\{CannotRemoveFileException, CannotSaveFileException, DirNotExistException, DirNotWritableException, FileNotExistException};

use Illuminate\Filesystem\Filesystem;
use Countable;

abstract class BaseProvider implements Countable
{
    protected $outputDir;
    protected $appended = '';
    protected $filename = '';
    protected $files = [];
    protected $headers = [];
    private $publicPath;
    protected $file;
    private $disable_mtime;
    private $hash_salt;

    /**
     * @param null $publicPath
     */
    public function __construct($publicPath = null, $config = null, Filesystem $file = null)
    {
        $this->file			= $file ?: new Filesystem;
        $this->publicPath	= $publicPath ?: $_SERVER['DOCUMENT_ROOT'];

        $this->disable_mtime	= $config['disable_mtime'] ?: false;
        $this->hash_salt		= $config['hash_salt'] ?: '';

        $value = function($key)
        {
            return isset($_SERVER[$key]) ? $_SERVER[$key] : '';
        };

        $this->headers = array(
            'User-Agent'      => $value('HTTP_USER_AGENT'),
            'Accept'          => $value('HTTP_ACCEPT'),
            'Accept-Language' => $value('HTTP_ACCEPT_LANGUAGE'),
            'Accept-Encoding' => 'identity',
            'Connection'      => 'close',
        );
    }

    /**
     * Build a new minified file.
     *
     * @param $outputDir
     * @return bool
     */
    public function make ($outputDir) : bool
    {
        $this->outputDir = $this->publicPath . $outputDir;

        $this->checkDirectory();

        if($this->checkExistingFiles())
        {
            return false;
        }

        $this->removeOldFiles();
        $this->appendFiles();

        return true;
    }

	/**
	 * Add all files to the minifier.
	 *
     * @param  mixed $file
     * @throws \PerryvanderMeer\Minify\Exceptions\FileNotExistException
	 * @return void
     */
    public function add ($file)
    {
		# Array of files
        if(is_array($file))
        {
            foreach($file as $value) $this->add($value);
        }

		# External resource
        else if($this->checkExternalFile($file))
        {
            $this->files[] = $file;
        }

		# Just one file
        else
		{
            $file	= $this->publicPath . $file;

            if(!file_exists($file))
            {
                throw new FileNotExistException("File '{$file}' does not exist");
            }

            $this->files[] = $file;
        }
    }

    /**
     * Builds html tags, including all attributes.
     *
     * @param string $baseUrl
     * @param array  $attributes
     * @return string
     */
    public function tags (string $baseUrl, array $attributes) : string
    {
        $html = '';

		foreach($this->files as $file)
        {
            $file	= $baseUrl . str_replace($this->publicPath, '', $file);
            $html	.= $this->tag($file, $attributes);
        }

        return $html;
    }

    /**
     * Counts all the files that should be minified.
     *
     * @return int
     */
    public function count () : int
    {
        return count($this->files);
    }

    /**
     * Combines all required files into one variable.
     *
     * @throws \PerryvanderMeer\Minify\Exceptions\FileNotExistException
     */
    protected function appendFiles ()
    {
        foreach($this->files as $file)
		{
			# External resource: download file
            if($this->checkExternalFile($file))
            {
                if(strpos($file, '//') === 0)
				{
					$file = 'http:' . $file;
				}

                $headers = $this->headers;

				foreach ($headers as $key => $value)
                {
                    $headers[$key] = $key . ': ' . $value;
                }

				$context = stream_context_create(['http' => [
                    'ignore_errors' => true,
                    'header' => implode("\r\n", $headers),
                ]]);

                $http_response_header = [false];
                $contents = file_get_contents($file, false, $context);

                if(strpos($http_response_header[0], '200') === false)
                {
                    throw new FileNotExistException("File '{$file}' does not exist");
                }
            }

			# No external resource: load file contents
			else
			{
                $contents = file_get_contents($file);
            }

            $this->appended .= $contents . "\n";
        }
    }

    /**
     * Checks if the generated filename already exists: don't minify content.
     *
     * @return bool
     */
    protected function checkExistingFiles () : bool
    {
        $this->buildMinifiedFilename();

        return file_exists($this->outputDir . $this->filename);
    }

    /**
     * Checks if the buildpath exists and is writable.
     *
     * @throws \PerryvanderMeer\Minify\Exceptions\DirNotWritableException
     * @throws \PerryvanderMeer\Minify\Exceptions\DirNotExistException
     */
    protected function checkDirectory ()
    {
		if(!file_exists($this->outputDir))
		{
			# Try to create a new directory
			if(!$this->file->makeDirectory($this->outputDir, 0775, true))
			{
				throw new DirNotExistException("Buildpath '{$this->outputDir}' does not exist");
			}
		}

		if(!is_writable($this->outputDir))
		{
			throw new DirNotWritableException("Buildpath '{$this->outputDir}' is not writable");
		}
    }

    /**
     * Checks if the loaded file is an external resource.
     *
     * @param  string  $file
     * @return bool
     */
    protected function checkExternalFile (string $file) : bool
    {
        return preg_match('/^(https?:)?\/\//', $file);
    }

    /**
     * Builds the minified filename based on a hash and the modification time
     *
     * @return void
     */
    protected function buildMinifiedFilename ()
    {
        $this->filename = $this->getHashedFilename() . (($this->disable_mtime) ? '' : $this->countModificationTime()) . static::EXTENSION;
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @param  array  $attributes
     * @return string
     */
    protected function attributes (array $attributes)
    {
        $html	= [];

        foreach($attributes as $key => $value)
        {
            $element = $this->attributeElement($key, $value);

            if(!is_null($element))
			{
				$html[] = $element;
			}
        }

        $output = count($html) > 0 ? ' ' . implode(' ', $html) : '';

        return trim($output);
    }

    /**
     * Build a single attribute element.
     *
     * @param  string|integer $key
     * @param  string|boolean $value
     * @return string|null
     */
    protected function attributeElement ($key, $value)
    {
        if(is_numeric($key))
		{
			$key	= $value;
		}

        if(is_bool($value))
		{
            return $key;
		}

        if(!is_null($value))
		{
            return $key.'="'.htmlentities($value, ENT_QUOTES, 'UTF-8', false).'"';
		}

        return null;
    }

    /**
     * Returns a md5-hashed filename based on file contents.
     *
     * @return string
     */
    protected function getHashedFilename () : string
    {
		return md5(implode('-', array_map(fn ($file) => str_replace($this->publicPath, '', $file), $this->files)) . $this->hash_salt);
    }

    /**
     * Calculates the modification time for refreshing the minified files.
     *
     * @return int
     */
    protected function countModificationTime () : int
    {
        $time = 0;

        foreach($this->files as $file)
        {
            if($this->checkExternalFile($file))
            {
                $userAgent	= isset($this->headers['User-Agent']) ? $this->headers['User-Agent'] : '';
                $time 		+= hexdec(substr(md5($file . $userAgent), 0, 8));
            }
            else
			{
                $time		+= filemtime($file);
            }
        }

        return $time;
    }

    /**
     * Removes all old files.
     *
     * @throws \PerryvanderMeer\Minify\Exceptions\CannotRemoveFileException
     */
    protected function removeOldFiles ()
    {
        $pattern	= $this->outputDir . $this->getHashedFilename() . '*';
        $find		= glob($pattern);

		# Remove all old files from the build path
        if(is_array($find) && count($find))
        {
            foreach($find as $file)
            {
                if(!unlink($file))
				{
                    throw new CannotRemoveFileException("File '{$file}' cannot be removed");
                }
            }
        }
    }

    /**
     * Stores the minified content and returns the minified filename.
     *
     * @param  string $minified
     * @throws \PerryvanderMeer\Minify\Exceptions\CannotSaveFileException
	 * @return string
     */
    protected function put (string $minified) : string
    {
        if(file_put_contents($this->outputDir . $this->filename, $minified) === false)
        {
            throw new CannotSaveFileException("File '{$this->outputDir}{$this->filename}' cannot be saved");
        }

        return $this->filename;
    }

    /**
     * Returns the full code to minify.
     *
     * @return string
     */
    public function getAppended () : string
    {
        return $this->appended;
    }

    /**
     * Returns the minified filename.
     *
     * @return string
     */
    public function getFilename () : string
    {
        return $this->filename;
    }
}
