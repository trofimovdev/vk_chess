<?php

namespace App\Http\Responses;


abstract class BaseResponse
{
    public bool $success = true;


    public function __toString(): string
    {
        return json_encode($this);
    }
}
