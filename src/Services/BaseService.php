<?php

namespace CjDropshipping\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use CjDropshipping\Auth\Authentication;
use CjDropshipping\Exceptions\ApiException;

abstract class BaseService
{
    protected $client;
    protected $auth;

    public function __construct(Client $client, Authentication $auth)
    {
        $this->client = $client;
        $this->auth = $auth;
    }

    /**
     * 统一发送授权请求到CJ Dropshipping API
     * 
     * @param string $method HTTP方法
     * @param string $endpoint API端点
     * @param array $data 请求数据
     * @return array API响应
     * @throws ApiException 当API请求失败时抛出异常
     */
    protected function request($method, $endpoint, $data = [])
    {
        try {
            $options = [
                'headers' => $this->auth->getAuthHeaders()
            ];

            // 根据请求方法添加数据
            if (!empty($data)) {
                if ($method === 'GET') {
                    $options['query'] = $data;
                } else {
                    $options['json'] = $data;
                }
            }

            // 发送请求
            $response = $this->client->request($method, $endpoint, $options);
            
            // 解析响应
            $body = (string) $response->getBody();
            $result = json_decode($body, true);

            // 检查JSON解析错误
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApiException('Failed to parse JSON response: ' . json_last_error_msg());
            }

            return $result;
        } catch (RequestException $e) {
            // 处理Guzzle请求异常
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                
                // 如果是认证错误，清除token并重试
                if ($statusCode === 401) {
                    $this->auth->clearToken();
                    return $this->request($method, $endpoint, $data);
                }
                
                $body = (string) $response->getBody();
                $error = json_decode($body, true) ?? $body;
                
                throw new ApiException("API request failed with status $statusCode: " . print_r($error, true));
            } else {
                throw new ApiException("API request failed: " . $e->getMessage());
            }
        }
    }

    /**
     * GET请求快捷方法
     * 
     * @param string $endpoint API端点
     * @param array $params 查询参数
     * @return array
     */
    protected function get($endpoint, $params = [])
    {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * POST请求快捷方法
     * 
     * @param string $endpoint API端点
     * @param array $data 请求数据
     * @return array
     */
    protected function post($endpoint, $data = [])
    {
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * PUT请求快捷方法
     * 
     * @param string $endpoint API端点
     * @param array $data 请求数据
     * @return array
     */
    protected function put($endpoint, $data = [])
    {
        return $this->request('PUT', $endpoint, $data);
    }

    /**
     * DELETE请求快捷方法
     * 
     * @param string $endpoint API端点
     * @param array $data 请求数据
     * @return array
     */
    protected function delete($endpoint, $data = [])
    {
        return $this->request('DELETE', $endpoint, $data);
    }
}