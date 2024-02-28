<?php

namespace Trinos\PostcodeNL\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Trinos\PostcodeNL\Api\PostcodeManagementInterface;
use Trinos\PostcodeNL\Model\Config\PostcodeNL as PostcodeNLConfig;

/**
 * Class PostcodeManagement
 * @package Experius\Postcode\Model
 */
class PostcodeManagement implements PostcodeManagementInterface
{
    const API_URL = 'https://api.postcode.eu';

    public function __construct(
        private readonly PostcodeNLConfig $postcodeNLConfig,
    ) {
    }

    /**
     * @param string $postcode The postcode you would like to get information for.
     * @param string $houseNumber The housenumber you would like to get information for.
     * @param string $houseNumberAddition The housenumber addition you would like to get information for.
     * @return string
     */
    public function getPostcodeInformation(string $postcode, string|int $housenumber, string $housenumberAddition = ''): string
    {
        $client = new Client([
            'base_uri' => self::API_URL,
            'timeout' => 3.0,
        ]);

        $apiKey = $this->postcodeNLConfig->getApiKey();
        $apiSecret = $this->postcodeNLConfig->getApiSecret();

        $urlEncPostcode = rawurlencode($postcode);
        $urlEncHousenumber = rawurlencode($housenumber);
        $urlEncHousenumberAdd = rawurlencode($housenumberAddition);

        try {
            $response = $client->request('GET', "/nl/v1/addresses/postcode/$urlEncPostcode/$urlEncHousenumber/$urlEncHousenumberAdd", [
                'auth' => [$apiKey, $apiSecret],
            ]);
        } catch (GuzzleException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents(), true);
            if (isset($response['exception'])) {
                $response['exception'] = __($response['exception']);
            }
            return json_encode($response);
        }

        return $response->getBody()->getContents();
    }
}
