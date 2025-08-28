<?php

namespace CjDropshipping\Services;

use GuzzleHttp\Client;
use CjDropshipping\Auth\Authentication;

class OrderService extends BaseService
{
    public function __construct(Client $client, Authentication $auth)
    {
        parent::__construct($client, $auth);
    }

    /**
     * 创建订单
     * 
     * @param array $orderData 订单数据
     * @return array
     */
    public function create($orderData)
    {
        return $this->post('order/create', $orderData);
    }

    /**
     * 查询订单
     * 
     * @param string $orderId 订单ID
     * @return array
     */
    public function getOrder($orderId)
    {
        return $this->get('order/query', ['orderId' => $orderId]);
    }


    /**
     * 获取订单列表
     * 
     * @param int $pageNum 页码
     * @param int $pageSize 每页数量
     * @param array $filters 过滤条件
     * @return array
     */
    public function getList($pageNum = 1, $pageSize = 20, $filters = [])
    {
        $data = array_merge([
            'pageNum' => $pageNum,
            'pageSize' => $pageSize
        ], $filters);

        return $this->get('order/list', $data);
    }

    /**
     * 取消订单
     * 
     * @param string $orderId 订单ID
     * @return array
     */
    public function cancel($orderId)
    {
        return $this->post('order/cancel', ['orderId' => $orderId]);
    }

    /**
     * 更新订单
     * 
     * @param string $orderId 订单ID
     * @param array $updateData 更新数据
     * @return array
     */
    public function update($orderId, $updateData)
    {
        $data = array_merge(['orderId' => $orderId], $updateData);
        return $this->put('order/update', $data);
    }
}