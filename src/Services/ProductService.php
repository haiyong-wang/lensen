<?php

namespace CjDropshipping\Services;

use CjDropshipping\Http\CurlClient;
use CjDropshipping\Auth\Authentication;

class ProductService extends BaseService
{
    public function __construct(CurlClient $client, Authentication $auth)
    {
        parent::__construct($client, $auth);
    }

    /**
     * 获取产品列表
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

        return $this->get('product/list', $data);
    }

    /**
     * 获取产品详情
     * 
     * @param string $pid 产品ID
     * @return array
     */
    public function getDetail($pid)
    {
        return $this->get('product/query', ['pid' => $pid]);
    }

    /**
     * 搜索产品
     * 
     * @param string $keyword 搜索关键词
     * @param int $pageNum 页码
     * @param int $pageSize 每页数量
     * @return array
     */
    public function search($keyword, $pageNum = 1, $pageSize = 20)
    {
        return $this->get('product/list', [
            'pageNum' => $pageNum,
            'pageSize' => $pageSize,
            'keyword' => $keyword
        ]);
    }

    /**
     * 查询库存
     * 
     * @param string $productId 产品ID
     * @param string|null $skuId SKU ID
     * @return array
     */
    public function checkInventory($productId, $skuId = null)
    {
        $data = ['productId' => $productId];
        if ($skuId) {
            $data['skuId'] = $skuId;
        }

        return $this->get('stock/query', $data);
    }

    /**
     * 批量查询库存
     * 
     * @param array $productList 产品列表
     * @return array
     */
    public function batchCheckInventory($productList)
    {
        return $this->post('stock/batchQuery', $productList);
    }
}