<?php
/**
 * Contain all response functions
 */
namespace App;
use Log;
/**
  Contain api response functions
 */
class ApiResponse
{


    /**
     * Check if the access token has been revoked.
     *
     * @param array   $data   Contain array of data.
     * @param integer $status Default code 200.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success(array $data, int $status=OK_HTTP_STATUS_CODE)
    {
        $content = [
            'status' => true,
            'data'   => $data,
            'error'  => [],
        ];
        return response()->json($content, $status);

    }//end success()


    /**
     * Send Json response to user with message.
     *
     * @param string $message Success message to send along with success response.
     *
     * @return \Illuminate\Http\JsonResponse Return json response.
     */
    public static function successMessage(string $message)
    {
        return self::success(
            ['message' => $message]
        );

    }//end successMessage()


    /**
     * Send Json response to user with 201 code.
     *
     * @param array $data Contain array of data.
     *
     * @return \Illuminate\Http\JsonResponse Return json response with 201 http code.
     */
    public static function create(array $data)
    {
        return self::success($data, CREATED_HTTP_STATUS_CODE);

    }//end create()


    /**
     * Send Json response to user with message with 201 code.
     *
     * @param string $message Message to send with create.
     *
     * @return \Illuminate\Http\JsonResponse Return json respose with message and 201 http code.
     */
    public static function createMessage(string $message)
    {
        return self::create(
            ['message' => $message]
        );

    }//end createMessage()


    /**
     * Send Json response to user with error and 400 code.
     *
     * @param array   $error  Array of errors.
     * @param integer $status Http status code default 400.
     *
     * @return \Illuminate\Http\JsonResponse Return json respose with error array and 400 http code.
     */
    public static function error(array $error, int $status=BAD_REQUEST_HTTP_STATUS_CODE)
    {
        $content = [
            'status' => false,
            'data'   => new \stdClass,
            'error'  => $error,
        ];

        return response()->json($content, $status);

    }//end error()


    /**
     * Send Json response to user with error and 400 code and message.
     *
     * @param string $message Message to send with error.
     *
     * @return \Illuminate\Http\JsonResponse Return json respose with error array and 400 http code and a message.
     */
    public static function errorMessage(string $message)
    {
        return self::error(
            [
                [
                    'code'    => '',
                    'key'     => '',
                    'message' => $message,
                ],
            ]
        );

    }//end errorMessage()


    /**
     * Check if the access token has been revoked.
     *
     * @param array $params Params whose validation failed.
     *
     * @return \Illuminate\Http\JsonResponse Return json respose with array contained validation failed params.
     */
    public static function validationFailed(array $params)
    {
        $errors = [];
        foreach ($params as $key => $value) {
            $errors[] = [
                'code'    => EC_VALIDATION_FAILED,
                'key'     => $key,
                'message' => $value,
            ];
        }

        return self::error($errors);

    }//end validationFailed()


    /**
     * Function to call if there is error on server side.
     *
     * @param string $code    Error code.
     * @param string $message Message for server error.
     *
     * @return \Illuminate\Http\JsonResponse response containing error code and error data for server error.
     */
    public static function serverError(string $code, string $message)
    {
        return self::error(
            [[
                'code'    => $code,
                'key'     => '',
                'message' => $message,
            ],
            ],
            INTERNAL_SERVER_ERROR_HTTP_STATUS_CODE
        );

    }//end serverError()


    /**
     * For use when authentication is possible but has failed or not yet been provided.
     *
     * @param string $code    Error code.
     * @param string $message Message for server error.
     * @param string $key     Key for which error happened.
     *
     * @return \Illuminate\Http\JsonResponse response containing error code and error data.
     */
    public static function unauthorizedError(string $code, string $message, string $key='')
    {
        return self::error(
            [[
                'code'    => $code,
                'key'     => $key,
                'message' => $message,
            ],
            ],
            UNAUTHORIZED_HTTP_STATUS_CODE
        );

    }//end unauthorizedError()


    /**
     * Sends json response when code breaks.
     *
     * @param string $code    Error code.
     * @param string $message Message for server error.
     *
     * @return \Illuminate\Http\JsonResponse response containing error code and error data and message.
     */
    public static function badRequestError(string $code, string $message)
    {
        return self::error(
            [[
                'code'    => $code,
                'key'     => '',
                'message' => $message,
            ],
            ],
            BAD_REQUEST_HTTP_STATUS_CODE
        );

    }//end badRequestError()


    /**
     * Function to call if there is request is ok but some condition or constraint failed.
     *
     * @param string $code    Error code.
     * @param string $message Message for server error.
     *
     * @return \Illuminate\Http\JsonResponse response containing error code and error data.
     */
    public static function forbiddenError(string $code, string $message)
    {
        return self::error(
            [[
                'code'    => $code,
                'key'     => '',
                'message' => $message,
            ],
            ],
            FORBIDDEN_HTTP_STATUS_CODE
        );

    }//end forbiddenError()


    /**
     * Function to call if there is request is ok but resource or record not found.
     *
     * @param string $code    Error code.
     * @param string $message Message for server error.
     *
     * @return \Illuminate\Http\JsonResponse response containing error code and error data.
     */
    public static function notFoundError(string $code, string $message)
    {
        return self::error(
            [[
                'code'    => $code,
                'key'     => '',
                'message' => $message,
            ],
            ],
            NOT_FOUND_HTTP_STATUS_CODE
        );

    }//end notFoundError()


    /**
     * Function to call if the request depends on an external API but received an invalid response.
     *
     * @param string $code    Error code.
     * @param string $message Message for server error.
     *
     * @return \Illuminate\Http\JsonResponse response containing error code and error data.
     */
    public static function badGatewayError(string $code, string $message)
    {
        return self::error(
            [[
                'code'    => $code,
                'key'     => '',
                'message' => $message,
            ],
            ],
            BAD_GATEWAY_HTTP_STATUS_CODE
        );

    }//end badGatewayError()


    /**
     * Function to call if the request depends on an external API but received no response.
     *
     * @param string $code    Error code.
     * @param string $message Message for server error.
     *
     * @return \Illuminate\Http\JsonResponse response containing error code and error data.
     */
    public static function serviceUnavailableError(string $code, string $message)
    {
        return self::error(
            [[
                'code'    => $code,
                'key'     => '',
                'message' => $message,
            ],
            ],
            SERVICE_UNAVAILABLE_HTTP_STATUS_CODE
        );

    }//end serviceUnavailableError()


}//end class
