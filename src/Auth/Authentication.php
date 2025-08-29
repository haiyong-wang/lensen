<?php

namespace CjDropshipping\Auth;

use CjDropshipping\Http\CurlClient;
use CjDropshipping\Exceptions\ApiException;
use CjDropshipping\Logger\Logger;

class Authentication
{
    private $client;
    private $accessToken;
    private $tokenExpires;
    private $logger;

    public function __construct(CurlClient $client, Logger $logger = null)
    {
        $this->logger = $logger;
        $this->client = $client;
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
     * 使用API密钥和密钥进行认证
     * 
     * @param string $apiKey API密钥
     * @param string $apiSecret API密钥
     * @return string 访问令牌
     * @throws ApiException 当认证失败时抛出异常
     */
    public function authenticateWithCredentials($apiKey, $apiSecret)
    {
        try {
            $response = $this->client->post('authentication/accessToken', [
                'apiKey' => $apiKey,
                'apiSecret' => $apiSecret
            ]);
            
            if (isset($response['data']['accessToken'])) {
                $this->accessToken = $response['data']['accessToken'];
                // 设置token过期时间（假设有效期为1小时）
                $this->tokenExpires = time() + 3600;

                // 记录认证成功
                if ($this->logger) {
                    $this->logger->info('Authentication successful', [
                        'token' => substr($this->accessToken, 0, 10) . '...', // 部分隐藏token
                        'expires_at' => date('Y-m-d H:i:s', $this->tokenExpires)
                    ]);
                }

                return $this->accessToken;
            } else {

                $errorMsg = 'Authentication failed: ' . json_encode($response);
                
                // 记录认证失败
                if ($this->logger) {
                    $this->logger->error($errorMsg, [
                        'response' => $response
                    ]);
                }

                throw new ApiException('Authentication failed: ' . json_encode($response));
            }
        } catch (ApiException $e) {
            // 记录认证异常
            if ($this->logger) {
                $this->logger->exception($e, [
                    'api_key' => substr($apiKey, 0, 8) . '...'
                ]);
            }

            throw new ApiException('Authentication request failed: ' . $e->getMessage());
        }
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
        $this->accessToken = $accessToken;
        $this->tokenExpires = time() + $expiresIn;
        return $this;
    }

    /**
     * 获取访问令牌
     * 
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * 获取认证头信息
     * 
     * @return array
     * @throws ApiException 当没有有效token时抛出异常
     */
    public function getAuthHeaders()
    {
        // if (!$this->isTokenValid()) {
        //     throw new ApiException('No valid access token available. Please authenticate first.');
        // }
        
        return [
            'CJ-Access-Token: ' . $this->accessToken,
        ];
    }

    /**
     * 设置HTTP客户端
     * 
     * @param CurlClient $client
     * @return self
     */
    public function setClient(CurlClient $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * 清除当前token（强制重新认证）
     */
    public function clearToken()
    {
        $this->accessToken = null;
        $this->tokenExpires = null;
    }

    /**
     * 检查token是否有效
     * 
     * @return bool
     */
    public function isTokenValid()
    {
        return $this->accessToken && $this->tokenExpires > time();
    }
}