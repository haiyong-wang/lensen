<?php

namespace CjDropshipping\Logger;

use CjDropshipping\Exceptions\ApiException;

class Logger
{
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    
    private $logFile;
    private $logLevel;
    private $enabled;
    
    /**
     * 初始化日志记录器
     * 
     * @param string $logFile 日志文件路径
     * @param string $logLevel 日志级别
     * @param bool $enabled 是否启用日志
     */
    public function __construct($logFile = null, $logLevel = self::LEVEL_INFO, $enabled = true)
    {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
        $this->enabled = $enabled;
        
        // 如果没有指定日志文件，使用默认位置
        if ($logFile === null) {
            $this->logFile = dirname(__DIR__, 2) . '/logs/cj_dropshipping.log';
        }
        
        // 确保日志目录存在
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * 记录调试信息
     * 
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    public function debug($message, array $context = [])
    {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * 记录一般信息
     * 
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    public function info($message, array $context = [])
    {
        $this->log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * 记录警告信息
     * 
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    public function warning($message, array $context = [])
    {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * 记录错误信息
     * 
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    public function error($message, array $context = [])
    {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * 记录API异常
     * 
     * @param ApiException $exception API异常
     * @param array $context 上下文信息
     */
    public function exception(ApiException $exception, array $context = [])
    {
        $this->error('API Exception: ' . $exception->getMessage(), array_merge([
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ], $context));
    }
    
    /**
     * 记录HTTP请求
     * 
     * @param string $method HTTP方法
     * @param string $url 请求URL
     * @param array $data 请求数据
     * @param array $headers 请求头
     */
    public function logRequest($method, $url, $data = [], $headers = [])
    {
        $this->info('API Request', [
            'method' => $method,
            'url' => $url,
            'data' => $data,
            'headers' => $this->sanitizeHeaders($headers)
        ]);
    }
    
    /**
     * 记录HTTP响应
     * 
     * @param int $statusCode HTTP状态码
     * @param array $response 响应数据
     * @param float $duration 请求耗时（秒）
     */
    public function logResponse($statusCode, $response, $duration)
    {
        $this->info('API Response', [
            'status_code' => $statusCode,
            'response' => $response,
            'duration' => round($duration, 3) . 's'
        ]);
    }
    
    /**
     * 记录cURL错误
     * 
     * @param string $error 错误消息
     * @param string $url 请求URL
     * @param string $method HTTP方法
     */
    public function logCurlError($error, $url, $method)
    {
        $this->error('cURL Error', [
            'error' => $error,
            'url' => $url,
            'method' => $method
        ]);
    }
    
    /**
     * 实际记录日志的方法
     * 
     * @param string $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文信息
     */
    private function log($level, $message, array $context = [])
    {
        if (!$this->enabled) {
            return;
        }
        
        // 检查日志级别
        $levels = [self::LEVEL_DEBUG, self::LEVEL_INFO, self::LEVEL_WARNING, self::LEVEL_ERROR];
        $currentLevelIndex = array_search($this->logLevel, $levels);
        $messageLevelIndex = array_search($level, $levels);
        
        // if ($messageLevelIndex < $currentLevelIndex) {
        //     return; // 低于当前日志级别，不记录
        // }
        
        // 构建日志条目
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message";
        
        if (!empty($context)) {
            $logEntry .= " " . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        $logEntry .= PHP_EOL;
        
        // 写入日志文件
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 清理敏感信息的请求头（如认证令牌）
     * 
     * @param array $headers 原始请求头
     * @return array 清理后的请求头
     */
    private function sanitizeHeaders($headers)
    {
        $sanitized = [];
        
        foreach ($headers as $header) {
            if (stripos($header, 'CJ-Access-Token') !== false) {
                $sanitized[] = 'CJ-Access-Token: ***REDACTED***';
            } else {
                $sanitized[] = $header;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * 设置日志级别
     * 
     * @param string $level 日志级别
     */
    public function setLogLevel($level)
    {
        $this->logLevel = $level;
    }
    
    /**
     * 启用或禁用日志记录
     * 
     * @param bool $enabled 是否启用
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool)$enabled;
    }
    
    /**
     * 获取日志文件路径
     * 
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }
}