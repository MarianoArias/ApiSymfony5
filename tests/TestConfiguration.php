<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

class TestConfiguration extends WebTestCase
{
    protected static $inited = false;
    
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $this->elasticsearch = self::$kernel->getContainer()->get('elasticsearch');

        // BEFORE FIRST TEST ONLY: DROP DATABASE SCHEMA, CREATE DATABASE SCHEMA & DELETE ELASTICSEARCH INDEX
        if (!static::$inited) {
            $schemaTool = new SchemaTool($this->em);
            $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
            $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        
            $this->elasticsearch->deleteIndex();
            
            static::$inited = true;
        }
    }
        
    protected function getClientStatusCode()
    {
        return $this->client->getResponse()->getStatusCode();
    }
        
    protected function getClientContent()
    {
        return json_decode($this->client->getResponse()->getContent());
    }
        
    protected function getClientHeader($headerName)
    {
        return $this->client->getResponse()->headers->all()[$headerName][0];
    }
}
