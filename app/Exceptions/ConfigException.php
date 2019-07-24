<?php

namespace App\Exceptions;

use Exception;

class ConfigException extends Exception
{
    public $message;
    public $errorCode;

    public function __construct($message, $errorCode = 9875)
    {
        $this->message = ($errorCode == 400) ? json_decode($message, true) : $message;
        $this->errorCode = $errorCode;
        parent::__construct($message, $errorCode);
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
