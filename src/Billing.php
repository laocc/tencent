<?php

namespace laocc\tencent;

class Billing extends _Base
{
    protected string $product = 'billing';//当前产品名称，在各类中自行指定
    protected string $domain = 'billing.tencentcloudapi.com';
    protected string $version = '2018-07-09';

    /**
     * @return array|string
     */
    public function balance(): array|string
    {
        $send = $this->request('DescribeAccountBalance', []);
        if (is_string($send)) return $send;

        return [
            'account' => $send['Uin'],//账户ID
            'balance' => $send['RealBalance'],//余额
            'cache' => $send['CashAccountBalance'],//现金
            'credit' => $send['RealCreditBalance'],//信用
            'freeze' => $send['FreezeAmount'],//冻结
        ];
    }
}