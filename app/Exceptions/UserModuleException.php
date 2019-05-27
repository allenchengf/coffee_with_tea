<?php

namespace App\Exceptions;

use Exception;

class UserModuleException extends Exception
{
    public $message;
    public $errorCode;

    public function __construct($message = [], $errorCode = 5000)
    {
        $this->message = $message;
        $this->errorCode = $errorCode;
        parent::__construct($message, $errorCode);
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        //
    }

    /**
     * 将異常渲染到 HTTP 響應中
     *
     * @param  \Illuminate\Http\Request
     * @return void
     */
    public function render()
    {
        return response()->json([
            'message' => $this->message,
            'errorCode' => $this->errorCode,
        ])->setStatusCode(400);
    }
}
