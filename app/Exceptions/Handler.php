<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception) {
        if($exception instanceof GetDataFailedException){
            return GetDataFailedException::render($exception->getMessage()); //E-0021
        }
        else if($exception instanceof StoreDataFailedException){
            return StoreDataFailedException::render($exception->getMessage()); //E-0022
        }
        else if($exception instanceof SaveFileFailedException){
            return SaveFileFailedException::render($exception->getMessage()); //E-0023
        }
        else if($exception instanceof UpdateDataFailedException){
            return UpdateDataFailedException::render($exception->getMessage()); //E-0024
        }
        else if($exception instanceof DeleteDataFailedException){
            return DeleteDataFailedException::render($exception->getMessage()); //E-0025
        }
        else if($exception instanceof SearchDataFailedException){
            return SearchDataFailedException::render($exception->getMessage()); //E-0026
        }
        else if($exception instanceof GetHistoryRangeFailedException){
            return GetHistoryRangeFailedException::render($exception->getMessage()); //E-0026
        }
        else if ($exception instanceof ValidationException) 
        { 
            return response()->json(["error" => true,"code" => "E-0031","message" => $exception->errors()], 422);
        }
        else if($exception instanceof SuspiciousInputException){
            return SuspiciousInputException::render($exception->getMessage()); //E-0032
        }
        else if ($exception instanceof ModelNotFoundException) {
            return response()->json(['error' => true, 'code' => 'E-0033', 'message'=> "data not found"], 400);
        }
        else
        {
            // if($message = $exception->getMessage()) {
            //     return UnexpectedException::render("Unexpected Exception : " . $message); //E-0041
            // } else {
            //     return UnexpectedException::render("Unexpected Exception : undefined message"); //E-0041
            // }
            
            //go to next request
            return parent::render($request, $exception);
        }
    }
}
