<?php

namespace CjDropshipping;

use CjDropshipping\Auth\Authentication;
use CjDropshipping\Services\ProductService;
use CjDropshipping\Services\OrderService;
use GuzzleHttp\Client;

class CjDropshippingSDK
{
    private $client;
    private $auth;
    private $productService;
    private $orderService;
    private $baseUri = 'https://developers.cjdropshipping.com/api2.0/v1/';

    /**
     * 初始化CJ Dropshipping SDK
     * 
     * @param string $apiKey API密钥
     * @param string $apiSecret API密钥
     * @param array $options Guzzle客户端额外配置选项
     */
    public function __construct($apiKey, $apiSecret, $options = [])
    {
        // 默认配置
        $defaultOptions = [
            'base_uri' => $this->baseUri,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'verify' => false
        ];

        // 合并用户自定义配置
        $clientOptions = array_merge($defaultOptions, $options);
        
        // 创建Guzzle客户端实例
        $this->client = new Client($clientOptions);
        
        // 初始化认证服务
        $this->auth = new Authentication($this->client, $apiKey, $apiSecret);
        
        // 初始化各服务
        $this->productService = new ProductService($this->client, $this->auth);
        $this->orderService = new OrderService($this->client, $this->auth);
    }

    /**
     * 获取认证服务实例
     * 
     * @return Authentication
     */
    public function auth()
    {
        return $this->auth;
    }

    /**
     * 获取产品服务实例
     * 
     * @return ProductService
     */
    public function products()
    {
        return $this->productService;
    }

    /**
     * 获取订单服务实例
     * 
     * @return OrderService
     */
    public function orders()
    {
        return $this->orderService;
    }
}