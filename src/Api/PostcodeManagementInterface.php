<?php

namespace Trinos\PostcodeNL\Api;

interface PostcodeManagementInterface
{

    /**
     * Get postcode information
     *
     * @param string $postcode
     * @param string $houseNumber
     * @param string $houseNumberAddition
     * @return string
     */
    public function getPostcodeInformation(string $postcode, string $housenumber, string $housenumberAddition = '');
}