<?php

namespace PerryvanderMeer\Minify\Providers;

use PerryvanderMeer\Minify\Contracts\Minify;
use JShrink\Minifier;

class JavaScript extends BaseProvider implements Minify
{
    /**
     * The extension of the outputted file.
     */
    const EXTENSION = '.js';

    /**
	 * Returns minified content.
	 *
     * @return string
     */
    public function minify () : string
    {
        $minified = Minifier::minify($this->appended);

        return $this->put($minified);
    }

    /**
	 * Returns a HTML tag for loading the minified content.
	 *
     * @param string  $file
     * @param array   $attributes
     * @return string
     */
    public function tag (string $file, array $attributes) : string
    {
        $attributes = ['src' => $file] + $attributes;

        return "<script {$this->attributes($attributes)} crossorigin=\"anonymous\"></script>" . PHP_EOL;
    }
}
