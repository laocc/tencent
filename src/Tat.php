<?php

namespace laocc\tencent;

use esp\http\Http;

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


    /**
     * 创建命令，创建过的命令在控制台【自动化助手】【我的命令】中可以看到
     */
    public function create(array $option)
    {
        $params = [];
        $params['Action'] = 'CreateCommand';
        $params['Region'] = $option['region'] ?? 'ap-shanghai';

        $data = [];
        $data['CommandName'] = $option['name'];//命令名称
        $data['Content'] = base64_encode($option['command']);
        $data['Description'] = $option['desc'] ?? '';//描述
        $data['CommandType'] = 'SHELL';
        $data['WorkingDirectory'] = '/root';
        $data['Timeout'] = intval($option['timeout'] ?? 10);//秒
        $data['Username'] = 'root';

        return $this->request($params, $data);
    }

    public function delete(array $option)
    {
        $params = [];
        $params['Action'] = 'DeleteCommand';
        $params['Region'] = $option['region'] ?? 'ap-shanghai';

        $data = [];
        $data['CommandId'] = $option['id'];

        $send = $this->request($params, $data);
        if (is_string($send)) return $send;
        return true;
    }

    /**
     * 触发已创建的命令
     */
    public function run(array $option)
    {
        $params = [];
        $params['Action'] = 'InvokeCommand';
        $params['Region'] = $option['region'] ?? 'ap-shanghai';

        $data = [];
        $data['CommandId'] = $option['cmd_id'];
        $data['InstanceIds'] = $option['serv_id'];
        if (is_string($data['InstanceIds'])) $data['InstanceIds'] = [$data['InstanceIds']];

        $send = $this->request($params, $data);
        if (is_string($send)) return $send;
        return $send;
    }


    /**
     * 直接执行命令
     */
    public function exec(array $option)
    {
        $params = [];
        $params['Action'] = 'RunCommand';
        $params['Region'] = $option['region'] ?? 'ap-shanghai';

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

        $send = $this->request($params, $data);
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
        $params = [];
        $params['Action'] = 'DescribeInvocations';
        $params['Region'] = $option['region'] ?? 'ap-shanghai';

        $data = [];
        $data['InvocationIds'] = $option['run_id'];
        if (is_string($data['InvocationIds'])) $data['InvocationIds'] = [$data['InvocationIds']];

        $send = $this->request($params, $data);
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

    private function request(array $params, array $postData)
    {
        $json = json_encode($postData, 320);

        $option = [];
        $option['encode'] = 'json';
        $option['decode'] = 'json';
        $option['headers'] = [];
        $option['headers']['Content-Type'] = 'application/json';
        $option['headers']['Host'] = 'tat.tencentcloudapi.com';
        $option['headers']['X-TC-Action'] = $params['Action'];
        $option['headers']['X-TC-Region'] = $params['Region'] ?? 'ap-shanghai';
        $option['headers']['X-TC-Version'] = '2020-10-28';
        $option['headers']['X-TC-Timestamp'] = time();
        $option['headers']['X-TC-Language'] = 'zh-CN';
        $option['headers']['Authorization'] = $this->signature($option, $json);

        $http = new Http($option);
        $request = $http->data($json)->post('https://tat.tencentcloudapi.com/');
        $response = $request->data('Response');
        if ($err = ($response['Error'] ?? null)) return $err['Message'];
        return $response;
    }


}