<?php

namespace Tyea\LaraReactPhp;

use React\Http\Io\ServerRequest as ReactPhpRequest;
use React\Http\Response as ReactPhpResponse;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tyea\LaraReactPhp\Factories\RequestFactory;
use Tyea\LaraReactPhp\Factories\ResponseFactory;
use Exception;

class Handler
{
	private function __construct()
	{
	}
	
	public static function handle(ReactPhpRequest $reactPhpRequest): ReactPhpResponse
	{
		// @todo use promises
		// @todo investigate terminable middleware
		$pathExists = Storage::disk("reactphp")->exists($reactPhpRequest->getUri()->getPath());
		$isEntryPoint = Str::startsWith($reactPhpRequest->getUri()->getPath(), "/index.php");
		if ($pathExists && !$isEntryPoint) {
			$reactPhpResponse = ResponseFactory::makeFromPath($reactPhpRequest);
		} else {
			$kernel = App::make("Illuminate\\Contracts\\Http\\Kernel");
			$laravelRequest = RequestFactory::makeFromRequest($reactPhpRequest);
			$laravelResponse = $kernel->handle($laravelRequest);
			$reactPhpResponse = ResponseFactory::makeFromResponse($laravelResponse);
		}
		return $reactPhpResponse;
	}
	
	public static function recover(Exception $exception): Void
	{
		// @todo error formatting
		echo $exception . "\n";
	}
}
