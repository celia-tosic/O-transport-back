<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Delivery;
use App\Repository\DeliveryRepository;
use App\Repository\UserRepository;

class DeliveryTestsContent
{
    public function __construct(UserRepository $userRepository,DeliveryRepository $deliveryRepository)
    {
        $this->deliveryRepository = $deliveryRepository;
        $this->userRepository = $userRepository;
    }
    /**
     * Function that update delivery's related content according to the json receive
     *
     * @param Delivery $delivery
     * @param [type] $array
     * @return void
     */
    public function decodeDeliveryAndUpdate(Delivery $delivery, $array)
    {

        if ($delivery->getMerchandise() !== $array['merchandise']) {
            $delivery->setMerchandise($array['merchandise']);
        }
        if ($delivery->getVolume() !== $array['volume']) {
            $delivery->setVolume($array['volume']);
        }
        if ($delivery->getComment() !== $array['comment']) {
            $delivery->setComment($array['comment']);
        }
    }

    /**
     * function that test if a customer have more than one Delivery ordered
     *
     * @param Customer $customer
     * @return void
     */
    public function deliveriesRequestedMoreThanOnce(Customer $customer)
    {
        $result = '';
        $shipmentCount = count($this->deliveryRepository->findByCustomer($customer));
        if ($shipmentCount > 1) {
            $result = true;
            return $result;
        } else {
            $result = false;
            return $result;
        }
    }

        /**
     * Function that create a new customer according to a delivery related content
     *
     * @param [type] $array
     * @return Customer
     */
    public function createCustomerFromArray($array)
    {
        $customerToCreate = new Customer();

        $customerToCreate->setName($array['name']);
        $customerToCreate->setAddress($array['address']);
        $customerToCreate->setPhoneNumber($array['phoneNumber']);

        return $customerToCreate;
    }

    /**
     * Function that update customer's delivery related content
     *
     * @param Customer $customer
     * @param [type] $array
     * @return void
     */
    public function setCustomerFromArray(Customer $customer, $array)
    {

        $customer->setName($array['name']);
        $customer->setAddress($array['address']);
        $customer->setPhoneNumber($array['phoneNumber']);

        return $customer;
    }
}
