<?php

use EasySwoole\Spl\SplBean;
use Hyperf\WebSocketClient\ClientFactory;
use Swoole\Runtime;

require_once "./vendor/autoload.php";

use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

class Command extends SplBean{
    protected $method;
    protected $id;
    protected $params;
    protected function initialize(): void
    {
        if(empty($this->id)){
            global $i;
            $i++;
            $this->id = $i;
        }
    }
}

run(function () {
    // 对端服务的地址，如没有提供 ws:// 或 wss:// 前缀，则默认补充 ws://
    $host = 'ws://0.0.0.0:9222/devtools/browser/bbb73544-726d-4bc2-86e0-1596f6be8e69';

    // 通过 ClientFactory 创建 Client 对象，创建出来的对象为短生命周期对象
    $client = \FriendsOfHyperf\Helpers\di(ClientFactory::class)->create($host);

    $targetUrl = 'https://datacenter.jin10.com/price';

    //打开URL
    $command = new Command([
        'method'=>'Target.createTarget',
        'params'=>[
            'url'=>$targetUrl
        ]
    ]);
    $client->push($command->__toString());
    $targetId = json_decode($client->recv(1), true);
    $targetId = $targetId['result']['targetId'];

    $command = new Command([
        'method'=>'Target.activateTarget',
        'params'=>[
            'targetId'=>$targetId
        ]
    ]);
    $client->push($command->__toString());
    $client->recv(1);


    // //模拟等待渲染
    sleep(2);
    // //实现 js 语句
    $command = new Command([
        'method'=>'Runtime.evaluate',
        'params'=>[
            'expression'=>"var p = 'test';p;"
        ]
    ]);
    $client->push($command->__toString());
    //此处就可以得到渲染后的数据了
    $data = json_decode($client->recv(),true);
    var_dump($data);

});