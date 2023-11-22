<?php

use GuzzleHttp\Client;

include_once './vendor/autoload.php';


function getPackageData(int $page = 0, string $tag = 'laravel')
{
    $applicationId = 'M58222SH95';
    $applicationKey = '5ae4d03c98685bd7364c2e0fd819af05';
    $uri = 'https://m58222sh95-dsn.algolia.net/1/indexes/*/queries';

    $client = new Client([
        'base_uri' => sprintf('%s?x-algolia-application-id=%s&x-algolia-api-key=%s', $uri, $applicationId, $applicationKey),
    ]);
    try {
        $response = $client->post('', [
            'json' => [
                'requests' => [
                    [
                        'indexName' => 'packagist',
                        'params' => 'query=&maxValuesPerFacet=100&page=' . $page . '&facets=%5B%22tags%22%2C%22type%22%2C%22type%22%5D&tagFilters=&facetFilters=%5B%5B%22tags%3A' . $tag . '%22%5D%5D',
                    ],
                ],
            ],
        ]);
        $result = json_decode($response->getBody()->getContents(), true);
        if (isset($result['results'][0]['hits'])) {
            return $result['results'][0]['hits'];
        }
        throw new \Exception('获取数据失败');
    } catch (\Throwable $e) {
        var_dump($e->getMessage());
        return [];
    }

}

try {
    $header = [
        '排名', '包名', '描述', '下载量', '星星数', '仓库地址'
    ];
    // 格式化为 Markdown 表格
    $table = '| ' . implode(' | ', $header) . " |\n";
    $table .= '| ' . str_repeat('--- | ', count($header)) . "\n";
    $top = 1;
    for ($i = 1; $i <= 10; $i++) {
        $result = getPackageData($i);
        foreach ($result as $hit) {
            $name = $hit['name'];
            //排除以laravel-开头的包
            if (str_starts_with($name, 'laravel/')) {
                continue;
            }
            $table .= sprintf(
                '| %s | [%s](%s) | %s | %s | %s | %s |' . PHP_EOL,
                $top,
                $hit['name'],
                $hit['repository'],
                str_replace('|', '\|', $hit['description']), // 防止描述中有 | 导致表格格式错误
                $hit['meta']['downloads'],
                $hit['meta']['favers'],
                $hit['repository'],
            );
            $top++;
            if ($top > 100) {
                break;
            }
        }
        if ($top > 100) {
            break;
        }
        sleep(1);
    }
    // 写入到 MD 文件
    $filename = 'README.md';
    $file = fopen($filename, 'w');
    fwrite($file, $table);
    fclose($file);

    echo "数据已保存到 $filename";
} catch (\Throwable $e) {
    var_dump($e->getMessage());
}


