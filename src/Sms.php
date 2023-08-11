<?php

namespace laocc\tencent;

use Qcloud\Sms\SmsSingleSender;

class Sms extends _Base
{
    protected string $product = 'sms';//当前产品名称，在各类中自行指定

    public function send(string $mobile, array $conf)
    {
        $code = $this->createSignCode($mobile, $conf['ttl'], $conf['len']);
        if (_DEBUG) return $code;

        $senderSMS = new SmsSingleSender($this->conf['appid'], $this->conf['appkey']);
        $params = [$code];

        $err = '';
        foreach (explode(';', $conf['sign']) as $sign) {
            $result = $senderSMS->sendWithParam("86", $mobile, $conf['id'], $params, $sign, "", "");
            $rest = json_decode($result, true);
            if ($rest['result'] === 0) return true;
            $err = $rest['errmsg'];
        }
        return $err;
    }

    public function check(string $mobile, int $code, int $len)
    {
        if (!$code or strlen(strval($code)) !== $len) {
            return "请输入{$len}位数验证码";
        }
        $sCode = $this->_controller->_redis->get("message_{$mobile}");
        if (!$sCode) return '验证码校验失败，请重新发送验证码';
        if (intval($sCode) !== $code) return '验证码错误';
        $this->_controller->_redis->del("message_{$mobile}");
        return true;
    }

    public function delete($mobile)
    {
        return $this->_controller->_redis->del("message_{$mobile}");
    }

    private function createSignCode(string $mobile, int $ttl = 15, int $len = 4): int
    {
        $code = mt_rand(intval('1' . str_repeat('0', $len - 1)), intval(str_repeat('9', $len)));
        $this->_controller->_redis->set("message_{$mobile}", $code, $ttl * 60);
        return $code;
    }

}