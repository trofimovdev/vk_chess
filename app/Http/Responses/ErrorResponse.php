<?php

namespace App\Http\Responses;


class ErrorResponse extends BaseResponse
{
    public array $error;

    private const FIELD_CODE = 'code';
    private const FIELD_MSG = 'msg';


    public function __construct(int $code, string $msg)
    {
        $this->success = false;
        $this->error = [
            self::FIELD_CODE => $code,
            self::FIELD_MSG => $msg
        ];
    }
}
