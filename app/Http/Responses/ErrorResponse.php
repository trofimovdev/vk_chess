<?php

namespace App\Http\Responses;


class ErrorResponse extends BaseResponse
{
    public array $error;


    public function __construct(int $code, string $msg)
    {
        $this->success = false;
        $this->error = [
            'code' => $code,
            'msg' => $msg
        ];
    }
}
