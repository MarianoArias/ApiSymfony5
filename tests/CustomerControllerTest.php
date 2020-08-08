<?php

namespace App\Tests;

use Symfony\Component\HttpFoundation\Response;

use App\Tests\TestConfiguration;

use App\Entity\Customer;
use App\Mapper\CustomerMapper;

class CustomerControllerTest extends TestConfiguration
{
    const PATH = "/customers/";
    const EXTREMELY_HIGH_NUMBER = 1000000;

    const FULL_MOCK_DATA = [
        "firstName" => "Full Test",
        "lastName" => "Full Test",
        "email" => "full@test.com",
        "phoneNumber" => "(full)0123456789"
    ];
    
    const FULL_FAKE_MOCK_DATA = [
        "fakeField" => "Fake Test",
        "lastName" => "Fake Test",
        "email" => "fake@test.com",
        "phoneNumber" => "(fullfake)0123456789"
    ];
    
    const PARTIAL_MOCK_DATA = [
        "firstName" => "Partial Test",
        "lastName" => "Partial Test",
        "email" => "partial@test.com",
    ];
    
    const PARTIAL_FAKE_MOCK_DATA = [
        "fakeField" => "Partial Fake Test",
        "lastName" => "Partial Test",
        "phoneNumber" => "(partialfake)0123456789"
    ];
    
