<?php

namespace CjDropshipping\Http;

use CjDropshipping\Exceptions\ApiException;

class CurlClient
{
    private $options;
    private $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    /**
     * 初始化cURL客户端
     * 
     * @param array $options 配置选项
     */
    public function __construct($options = [])
    {
        $this->options = array_merge([
            'base_uri' => '',
            'timeout' => 30,
            'headers' => [],
            'verify_ssl' => false
        ], $options);
    }

    /**
     * 设置选项
     * 
     * @param string $key 选项键名
     * @param mixed $value 选项值
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * 发送HTTP请求
     * 
     * @param string $method HTTP方法
     * @param string $url 请求URL
     * @param array $data 请求数据
     * @param array $headers 请求头
     * @return array 响应数据
     * @throws ApiException 当请求失败时抛出异常
     */
    public function request($method, $url, $data = [], $headers = [])
    {
        // 处理URL
        $fullUrl = $this->buildUrl($url);
        
        // 初始化cURL
        $ch = curl_init();
        
        // 设置cURL选项
        $this->setCurlOptions($ch, $method, $fullUrl, $data, $headers);
        
        // 执行请求
        $response = curl_exec($ch);
        
        // 检查错误
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new ApiException("cURL request failed: " . $error);
        }
        
        // 获取HTTP状态码
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // 关闭cURL
        curl_close($ch);
        
        // 解析响应
        $result = json_decode($response, true);
        
        // 检查JSON解析错误
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException('Failed to parse JSON response: ' . json_last_error_msg());
        }
        
        // 检查HTTP错误状态
        if ($httpCode >= 400) {
            throw new ApiException("API request failed with status $httpCode: " . print_r($result, true));
        }
        
        return $result;
    }

    /**
     * 构建完整URL
     * 
     * @param string $url 相对URL
     * @return string 完整URL
     */
    private function buildUrl($url)
    {
        $baseUri = rtrim($this->options['base_uri'], '/');
        $url = ltrim($url, '/');
        return $baseUri . '/' . $url;
    }

    /**
     * 设置cURL选项
     * 
     * @param resource $ch cURL句柄
     * @param string $method HTTP方法
     * @param string $url 完整URL
     * @param array $data 请求数据
     * @param array $headers 请求头
     */
    private function setCurlOptions($ch, $method, $url, $data, $headers)
    {
        // 基本选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->options['timeout']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        
        // SSL验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->options['verify_ssl']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->options['verify_ssl'] ? 2 : 0);
        
        // 方法特定选项
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'GET':
            default:
                if (!empty($data)) {
                    $url = $url . '?' . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                }
                break;
        }
        
        // 设置请求头
        $allHeaders = array_merge($this->defaultHeaders, $this->options['headers'], $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
    }

    /**
     * 发送GET请求
     * 
     * @param string $url 请求URL
     * @param array $params 查询参数
     * @param array $headers 请求头
     * @return array 响应数据
     */
    public function get($url, $params = [], $headers = [])
    {
        return $this->request('GET', $url, $params, $headers);
    }

    /**
     * 发送POST请求
     * 
     * @param string $url 请求URL
     * @param array $data 请求数据
     * @param array $headers 请求头
     * @return array 响应数据
     */
    public function post($url, $data = [], $headers = [])
    {
        return $this->request('POST', $url, $data, $headers);
    }

    /**
     * 发送PUT请求
     * 
     * @param string $url 请求URL
     * @param array $data 请求数据
     * @param array $headers 请求头
     * @return array 响应数据
     */
    public function put($url, $data = [], $headers = [])
    {
        return $this->request('PUT', $url, $data, $headers);
    }

    /**
     * 发送DELETE请求
     * 
     * @param string $url 请求URL
     * @param array $data 请求数据
     * @param array $headers 请求头
     * @return array 响应数据
     */
    public function delete($url, $data = [], $headers = [])
    {
        return $this->request('DELETE', $url, $data, $headers);
    }
}