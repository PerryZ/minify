<?php

namespace PerryvanderMeer\Minify\Contracts;

interface Minify
{
    /**
     * Returns minified content.
     *
     * @return string
     */
    public function minify () : string;

    /**
     * Returns a HTML tag for loading the minified content.
     *
     * @param  string $file
     * @param  array  $attributes
     * @return string
     */
    public function tag (string $file, array $attributes) : string;
}
