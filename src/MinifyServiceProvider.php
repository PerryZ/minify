<?php

namespace PerryvanderMeer\Minify;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class MinifyServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot ()
	{
		$this->publishes([
			__DIR__ . '/config/minify.php' => config_path('minify.php'),
		],
		'minify');
	}

	/**
     * Register any application services.
     *
     * @return void
     */
	public function register ()
	{
		$this->app->singleton('minify', function ($app)
		{
			return new Minify(
				[
					'css_build_path' => config('minify.css_build_path'),
					'css_url_path' => config('minify.css_url_path'),
					'js_build_path' => config('minify.js_build_path'),
					'js_url_path' => config('minify.js_url_path'),
					'ignore_environments' => config('minify.ignore_environments'),
					'base_url' => config('minify.base_url'),
					'reverse_sort' => config('minify.reverse_sort'),
					'disable_mtime' => config('minify.disable_mtime'),
					'hash_salt' => config('minify.hash_salt'),
					'disable_url_correction' => config('minify.disable_url_correction'),
				],
				$app->environment()
			);
		});

		$this->mergeConfigFrom(
			__DIR__ . '/config/minify.php', 'minify'
		);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides ()
	{
		return 'minify';
	}
}