    public function testCgetAction()
    {
        for ($i = 1; $i <= 11; $i++) {
            $this->createCustomer();
        }

        $this->client->request('GET', self::PATH . "?page=1");
        
        $content = $this->getClientContent();
        $statusCode = $this->getClientStatusCode();

        $this->assertEquals(Response::HTTP_OK, $statusCode);
        $this->assertNotEmpty($content);
        $this->assertEquals(10, count($content));
        
        $this->client->request('GET', self::PATH . "?page=2");
        
        $content = $this->getClientContent();
        $statusCode = $this->getClientStatusCode();

        $this->assertEquals(Response::HTTP_OK, $statusCode);
        $this->assertNotEmpty($content);
        $this->assertEquals(1, count($content));
    }
    
    
    public function testFailCgetAction()
    {
        $this->client->request('GET', self::PATH . "?page=" . self::EXTREMELY_HIGH_NUMBER);
        
        $content = $this->getClientContent();
        $statusCode = $this->getClientStatusCode();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $statusCode);
        $this->assertEmpty($content);
    }
    
    public function testGetAction()
    {
        $customer = $this->createCustomer();
    
        $this->client->request('GET', self::PATH . $customer->getId());
        
        $content = $this->getClientContent();
        $statusCode = $this->getClientStatusCode();
    
        $this->assertEquals(Response::HTTP_OK, $statusCode);
        $this->assertNotEmpty($content);
        $this->assertEquals($customer->getFirstName(), $content->firstName);
        $this->assertEquals($customer->getLastName(), $content->lastName);
        $this->assertEquals($customer->getEmail(), $content->email);
        $this->assertEquals($customer->getPhoneNumber(), $content->phoneNumber);
    }
    
    public function testFailGetAction()
    {
        $this->client->request('GET', self::PATH . self::EXTREMELY_HIGH_NUMBER);
        
        $content = $this->getClientContent();
        $statusCode = $this->getClientStatusCode();
    
        $this->assertEquals(Response::HTTP_NOT_FOUND, $statusCode);
        $this->assertEmpty($content);
    }
    
    public function testPostAction()
    {
        $this->client->request('POST', self::PATH, [], [], [], json_encode(self::FULL_MOCK_DATA));
        
        $statusCode = $this->getClientStatusCode();
        $content = $this->getClientContent();
        $resourceId = $this->getClientHeader("x-resource-id");
        $customer = $this->em->getRepository(Customer::class)->find($resourceId);
    
        $this->assertEquals(Response::HTTP_CREATED, $statusCode);
        $this->assertEmpty($content);
        $this->assertEquals($customer->getFirstName(), self::FULL_MOCK_DATA['firstName']);
        $this->assertEquals($customer->getLastName(), self::FULL_MOCK_DATA['lastName']);
        $this->assertEquals($customer->getEmail(), self::FULL_MOCK_DATA['email']);
        $this->assertEquals($customer->getPhoneNumber(), self::FULL_MOCK_DATA['phoneNumber']);
        $this->assertIsInt($resourceId);
    }
    
    public function testFailPostAction()
    {
        $this->client->request('POST', self::PATH, [], [], [], json_encode(self::FULL_FAKE_MOCK_DATA));
        
        $statusCode = $this->getClientStatusCode();
        $content = $this->getClientContent();
    
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $statusCode);
        $this->assertNotEmpty($content);
    }
    
    public function testPutAction()
    {
        $customer = $this->createCustomer();

        $this->client->request('PUT', self::PATH . $customer->getId(), [], [], [], json_encode(self::FULL_MOCK_DATA));
        
        $statusCode = $this->getClientStatusCode();
        $content = $this->getClientContent();
    
        $this->assertEquals(Response::HTTP_NO_CONTENT, $statusCode);
        $this->assertNull($content);
        $this->assertEquals($customer->getFirstName(), self::FULL_MOCK_DATA['firstName']);
        $this->assertEquals($customer->getLastName(), self::FULL_MOCK_DATA['lastName']);
        $this->assertEquals($customer->getEmail(), self::FULL_MOCK_DATA['email']);
        $this->assertEquals($customer->getPhoneNumber(), self::FULL_MOCK_DATA['phoneNumber']);
    }
    
    public function testFailPutAction()
    {
        $customer = $this->createCustomer();

        $this->client->request('PUT', self::PATH . $customer->getId(), [], [], [], json_encode(self::FULL_FAKE_MOCK_DATA));
        
        $statusCode = $this->getClientStatusCode();
        $content = $this->getClientContent();
    
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $statusCode);
        $this->assertNotEmpty($content);
    }
    
    public function testPatchAction()
    {
        $customer = $this->createCustomer();
    
        $this->client->request('PATCH', self::PATH . $customer->getId(), [], [], [], json_encode(self::PARTIAL_MOCK_DATA));
        
        $statusCode = $this->getClientStatusCode();
        $content = $this->getClientContent();
    
        $this->assertEquals(Response::HTTP_NO_CONTENT, $statusCode);
        $this->assertNull($content);
        $this->assertEquals($customer->getFirstName(), self::PARTIAL_MOCK_DATA['firstName']);
        $this->assertEquals($customer->getLastName(), self::PARTIAL_MOCK_DATA['lastName']);
        $this->assertEquals($customer->getEmail(), self::PARTIAL_MOCK_DATA['email']);
    }
    
    public function testFailPatchAction()
    {
        $customer = $this->createCustomer();
    
        $this->client->request('PATCH', self::PATH . $customer->getId(), [], [], [], json_encode(self::PARTIAL_FAKE_MOCK_DATA));
        
        $statusCode = $this->getClientStatusCode();
        $content = $this->getClientContent();
    
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $statusCode);
        $this->assertNotEmpty($content);
    }
    
    public function testDeleteAction()
    {
        $customer = $this->createCustomer();
    
        $this->client->request('DELETE', self::PATH . $customer->getId());
    
        $statusCode = $this->getClientStatusCode();
        $content = $this->getClientContent();
    
        $this->assertEquals(Response::HTTP_NO_CONTENT, $statusCode);
        $this->assertEmpty($content);
        $this->assertNotNull($customer->getDeletedAt());
    }
    
    public function testFailDeleteAction()
    {
        $this->client->request('DELETE', self::PATH . self::EXTREMELY_HIGH_NUMBER);
    
        $statusCode = $this->getClientStatusCode();
        $content = $this->getClientContent();
    
        $this->assertEquals(Response::HTTP_NOT_FOUND, $statusCode);
        $this->assertEmpty($content);
    }
    
    protected function createCustomer()
    {
        $customer = new Customer();
        $customer->setFirstName("Base Test");
        $customer->setLastName("Base Test");
        $customer->setEmail("base@test.com");
        $customer->setPhoneNumber("(base)0123456789");
        
        $this->em->persist($customer);
        $this->em->flush();
        
        $customerMapper = new CustomerMapper();
        $this->elasticsearch->indexDocument($customer->getId(), $customerMapper->toArray($customer));
        
        return $customer;
    }
}
