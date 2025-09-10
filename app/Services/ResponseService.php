<?php

namespace App\Services;


use Illuminate\Http\Response;


class ResponseService
{

    /**
     * api response
     *
     * @param integer $code
     * @param string $message
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    public static function apiResponse(int $code, string $message = "", $data = [])
    {
        return response()->json([
            'message' => $message,
            'data'    => $data,
        ], $code);

    }

}
