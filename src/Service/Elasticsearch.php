<?php

namespace App\Service;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class Elasticsearch
{
    const RETRIES = 3;
    
    public function __construct()
    {
        $this->client = $this->getClient();
    }
    
    protected function getClient(): Client
    {
        $hosts = [[
            'host' => $_ENV['ELASTICSEARCH_HOST'],
            'port' => $_ENV['ELASTICSEARCH_PORT'],
            'scheme' => $_ENV['ELASTICSEARCH_SCHEME'],
            'user' => $_ENV['ELASTICSEARCH_USER'],
            'pass' => $_ENV['ELASTICSEARCH_PASSWORD']
        ]];
        
        return ClientBuilder::create()
            ->setHosts($hosts)
            ->setRetries(self::RETRIES)
            ->build();
    }
    
    protected function checkEnvironmentVariables(): void
    {
        $this->checkEnvironmentVariables();
        
        if (isset($_ENV['ELASTICSEARCH_HOST']) == false) {
            throw new \Exception('Environment variable not found: ELASTICSEARCH_HOST');
        }
        if (isset($_ENV['ELASTICSEARCH_PORT']) == false) {
            throw new \Exception('Environment variable not found: ELASTICSEARCH_HOST');
        }
        if (isset($_ENV['ELASTICSEARCH_SCHEME']) == false) {
            throw new \Exception('Environment variable not found: ELASTICSEARCH_USER');
        }
        if (isset($_ENV['ELASTICSEARCH_USER']) == false) {
            throw new \Exception('Environment variable not found: ELASTICSEARCH_PASSWORD');
        }
        if (isset($_ENV['ELASTICSEARCH_PASSWORD']) == false) {
            throw new \Exception('Environment variable not found: ELASTICSEARCH_PASSWORD');
        }
    }
    
    public function indexDocument($id, $data): void
    {
        $params = [
            'index' => $_ENV['ELASTICSEARCH_INDEX'],
            'id'    => $id,
            'body'  => $data
        ];

        $this->client->index($params);
    }
    
    public function getDocument($id): void
    {
        $params = [
            'index' => $_ENV['ELASTICSEARCH_INDEX'],
            'id'    => $id
        ];

        $this->client->get($params);
    }
    
    public function deleteDocument($id): void
    {
        $params = [
            'index' => $_ENV['ELASTICSEARCH_INDEX'],
            'id'    => $id
        ];

        $this->client->delete($params);
    }
    
    public function deleteIndex(): void
    {
        $params = [
            'index' => $_ENV['ELASTICSEARCH_INDEX']
        ];

        if ($this->client->indices()->exists($params)) {
            $this->client->indices()->delete($params);
        }
    }
}
