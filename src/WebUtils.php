<?php

namespace Weskiller\ICBC_APi_SDK;

use Exception;
use GuzzleHttp\Client;

class WebUtils
{
    private static $version = "v2_20170324";

    /** @var Client  */
    private static $client;

    public static function doGet($url, $params, $charset)
    {
        $response = self::getHttpClient()->get($url,[
            'timeout' => 3,
            'query' => $params,
            'headers' => [
                IcbcConstants::$VERSION_HEADER_NAME => self::$version,
            ]
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new Exception("response status code is not valid. status code: " . $response->getStatusCode());
        }
    }

    public static function buildGetUrl($strUrl, $params, $charset)
    {
        if ($params == null || count($params) == 0) {
            return $strUrl;
        }
        $buildUrlParams = http_build_query($params);
        if (strrpos($strUrl, '?', 0) != (strlen($strUrl) + 1)) { //最后是否以？结尾
            return $strUrl . '?' . $buildUrlParams;
        }
        return $strUrl . $buildUrlParams;
    }

    public static function doPost($url, $params, $charset)
    {
        $response = self::getHttpClient()->post($url, [
            'timeout' => 3,
            'form' => $params,
            'headers' => [
                "Expect:" => '',
                IcbcConstants::$VERSION_HEADER_NAME => self::$version,
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new Exception("response status code is not valid. status code: " . $response->getStatusCode());
        }
        return (string) $response->getBody();
    }

    public static function buildOrderedSignStr($path, $params)
    {
        $isSorted = ksort($params);
        $comSignStr = $path . '?';

        $hasParam = false;
        foreach ($params as $key => $value) {
            if (null == $key || "" == $key || null == $value || "" == $value) {
            } else {
                if ($hasParam) {
                    $comSignStr = $comSignStr . '&';
                } else {
                    $hasParam = true;
                }
                $comSignStr = $comSignStr . $key . '=' . $value;
            }
        }

        return $comSignStr;
    }

    public static function buildForm($url, $params)
    {
        $buildedFields = self::buildHiddenFields($params);
        return '<form name="auto_submit_form" method="post" action="' . $url . '">' . "\n" . $buildedFields . '<input type="submit" value="立刻提交" style="display:none" >' . "\n" . '</form>' . "\n" . '<script>document.forms[0].submit();</script>';
    }

    public static function buildHiddenFields($params)
    {
        if ($params == null || count($params) == 0) {
            return '';
        }

        $result = '';
        foreach ($params as $key => $value) {
            if ($key == null || $value == null) {
                continue;
            }
            $buildfield = self::buildHiddenField($key, $value);
            $result = $result . $buildfield;
        }
        return $result;
    }

    public static function buildHiddenField($key, $value)
    {
        return '<input type="hidden" name="' . $key . '" value="' . preg_replace('/"/', '&quot;', $value) . '">' . "\n";
    }

    /**
     * @return Client
     */
    public static function getHttpClient()
    {
        if(self::$client === null) {
            self::setHttpClient($client = new Client());
        }
        return self::$client;
    }

    /**
     * @param Client $client
     */
    public static function setHttpClient($client)
    {
        self::$client = $client;
    }
}

?>