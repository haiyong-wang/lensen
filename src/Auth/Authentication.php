<?php

namespace CjDropshipping\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use CjDropshipping\Exceptions\ApiException;

class Authentication
{
    private $client;
    private $apiKey;
    private $apiSecret;
    private $accessToken;
    private $tokenExpires;

    public function __construct(Client $client, $apiKey, $apiSecret)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * 获取访问令牌
     * 
     * @return string Access token
     * @throws ApiException 当认证失败时抛出异常
     */
    public function getAccessToken()
    {
        // 如果已有有效token，直接返回
        if ($this->accessToken && $this->tokenExpires > time()) {
            return $this->accessToken;
        }

        try {
            $response = $this->client->post('authentication/getAccessToken', [
                'json' => [
                    'apiKey' => $this->apiKey,
                    'apiSecret' => $this->apiSecret
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['data']['accessToken'])) {
                $this->accessToken = $data['data']['accessToken'];
                // 设置token过期时间（假设有效期为1小时）
                $this->tokenExpires = time() + 3600;
                return $this->accessToken;
            } else {
                throw new ApiException('Authentication failed: ' . json_encode($data));
            }
        } catch (RequestException $e) {
            throw new ApiException('Authentication request failed: ' . $e->getMessage());
        }
    }

    /**
     * 获取认证头信息
     * 
     * @return array
     */
    public function getAuthHeaders()
    {
        return [
            'CJ-Access-Token' => $this->getAccessToken(),
        ];
    }

    /**
     * 清除当前token（强制重新认证）
     */
    public function clearToken()
    {
        $this->accessToken = null;
        $this->tokenExpires = null;
    }
}