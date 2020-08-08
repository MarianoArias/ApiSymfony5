<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\Elasticsearch;
use App\Service\FormErrorConverter;

use App\Entity\Customer;
use App\Form\CustomerType;
use App\Mapper\CustomerMapper;

/**
 * @Route(path="/customers")
 */
class CustomerController extends AbstractController
{
    const LIMIT = 10;
    
    public function __construct(CustomerMapper $customerMapper, Elasticsearch $elasticsearch, FormErrorConverter $formErrorConverter)
    {
        $this->customerMapper = $customerMapper;
        $this->elasticsearch = $elasticsearch;
        $this->formErrorConverter = $formErrorConverter;
    }
    
    /**
     * @Route("/", name="customer_post", methods={"POST"})
     */
    public function postAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if ($data == null) {
            return new JsonResponse(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
        
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->submit($data);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($customer);
            $this->getDoctrine()->getManager()->flush();
            $this->elasticsearch->indexDocument($customer->getId(), $this->customerMapper->toArray($customer));
            return new JsonResponse([], Response::HTTP_CREATED, ["X-Resource-Id" => $customer->getId()]);
        }
        
        return new JsonResponse($this->formErrorConverter->getMessage($form), Response::HTTP_BAD_REQUEST);
    }
    
    /**
     * @Route("/", name="customer_cget", methods={"GET"})
     */
    public function cgetAction(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        if ($page < 1) {
            return new JsonResponse(['message' => 'Invalid page'], Response::HTTP_BAD_REQUEST);
        }
        
        $limit = self::LIMIT;
        $offset = ($page - 1) * $limit;
        
        $customers = $this->getDoctrine()->getManager()->getRepository(Customer::class)->findBy([], [], $limit, $offset);
        if (!$customers) {
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }
        
        $data = [];
        foreach ($customers as $customer) {
            $data[] = $this->customerMapper->toArray($customer);
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="customer_get", methods={"GET"})
     */
    public function getAction($id): JsonResponse
    {
        $customer = $this->getDoctrine()->getManager()->getRepository(Customer::class)->find($id);
        if ($customer == null) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }
        
        return new JsonResponse($this->customerMapper->toArray($customer), Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="customer_put", methods={"PUT"})
     */
    public function putAction($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if ($data == null) {
            return new JsonResponse(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
        
        $customer = $this->getDoctrine()->getManager()->getRepository(Customer::class)->find($id);
        if ($customer == null) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }
        
        $form = $this->createForm(CustomerType::class, $customer);
        $form->submit($data);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->elasticsearch->indexDocument($customer->getId(), $this->customerMapper->toArray($customer));
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse($this->formErrorConverter->getMessage($form), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/{id}", name="customer_patch", methods={"PATCH"})
     */
    public function patchAction($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if ($data == null) {
            return new JsonResponse(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
        
        $customer = $this->getDoctrine()->getManager()->getRepository(Customer::class)->find($id);
        if ($customer == null) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }
        
        $form = $this->createForm(CustomerType::class, $customer);
        $form->submit($data, false);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->elasticsearch->indexDocument($customer->getId(), $this->customerMapper->toArray($customer));
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse($this->formErrorConverter->getMessage($form), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/{id}", name="customer_delete", methods={"DELETE"})
     */
    public function deleteAction($id): JsonResponse
    {
        $customer = $this->getDoctrine()->getManager()->getRepository(Customer::class)->find($id);
        if ($customer == null) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $this->getDoctrine()->getManager()->remove($customer);
        $this->getDoctrine()->getManager()->flush();
        $this->elasticsearch->deleteDocument($id);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
