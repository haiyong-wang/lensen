<?php

namespace CjDropshipping;

use CjDropshipping\Auth\Authentication;
use CjDropshipping\Services\ProductService;
use CjDropshipping\Services\OrderService;
use CjDropshipping\Http\CurlClient;
use CJDropshipping\Logger\Logger;

class CjDropshippingSDK
{
    private $client;
    private $auth;
    private $services = [];
    private $baseUri = 'https://developers.cjdropshipping.com/api2.0/v1/';

    /**
     * 初始化CJ Dropshipping SDK
     * 
     * @param string $accessToken 访问令牌（可选）
     * @param array $options Guzzle客户端额外配置选项
     * @param bool $verifySSL 是否验证SSL证书
     */
    public function __construct($accessToken = null, $options = [], $verifySSL = false, Logger $logger = null)
    {
        // 默认配置
        $defaultOptions = [
            'base_uri' => $this->baseUri,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ];

        // 合并用户自定义配置
        $clientOptions = array_merge($defaultOptions, $options);
        
        // 创建Guzzle客户端实例
        $this->client = new CurlClient($clientOptions);

        // 设置日志记录器
        $this->logger = $logger;
        if ($this->logger) {
            $this->client->setLogger($logger);
        }

        
        // 初始化认证服务（不需要API密钥和密钥）
        $this->auth = new Authentication($this->client);
        
        // 如果提供了access token，设置它
        if ($accessToken) {
            $this->auth->setAccessToken($accessToken);
        }
    }

    /**
     * 设置日志记录器
     * 
     * @param Logger $logger
     * @return self
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        $this->client->setLogger($logger);
        $this->auth->setLogger($logger);
        return $this;
    }


     /**
     * 获取日志记录器
     * 
     * @return Logger|null
     */
    public function getLogger()
    {
        return $this->logger;
    }


    /**
     * 使用API密钥和密钥进行认证
     * 
     * @param string $apiKey API密钥
     * @param string $apiSecret API密钥
     * @return string 访问令牌
     * @throws \Exception 当认证失败时抛出异常
     */
    public function authenticate($apiKey, $apiSecret)
    {
        if (empty($apiKey) || empty($apiSecret)) {
            throw new InvalidArgumentException('API key and secret are required for authentication');
        }
        
        return $this->auth->authenticateWithCredentials($apiKey, $apiSecret);
    }

    /**
     * 直接设置访问令牌
     * 
     * @param string $accessToken 访问令牌
     * @param int $expiresIn 过期时间（秒）
     * @return self
     */
    public function setAccessToken($accessToken, $expiresIn = 3600)
    {
        $this->auth->setAccessToken($accessToken, $expiresIn);
        return $this;
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
     * 获取产品服务实例（延迟初始化）
     * 
     * @return ProductService
     */
    public function products()
    {
        if (!isset($this->services['products'])) {
            $this->services['products'] = new ProductService($this->client, $this->auth);
        }
        return $this->services['products'];
    }

    /**
     * 获取订单服务实例（延迟初始化）
     * 
     * @return OrderService
     */
    public function orders()
    {
        if (!isset($this->services['orders'])) {
            $this->services['orders'] = new OrderService($this->client, $this->auth);
        }
        return $this->services['orders'];
    }


    /**
     * 获取HTTP客户端实例
     * 
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * 设置SSL验证
     * 
     * @param bool $verify 是否验证SSL
     * @return self
     */
    public function setSSLVerification($verify)
    {
        // 获取当前配置
        $config = $this->client->getConfig();
        
        // 更新SSL验证设置
        $config['verify'] = (bool)$verify;
        
        // 创建新的客户端实例
        $this->client = new Client($config);
        
        // 更新认证服务的客户端引用
        $this->auth->setClient($this->client);
        
        // 重置所有服务实例，下次调用时会重新初始化
        $this->services = [];
        
        return $this;
    }

    /**
     * 设置请求超时时间
     * 
     * @param int $timeout 超时时间（秒）
     * @return self
     */
    public function setTimeout($timeout)
    {
        // 获取当前配置
        $config = $this->client->getConfig();
        
        // 更新超时设置
        $config['timeout'] = (int)$timeout;
        
        // 创建新的客户端实例
        $this->client = new Client($config);
        
        // 更新认证服务的客户端引用
        $this->auth->setClient($this->client);
        
        // 重置所有服务实例，下次调用时会重新初始化
        $this->services = [];
        
        return $this;
    }

    /**
     * 设置基础URI
     * 
     * @param string $baseUri 基础URI
     * @return self
     */
    public function setBaseUri($baseUri)
    {
        // 获取当前配置
        $config = $this->client->getConfig();
        
        // 更新基础URI
        $config['base_uri'] = $baseUri;
        
        // 创建新的客户端实例
        $this->client = new Client($config);
        
        // 更新认证服务的客户端引用
        $this->auth->setClient($this->client);
        
        // 重置所有服务实例，下次调用时会重新初始化
        $this->services = [];
        
        return $this;
    }

    /**
     * 检查是否有有效的访问令牌
     * 
     * @return bool
     */
    public function hasValidToken()
    {
        return $this->auth->isTokenValid();
    }
}