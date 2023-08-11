<?php

namespace laocc\tencent;

use \Redis;

class Sms extends _Base
{
    protected string $product = 'sms';//当前产品名称，在各类中自行指定
    protected string $domain = 'sms.tencentcloudapi.com';
    protected string $version = '2021-01-11';
    protected string $region = 'ap-nanjing';
    private string $prefix = 'message';
    private Redis $redis;

    public function setRedis(Redis $redis): Sms
    {
        $this->redis = $redis;
        return $this;
    }

    public function send(array $conf)
    {
        $code = $this->createSignCode($conf['mobile'], $conf['ttl'] ?? 10, $conf['len'] ?? 4);
        if ($conf['debug'] ?? 0) return $code;

        $data = [];
        $data['PhoneNumberSet'] = [$conf['mobile']];
        $data['SmsSdkAppId'] = $conf['sdk'] ?? '';//1400265064
        $data['TemplateId'] = $conf['id'] ?? '';
        $data['SignName'] = $conf['sign'] ?? '';
        $data['TemplateParamSet'] = [$code];

        $send = $this->request('SendSms', $data);
        if (is_string($send)) return $send;

        $send['sms_code'] = $code;
        return $send;
    }

    private function createSignCode(string $mobile, int $ttl = 15, int $len = 4): string
    {
        $code = mt_rand(intval('1' . str_repeat('0', $len - 1)), intval(str_repeat('9', $len)));
        $this->redis->set("{$this->prefix}_{$mobile}", $code, $ttl * 60);
        return strval($code);
    }


    public function check(string $mobile, int $code, int $len)
    {
        if (!$code or strlen(strval($code)) !== $len) {
            return "请输入{$len}位数验证码";
        }
        $sCode = $this->redis->get("{$this->prefix}_{$mobile}");
        if (!$sCode) return '验证码校验失败，请重新发送验证码';
        if (intval($sCode) !== $code) return '验证码错误';
        $this->redis->del("{$this->prefix}_{$mobile}");
        return true;
    }

    public function delete($mobile)
    {
        return $this->redis->del("{$this->prefix}_{$mobile}");
    }

    public function setPrefix(string $prefix): Sms
    {
        $this->prefix = $prefix;
        return $this;
    }

}