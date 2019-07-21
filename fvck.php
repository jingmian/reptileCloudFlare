<?php

require 'vendor/autoload.php';

use liesauer\SimpleHttpClient;

/**
 * README FIRST!!!
 * 这段 demo 是针对 CloudFlare 的5秒盾
 * 对于国内的那些，代码可能要稍作修改
 * 比如 $needValid 参数的匹配
 * 以及后面几个字段、js代码的匹配等
 */

// 这里一定要填完整的协议，以及最后一定要加 “/”
// 因为后面要匹配域名
$url = 'https://free-ss.ooo/';

// 过浏览器5秒检查

$cookie = '';

function runJSCode($code)
{
    $jsExecTimeout = 5;
    $token         = md5(microtime(true) . mt_rand());
    $returned      = '';

    $chArr = [];
    {
        $ch = curl_init("https://sandbox.tool.lu/tail/{$token}");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use ($token, &$returned) {
            if (strpos($data, $token) !== false) {
                preg_match("/{$token}(.*){$token}/ms", $data, $matches);
                @preg_match("/data:(.*)\s/", $matches[1], $matches);
                @$returned = $matches[1];
                return false;
            }
            return strlen($data);
        });
        $chArr[] = $ch;
    }
    {
        $ch = curl_init("https://sandbox.tool.lu/run/{$token}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'code'     => "console.log('{$token}');{$code};console.log('{$token}');",
            'language' => 'js',
        ]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $chArr[] = $ch;
    }

    $mh = curl_multi_init();

    curl_multi_add_handle($mh, $chArr[0]);

    $time = microtime(true);
    do {
        $mrc = curl_multi_exec($mh, $running);
        usleep(50 * 1000);
    } while (microtime(true) < $time + $jsExecTimeout);

    curl_multi_add_handle($mh, $chArr[1]);

    // foreach ($chArr as $k => $ch) {
    //     curl_multi_add_handle($mh, $ch);
    //     $mrc = curl_multi_exec($mh, $running);
    // }

    $running = null;
    do {
        $mrc = curl_multi_exec($mh, $running);
        usleep(10 * 1000);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    $time = microtime(true);
    while (microtime(true) < $time + $jsExecTimeout && $running && $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do {
                $mrc = curl_multi_exec($mh, $running);
                usleep(10 * 1000);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    foreach ($chArr as $k => $ch) {
        $result[$k] = curl_multi_getcontent($ch);
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);
    return $returned;
}

$response = SimpleHttpClient::quickGet($url, [
    'accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
    'accept-encoding'           => '',
    'accept-language'           => 'zh-CN,zh;q=0.9',
    'upgrade-insecure-requests' => '1',
    'user-agent'                => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
], $cookie, null, [
    CURLOPT_TIMEOUT => 10,
]);

$html = $response['data'];

$needValid = strpos($response['data'], 'Please allow up to 5 seconds') !== false;

if ($needValid) {
    $cookie = getMiddleText($response['header'], '__cfduid=', ';', 0, $pos, INCLUDING_BOTH);

    $s        = getMiddleText($html, 'name="s" value="', '">', 0, $pos);
    $jschl_vc = getMiddleText($html, 'name="jschl_vc" value="', '"/>', $pos, $pos);
    $pass     = getMiddleText($html, 'name="pass" value="', '"/>', $pos, $pos);

    $domain = getMiddleText($url, '://', '/');

    preg_match('/var s,t,o,p,b,r,e,a,k,i,n,g,f, (.*?);/m', $html, $matches);
    $code = $matches[1];

    $code .= ';';

    preg_match("/challenge-form'\\);\s+;(.*?);\s/m", $html, $matches);

    $code .= $matches[1];

    $code = str_replace(['a.value = ', 't.length'], ['console.log(', strlen($domain)], $code) . ')';

    $jschl_answer = runJSCode($code);

    sleep(5);

    $response = SimpleHttpClient::quickGet("{$url}cdn-cgi/l/chk_jschl", [
        'accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'accept-encoding'           => '',
        'accept-language'           => 'zh-CN,zh;q=0.9',
        'upgrade-insecure-requests' => '1',
        'user-agent'                => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
    ], $cookie, http_build_query([
        's'            => $s,
        'jschl_vc'     => $jschl_vc,
        'pass'         => $pass,
        'jschl_answer' => $jschl_answer,
    ]), [
        CURLOPT_TIMEOUT => 10,
    ]);

    $cookie .= getMiddleText($response['header'], 'cf_clearance=', ';', 0, $pos, INCLUDING_BOTH);

    $response = SimpleHttpClient::quickGet($url, [
        'accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'accept-encoding'           => '',
        'accept-language'           => 'zh-CN,zh;q=0.9',
        'upgrade-insecure-requests' => '1',
        'user-agent'                => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
    ], $cookie, null, [
        CURLOPT_TIMEOUT => 10,
    ]);
}

echo "过浏览器5秒检查后的Response：<br />\n";

echo "<pre>" . print_r($response, true) . "</pre>";
