<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler {
	use ApiResponse;
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		AuthorizationException::class,
		HttpException::class,
		ModelNotFoundException::class,
		ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Throwable  $exception
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function report(Throwable $exception) {
		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Throwable  $exception
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 *
	 * @throws \Throwable
	 */
	public function render($request, Throwable $exception) {
		if ($exception instanceof AuthorizationException) {
			return $this->errorResponse($exception->getMessage(), Response::UNAUTHORIZED);
		}
		if ($exception instanceof HttpException) {
			$code = $exception->getStatusCode();
			$message = Response::$statusTexts[$code];
			return $this->errorResponse($message, $code);
		}
		if ($exception instanceof ModelNotFoundException) {
			$modelo = class_basename($exception->getModel());
			return $this->errorResponse("No existe el modelo {$modelo} para el id", Response::HTTP_NOT_FOUND);
		}
		if ($exception instanceof ValidationException) {
			$message = $exception->validator->errors()->getMessages();
			return $this->errorResponse($message, Response::HTTP_UNPROCESSABLE_ENTITY);
		}
		return parent::render($request, $exception);
	}
}
