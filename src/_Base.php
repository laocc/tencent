<?php
declare(strict_types=1);

namespace laocc\tencent;

use esp\core\Library;
use esp\http\Http;
use function esp\core\esp_error;

class _Base extends Library
{
    protected array $conf = [];
    protected string $keyID = '';
    protected string $secret = '';
    protected string $product = '';//当前产品名称，在各类中自行指定
    protected string $region = '';
    protected string $version = '';
    protected string $domain = '';

    public function _init(array $option = [])
    {
        $this->conf = $option;
        $this->keyID = ($option['id'] ?? ($option['appid'] ?? ''));
        $this->secret = ($option['key'] ?? ($option['appkey'] ?? ($option['secret'] ?? '')));
    }

    public function setRegion(string $value): _Base
    {
        $this->region = $value;
        return $this;
    }

    public function setVersion(string $value): _Base
    {
        $this->version = $value;
        return $this;
    }

    public function setDomain(string $value): _Base
    {
        $this->domain = $value;
        return $this;
    }

    public function request(string $action, array $postData)
    {
        $json = json_encode($postData, 320);

        $option = [];
        $option['encode'] = 'json';
        $option['decode'] = 'json';
        $option['headers'] = [];
        $option['headers']['Content-Type'] = 'application/json';
        $option['headers']['Host'] = $this->domain;
        $option['headers']['X-TC-Action'] = $action;
        $option['headers']['X-TC-Region'] = $this->region;
        $option['headers']['X-TC-Version'] = $this->version;
        $option['headers']['X-TC-Timestamp'] = time();
        $option['headers']['X-TC-Language'] = 'zh-CN';
        $option['headers']['Authorization'] = $this->signature($option, $json);

        $http = new Http($option);
        $request = $http->data($json)->post("https://{$this->domain}/");
        $response = $request->data('Response');
        if ($err = ($response['Error'] ?? null)) return $err['Message'];
        return $response;
    }

    /**
     * 签名Authorization说明见：https://cloud.tencent.com/document/api/1340/52690
     */
    protected function signature(array $option, string $json): string
    {
        $date = gmdate('Y-m-d', $option['headers']['X-TC-Timestamp']);

        $reqs = [];
        $reqs[0] = 'POST';
        $reqs[1] = '/';//URI 参数，API 3.0 固定为正斜杠（/）。
        $reqs[2] = '';//发起 HTTP 请求 URL 中的查询字符串，对于 POST 请求，固定为空字符串""，对于 GET 请求，则为 URL 中问号（?）后面的字符串内容，例如：Limit=10&Offset=0。
        $reqs[3] = "content-type:{$option['headers']['Content-Type']}\nhost:{$option['headers']['Host']}\n";
        $reqs[4] = 'content-type;host';
        $reqs[5] = hash("SHA256", $json);

        $signArr = [];
        $signArr[0] = 'TC3-HMAC-SHA256';
        $signArr[1] = $option['headers']['X-TC-Timestamp'];
        $signArr[2] = "{$date}/{$this->product}/tc3_request";
        $signArr[3] = hash("SHA256", implode("\n", $reqs));
        var_dump($this->keyID);
        var_dump($this->secret);

        $sign = $this->signTC3($this->secret, $date, $this->product, implode("\n", $signArr));

        return "{$signArr[0]} Credential={$this->keyID}/{$signArr[2]}, SignedHeaders={$reqs[4]}, Signature={$sign}";
    }


    /**
     * V3签名
     *
     * https://cloud.tencent.com/document/api/1340/52691
     *
     * @param string $key
     * @param string $date
     * @param string $service
     * @param string $str2sign
     * @return false|string
     */
    public function signTC3(string $key, string $date, string $service, string $str2sign)
    {
        $dateKey = hash_hmac("SHA256", $date, "TC3" . $key, true);
        $serviceKey = hash_hmac("SHA256", $service, $dateKey, true);
        $reqKey = hash_hmac("SHA256", "tc3_request", $serviceKey, true);
        return hash_hmac("SHA256", $str2sign, $reqKey);
    }

    public function signV2(string $secretKey, string $signStr, string $signMethod)
    {
        $signMethodMap = ["HmacSHA1" => "SHA1", "HmacSHA256" => "SHA256"];
        if (!array_key_exists($signMethod, $signMethodMap)) esp_error('signMethod only support (HmacSHA1, HmacSHA256)');
        return base64_encode(hash_hmac($signMethodMap[$signMethod], $signStr, $secretKey, true));
    }


}