<?php

namespace PerryvanderMeer\Minify\Providers;

use PerryvanderMeer\Minify\Exceptions\FileNotExistException;
use PerryvanderMeer\Minify\Contracts\Minify;
use CssMinifier;

class StyleSheet extends BaseProvider implements Minify
{
    /**
     * The extension of the outputted file.
     */
    const EXTENSION = '.css';

    /**
	 * Returns minified content.
	 *
     * @return string
     */
    public function minify () : string
    {
        $minified = new CssMinifier($this->appended);

        return $this->put($minified->getMinified());
    }

    /**
	 * Returns a HTML tag for loading the minified content.
	 *
     * @param string  $file
     * @param array   $attributes
     * @return string
     */
    public function tag (string $file, array $attributes = []) : string
    {
        $attributes = ['href' => $file, 'rel' => 'stylesheet'] + $attributes;

        return "<link {$this->attributes($attributes)}>" . PHP_EOL;
    }

    /**
     * Override appendFiles to solve css url path issue
     *
     * @throws \PerryvanderMeer\Minify\Exceptions\FileNotExistException
     */
    protected function appendFiles()
    {
        foreach($this->files as $file)
		{
            if($this->checkExternalFile($file))
			{
                if(strpos($file, '//') === 0)
				{
					$file = 'http:' . $file;
				}

                $headers	= $this->headers;

                foreach($headers as $key => $value)
				{
                    $headers[$key] = $key.': '.$value;
                }

                $context = stream_context_create(['http' =>
				[
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

            $contents	= $this->urlCorrection($file);
            $this->appended .= $contents."\n";
        }
    }

    /**
     * Css url path correction
     *
     * @param string $file
     * @return string
     */
    public function urlCorrection ($file)
    {
        $folder             = str_replace(public_path('\\'), '', $file);
        $folder             = str_replace(basename($folder), '', $folder);
        $content            = file_get_contents($file);
        $contentReplace     = [];
        $contentReplaceWith = [];

		if($this->disable_url_correction)
		{
            return $content;
        }

		preg_match_all('/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i', $content, $matches, PREG_PATTERN_ORDER);

        if(!count($matches))
		{
            return $content;
        }

        foreach($matches[0] as $match)
		{
            if(strpos($match, "'") != false)
			{
                $contentReplace[]     = $match;
                $contentReplaceWith[] = str_replace('url(\'', 'url(\''.$folder, $match);
            }
			elseif(strpos($match, '"') !== false)
			{
                $contentReplace[]     = $match;
                $contentReplaceWith[] = str_replace('url("', 'url("'.$folder, $match);
            }
			else
			{
                $contentReplace[]     = $match;
                $contentReplaceWith[] = str_replace('url(', 'url('.$folder, $match);
            }
        }

        return str_replace($contentReplace, $contentReplaceWith, $content);
    }
}
