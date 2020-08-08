<?php

namespace App\Mapper;

use App\Entity\Customer;

class CustomerMapper
{
    public function toArray(Customer $customer): array
    {
        return [
            'id' => $customer->getId(),
            'firstName' => $customer->getFirstName(),
            'lastName' => $customer->getLastName(),
            'email' => $customer->getEmail(),
            'phoneNumber' => $customer->getPhoneNumber(),
        ];
    }
}
