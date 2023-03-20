<?php

namespace Trinos\PostcodeNL\Controller\Adminhtml\System\Config;

use GuzzleHttp\Client;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Apicheck implements HttpGetActionInterface
{
    const API_URL = 'https://api.postcode.eu';

    public function __construct(
        protected RequestInterface $request,
        protected ResultFactory    $resultFactory,
    ) {
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $apiKey = $this->request->getParam('apikey');
        $apiSecret = $this->request->getParam('apisecret');

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (empty($apiKey) || empty($apiSecret)) {
            return $result->setData([
                'success' => false,
                'message' => __('Please fill in the API key and secret'),
            ]);
        }

        $accountInfo = $this->getAccountInfo($apiKey, $apiSecret);
        $accountInfo = json_decode($accountInfo, true);

        if (isset($accountInfo['hasAccess']) && !$accountInfo['hasAccess']) {
            return $result->setData([
                'success' => false,
                'message' => __('The API key and secret are valid but you do not have access to the API'),
            ]);
        }

        return $result->setData([
            'success' => true,
            'message' => __('The API key and secret are valid'),
        ]);
    }

    private function getAccountInfo(string $apiKey, string $apiSecret): string
    {
        $client = new Client([
            'base_uri' => self::API_URL,
            'timeout' => 3.0,
        ]);

        $response = $client->request('GET', '/account/v1/info', [
            'auth' => [$apiKey, $apiSecret],
        ]);

        return $response->getBody()->getContents();
    }
}