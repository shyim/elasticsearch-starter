<?php

namespace App;

use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class ClientFactory
{
    public static function create(): Client
    {
        return (new ClientBuilder())
            ->setHosts([$_SERVER['OPENSEARCH_URL']])
            ->build();
    }
}