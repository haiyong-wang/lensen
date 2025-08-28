<?php

require_once '../vendor/autoload.php';

use CjDropshipping\CjDropshippingSDK;

try {
    // 初始化SDK
    $cjApi = new CjDropshippingSDK();
    $accessToken = $cjApi->authenticate('ss', 'sss');
    echo "Access Token: " . $accessToken . "\n";die;
    
    // 获取产品列表示例
    echo "获取产品列表:\n";
    $cjApi = new CJDropshippingSDK();
    $cjApi->setAccessToken('sssss');
    $products = $cjApi->products()->getList(1, 5);
    print_r($products);die;
    
    // 搜索产品示例
    echo "\n搜索产品:\n";
    $searchResults = $cjApi->products()->search('phone', 1, 3);
    print_r($searchResults);
    
    // 获取产品详情示例
    echo "\n获取产品详情:\n";
    $productDetail = $cjApi->products()->getDetail('product123');
    print_r($productDetail);
    
    // 查询库存示例
    echo "\n查询库存:\n";
    $inventory = $cjApi->products()->checkInventory('product123', 'sku123');
    print_r($inventory);
    
    // 批量查询库存示例
    echo "\n批量查询库存:\n";
    $batchInventory = $cjApi->products()->batchCheckInventory([
        ['productId' => 'product123', 'skuId' => 'sku123'],
        ['productId' => 'product456', 'skuId' => 'sku456']
    ]);
    print_r($batchInventory);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}