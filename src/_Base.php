<?php
declare(strict_types=1);

namespace laocc\tencent;

use esp\core\Library;
use TencentCloud\Common\Exception\TencentCloudSDKException;

class _Base extends Library
{
    protected string $product = '';//当前产品名称，在各类中自行指定
    protected array $conf;

    public function _init(array $option = [])
    {
        $this->conf = $option;
    }


    /**
     * 签名Authorization说明见：https://cloud.tencent.com/document/api/1340/52690
     */
    protected function signature(array $option, string $json): string
    {
        $date = gmdate('Y-m-d', $option['headers']['X-TC-Timestamp']);

        $reqs = [];
        $reqs[0] = 'POST';
        $reqs[1] = '/';
        $reqs[2] = '';
        $reqs[3] = "content-type:{$option['headers']['Content-Type']}\nhost:{$option['headers']['Host']}\n";
        $reqs[4] = 'content-type;host';
        $reqs[5] = hash("SHA256", $json);

        $signArr = [];
        $signArr[0] = 'TC3-HMAC-SHA256';
        $signArr[1] = $option['headers']['X-TC-Timestamp'];
        $signArr[2] = "{$date}/{$this->product}/tc3_request";
        $signArr[3] = hash("SHA256", implode("\n", $reqs));

        $sign = $this->signTC3($this->conf['key'], $date, $this->product, implode("\n", $signArr));

        return "{$signArr[0]} Credential={$this->conf['id']}/{$signArr[2]}, SignedHeaders={$reqs[4]}, Signature={$sign}";
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
        if (!array_key_exists($signMethod, $signMethodMap)) {
            throw new TencentCloudSDKException("signMethod invalid", "signMethod only support (HmacSHA1, HmacSHA256)");
        }
        return base64_encode(hash_hmac($signMethodMap[$signMethod], $signStr, $secretKey, true));
    }


}