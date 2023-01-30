<?php

/**
 * Overwrite OpenPayU_HttpCurl class from openpayu library
 */
class OpenPayU_HttpCurl
{
    public static array $history;

    public static function addResponse(int $responseCode, string $responseContent): void
    {
        self::$history = ['responseCode' => $responseCode, 'responseContent' => $responseContent];
    }

    /**
     * @param AuthType $auth
     *
     * @return array
     *
     * @throws OpenPayU_Exception_Configuration
     * @throws OpenPayU_Exception_Network
     */
    public static function doPayuRequest($requestType, $pathUrl, $auth, $data = null)
    {
        if (empty($pathUrl)) {
            throw new OpenPayU_Exception_Configuration('The endpoint is empty');
        }

        $response = ['code' => self::$history['responseCode'], 'response' => trim(self::$history['responseContent'])];
        self::$history = [];

        return $response;
    }

    /**
     * @param array $headers
     *
     * @return mixed
     */
    public static function getSignature($headers)
    {
        foreach ($headers as $name => $value) {
            if (preg_match('/X-OpenPayU-Signature/i', $name) || preg_match('/OpenPayu-Signature/i', $name)) {
                return $value;
            }
        }

        return null;
    }
}
