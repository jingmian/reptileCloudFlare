<?php

namespace liesauer;

class SimpleHttpClient
{
    public static function quickGet($url, $header = null, $cookie = '', $data = '', $options = null)
    {
        return self::quickRequest($url . (empty($data) ? '' : '?' . $data), 'GET', $header, $cookie, '', $options);
    }
    public static function quickPost($url, $header = null, $cookie = '', $data = '', $options = null)
    {
        return self::quickRequest($url, 'POST', $header, $cookie, $data, $options);
    }
    public static function quickRequest($url, $method, $header = null, $cookie = '', $data = '', $options = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        switch (strtoupper($method)) {
            case 'GET':
                //curl_setopt($ch,CURLOPT_HTTPGET,TRUE);
                break;
            default:
                if ($method === 'POST') {
                    curl_setopt($ch, CURLOPT_POST, true);
                }
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
                break;
        }
        if (count($header) >= 1) {
            foreach ($header as $key => &$value) {
                if (is_string($key)) {
                    $value = "{$key}: {$value}";
                }
            }
            unset($value);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        if (!empty($cookie)) {
            if (is_array($cookie)) {
                $keys    = array_keys($cookie);
                $values  = array_values($cookie);
                $counter = count($cookie);
                $cookies = [];
                for ($i = 0; $i < $counter; $i++) {
                    $key   = '';
                    $value = '';
                    if (is_string($keys[$i])) {
                        $key   = rawurlencode(trim($keys[$i]));
                        $value = rawurlencode(trim($values[$i]));
                    } else {
                        $parse_cookie = explode('=', $values[$i]);
                        if (isset($parse_cookie[0])) {
                            $key = trim($parse_cookie[0]);
                        }
                        if (isset($parse_cookie[1])) {
                            $value = trim($parse_cookie[1]);
                        }
                    }
                    $cookies[] = "{$key}={$value}";
                }
                $cookie = implode(';', $cookies);
            }
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (strtolower(substr($url, 0, 5)) === 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        if (count($options) >= 1) {
            curl_setopt_array($ch, $options);
        }

        $re = array(
            'http_code' => 0,
            'header'    => false,
            'data'      => false,
        );
        $re['data'] = curl_exec($ch);
        if ($re['data'] === false) {
            goto close;
        }
        $info = curl_getinfo($ch);
        // curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // 1048577
        // curl_getinfo($ch, CURLINFO_HTTP_CODE); // 2097154
        // curl_getinfo($ch, CURLINFO_FILETIME); // 2097166
        // curl_getinfo($ch, CURLINFO_TOTAL_TIME); // 3145731
        // curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME); // 3145732
        // curl_getinfo($ch, CURLINFO_CONNECT_TIME); // 3145733
        // curl_getinfo($ch, CURLINFO_PRETRANSFER_TIME); // 3145734
        // curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME); // 3145745
        // curl_getinfo($ch, CURLINFO_REDIRECT_TIME); // 3145747
        // curl_getinfo($ch, CURLINFO_SIZE_UPLOAD); // 3145735
        // curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD); // 3145736
        // curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD); // 3145737
        // curl_getinfo($ch, CURLINFO_SPEED_UPLOAD); // 3145738
        // curl_getinfo($ch, CURLINFO_HEADER_SIZE); // 2097163
        // curl_getinfo($ch, CURLINFO_HEADER_OUT); // 2
        // curl_getinfo($ch, CURLINFO_REQUEST_SIZE); // 2097164
        // curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT); // 2097165
        // curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD); // 3145743
        // curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_UPLOAD); // 3145744
        // curl_getinfo($ch, CURLINFO_CONTENT_TYPE); // 1048594

        $re['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $header_size     = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $all_size    = strlen($re['data']);
        $header_size = strpos($re['data'], "\r\n\r\n");
        if ($header_size) {
            $header_size += 4;
        }
        $re['header'] = substr($re['data'], 0, $header_size);
        $re['data']   = substr($re['data'], $header_size);
        if ($all_size === $header_size) {
            $re['data'] = '';
        }

        close:
        curl_close($ch);
        return $re;
    }
}
