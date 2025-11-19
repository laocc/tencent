<?php

namespace laocc\tencent;

class Billing extends _Base
{
    protected string $product = 'billing';//当前产品名称，在各类中自行指定
    protected string $domain = 'billing.tencentcloudapi.com';
    protected string $version = '2018-07-09';

    public function balance()
    {
        $data = [];
        $send = $this->request('DescribeAccountBalance', $data);
        if (is_string($send)) return $send;

        return $send;
    }
}