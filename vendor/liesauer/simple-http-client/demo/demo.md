
# 请求
目前仅支持GET/POST请求

## GET
```
SimpleHttpClient::quickGet('http://www.baidu.com');

```

### 查询拼接
```
SimpleHttpClient::quickGet('http://www.baidu.com',null,'',http_build_query([
	'wd'=>'simplehttpclient',
	'ie'=>'UTF-8',
	]));
// http://www.baidu.com?wd=simplehttpclient&ie=UTF-8
```


## POST
```
SimpleHttpClient::quickPost('http://www.xxxxx.com',null,'',[
	'field1'=>'value1',
	'field2'=>'value2',
]);
```

## 设置header
```
SimpleHttpClient::quickGet('http://www.baidu.com',[
	'Accept-Encoding'=>'gzip',
]);

// or

SimpleHttpClient::quickGet('http://www.baidu.com',[
	'Accept-Encoding: gzip',
]);
```

## 设置cookie
```
SimpleHttpClient::quickGet('http://www.baidu.com',[
	'Accept-Encoding'=>'gzip',
],[
	'BDUSS'=>'xxxxxxxxx',
]);
```

## 设置CURL
```
SimpleHttpClient::quickGet('http://www.baidu.com',[
	'Accept-Encoding'=>'gzip',
],[
	'BDUSS'=>'xxxxxxxxx',
],null,[
	CURLOPT_TIMEOUT=>5,
]);
```

# 响应
响应体的数据结构是数组形式的
如果http_code为0的话则代表请求不成功
```
[
	"http_code": int,
	"header": string,
	"data":   string,
]
```

示例：
```
Array
(
    [http_code] => 200
    [header] => HTTP/1.1 200 OK
Accept-Ranges: bytes
Cache-Control: no-cache
Connection: Keep-Alive
Content-Length: 14615
Content-Type: text/html
Date: Thu, 28 Jun 2018 11:28:22 GMT
Etag: "5b31dd68-3917"
Last-Modified: Tue, 26 Jun 2018 06:30:00 GMT
P3p: CP=" OTI DSP COR IVA OUR IND COM "
Pragma: no-cache
Server: BWS/1.1
Set-Cookie: BAIDUID=CFDA74C78FD0732F1EB0FCCED2BD9E18:FG=1; expires=Thu, 31-Dec-37 23:55:55 GMT; max-age=2147483647; path=/; domain=.baidu.com
Set-Cookie: BIDUPSID=CFDA74C78FD0732F1EB0FCCED2BD9E18; expires=Thu, 31-Dec-37 23:55:55 GMT; max-age=2147483647; path=/; domain=.baidu.com
Set-Cookie: PSTM=1530185302; expires=Thu, 31-Dec-37 23:55:55 GMT; max-age=2147483647; path=/; domain=.baidu.com
Vary: Accept-Encoding
X-Ua-Compatible: IE=Edge,chrome=1


    [data] => <!DOCTYPE html><!--STATUS OK-->........
)
```

# 举个栗子
```
require __DIR__ . '/vendor/autoload.php';

use liesauer\SimpleHttpClient;

$response = SimpleHttpClient::quickPost('https://github.com/session', [
    "Content-Type" => "application/x-www-form-urlencoded",
    "User-Agent"   => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
], [
    "tz" => 'Asia/Shanghai',
], http_build_query([
    "commit"             => 'Sign in',
    "utf8"               => '✓',
    "authenticity_token" => '',
    "login"              => '',
    "password"           => '',
]), [
    CURLOPT_TIMEOUT => 5,
    // CURLOPT_PROXY   => '127.0.0.1:8888', //Fiddler代理，方便调试查看构建发送的请求是否有问题
]);

var_dump($response);
```
