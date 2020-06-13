<?php

namespace App\Http\Responses;


abstract class BaseResponse
{
    public bool $success = true;


    public function __toString()
    {
        return json_encode($this);
    }
}
