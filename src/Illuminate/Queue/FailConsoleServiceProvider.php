<?php namespace Illuminate\Queue;

use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Console\RetryCommand;
use Illuminate\Queue\Console\ListFailedCommand;
use Illuminate\Queue\Console\FlushFailedCommand;
use Illuminate\Queue\Console\ForgetFailedCommand;

class FailConsoleServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('command.queue.failed', function($app)
		{
			return new ListFailedCommand($app['queue.failer']);
		});

		$this->app->bindShared('command.queue.retry', function($app)
		{
			return new RetryCommand($app['queue.failer'], $app['queue']);
		});

		$this->app->bindShared('command.queue.forget', function($app)
		{
			return new ForgetFailedCommand($app['queue.failer']);
		});

		$this->app->bindShared('command.queue.flush', function($app)
		{
			return new FlushFailedCommand($app['queue.failer']);
		});

		$this->commands('command.queue.failed', 'command.queue.retry', 'command.queue.forget', 'command.queue.flush');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
			'command.queue.failed', 'command.queue.retry', 'command.queue.forget', 'command.queue.flush',
		);
	}

}