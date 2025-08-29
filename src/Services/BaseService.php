<?php

namespace CjDropshipping\Services;

use CjDropshipping\Http\CurlClient;
use CjDropshipping\Auth\Authentication;
use CjDropshipping\Exceptions\ApiException;
use CJDropshipping\Logger\Logger;


abstract class BaseService
{
    protected $client;
    protected $auth;
    protected $logger;

    public function __construct(CurlClient $client, Authentication $auth, Logger $logger = null)
    {
        $this->client = $client;
        $this->auth = $auth;
        $this->logger = $logger;
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
        return $this;
    }


    /**
     * 统一发送授权请求到CJ Dropshipping API
     * 
     * @param string $method HTTP方法
     * @param string $endpoint API端点
     * @param array $data 请求数据
     * @param array $options 额外请求选项
     * @return array API响应
     * @throws ApiException 当API请求失败时抛出异常
     */
    protected function request($method, $endpoint, $data = [], $options = [])
    {
        try {
            // 获取认证头信息
            $authHeaders = $this->auth->getAuthHeaders();
            
            // 发送请求
            switch (strtoupper($method)) {
                case 'GET':
                    $response = $this->client->get($endpoint, $data, $authHeaders);
                    break;
                    
                case 'POST':
                    $response = $this->client->post($endpoint, $data, $authHeaders);
                    break;
                    
                case 'PUT':
                    $response = $this->client->put($endpoint, $data, $authHeaders);
                    break;
                    
                case 'DELETE':
                    $response = $this->client->delete($endpoint, $data, $authHeaders);
                    break;
                    
                default:
                    throw new ApiException("Unsupported HTTP method: $method");
            }

            // 记录API响应
            if ($this->logger) {
                $this->logger->info("API {$method} response", [
                    'endpoint' => $endpoint,
                    'response' => $response
                ]);
            }

            
            return $response;
        } catch (ApiException $e) {

            // 记录API异常
            if ($this->logger) {
                $this->logger->exception($e, [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'data' => $data
                ]);
            }
            
            // 如果是认证错误，清除token并抛出异常
            if (strpos($e->getMessage(), '401') !== false || 
                strpos($e->getMessage(), 'Authentication failed') !== false) {
                $this->auth->clearToken();
                throw new ApiException('Authentication failed: Invalid or expired access token');
            }
            
            // 重新抛出其他异常
            throw $e;
        }
    }

    /**
     * GET请求快捷方法
     * 
     * @param string $endpoint API端点
     * @param array $params 查询参数
     * @param array $options 额外请求选项
     * @return array
     */
    protected function get($endpoint, $params = [], $options = [])
    {
        return $this->request('GET', $endpoint, $params, $options);
    }

    /**
     * POST请求快捷方法
     * 
     * @param string $endpoint API端点
     * @param array $data 请求数据
     * @param array $options 额外请求选项
     * @return array
     */
    protected function post($endpoint, $data = [], $options = [])
    {
        return $this->request('POST', $endpoint, $data, $options);
    }

    /**
     * PUT请求快捷方法
     * 
     * @param string $endpoint API端点
     * @param array $data 请求数据
     * @param array $options 额外请求选项
     * @return array
     */
    protected function put($endpoint, $data = [], $options = [])
    {
        return $this->request('PUT', $endpoint, $data, $options);
    }

    /**
     * DELETE请求快捷方法
     * 
     * @param string $endpoint API端点
     * @param array $data 请求数据
     * @param array $options 额外请求选项
     * @return array
     */
    protected function delete($endpoint, $data = [], $options = [])
    {
        return $this->request('DELETE', $endpoint, $data, $options);
    }
}