<?php

namespace laocc\tencent;


/**
 * 在腾讯云服务器执行命令，自动化服务
 * 官网：https://cloud.tencent.com/product/tat
 * API：https://cloud.tencent.com/document/product/1340/52696
 * api可视化执行：https://console.cloud.tencent.com/api/explorer?Product=tat&Version=2020-10-28&Action=CancelInvocation
 *
 * 这里只有命令相关操作，执行器暂不支持
 *
 * 非腾讯云服务器，可以在腾讯云后台注册托管后，也可以用此脚本管理
 *
 */
class Tat extends _Base
{
    protected string $product = 'tat';//当前产品名称，在各类中自行指定
    protected string $domain = 'tat.tencentcloudapi.com';
    protected string $version = '2020-10-28';
    protected string $region = 'ap-shanghai';//默认地区


    /**
     * 创建命令，创建过的命令在控制台【自动化助手】【我的命令】中可以看到
     */
    public function create(array $option)
    {
        $data = [];
        $data['CommandName'] = $option['name'];//命令名称
        $data['Content'] = base64_encode($option['command']);
        $data['Description'] = $option['desc'] ?? '';//描述
        $data['CommandType'] = 'SHELL';
        $data['WorkingDirectory'] = '/root';
        $data['Timeout'] = intval($option['timeout'] ?? 10);//秒
        $data['Username'] = 'root';

        return $this->request('CreateCommand', $data);
    }

    public function delete(array $option)
    {
        $data = [];
        $data['CommandId'] = $option['id'];

        $send = $this->request('DeleteCommand', $data);
        if (is_string($send)) return $send;
        return true;
    }

    /**
     * 触发已创建的命令
     */
    public function run(array $option)
    {
        $data = [];
        $data['CommandId'] = $option['cmd_id'];
        $data['InstanceIds'] = $option['serv_id'];
        if (is_string($data['InstanceIds'])) $data['InstanceIds'] = [$data['InstanceIds']];

        $send = $this->request('InvokeCommand', $data);
        if (is_string($send)) return $send;
        return $send;
    }


    /**
     * 直接执行命令
     */
    public function exec(array $option)
    {
        $data = [];
        $data['CommandName'] = $option['name'];//命令名称
        $data['Content'] = base64_encode($option['command']);
        $data['Description'] = $option['desc'] ?? '';//描述
        $data['CommandType'] = 'SHELL';
        $data['WorkingDirectory'] = '/root';
        $data['Timeout'] = intval($option['timeout'] ?? 100);//秒
        $data['Username'] = 'root';
        $data['SaveCommand'] = $option['save'] ?? false;
        $data['InstanceIds'] = $option['serv_id'];
        if (is_string($data['InstanceIds'])) $data['InstanceIds'] = [$data['InstanceIds']];

        $send = $this->request('RunCommand', $data);
        if (is_string($send)) return $send;
        return $send;
    }

    /**
     * 查询命令执行结果
     *
     * @param array $option
     * @return array|mixed|string|null
     */
    public function query(array $option)
    {
        $data = [];
        $data['InvocationIds'] = $option['run_id'];
        if (is_string($data['InvocationIds'])) $data['InvocationIds'] = [$data['InvocationIds']];

        $send = $this->request('DescribeInvocations', $data);
        if (is_string($send)) return $send;
        return $send;
    }

    public function edit()
    {

    }

    public function logs()
    {

    }

    public function view()
    {

    }



}