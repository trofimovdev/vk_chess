<?php

namespace App\Http\Responses;


class OkResponse extends BaseResponse
{
    public $response;


    public function __construct($response)
    {
        $this->success = true;
        $this->response = $response;
    }
}
