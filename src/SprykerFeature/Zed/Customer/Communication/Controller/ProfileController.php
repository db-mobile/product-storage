<?php

namespace SprykerFeature\Zed\Customer\Communication\Controller;

use SprykerFeature\Zed\Application\Communication\Controller\AbstractController;
use SprykerFeature\Zed\Customer\Business\Exception\AddressNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use SprykerFeature\Zed\Customer\Communication\CustomerDependencyContainer;

/**
 * @method CustomerDependencyContainer getDependencyContainer()
 */
class ProfileController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        $id_customer = $request->query->get('id');

        $this->setBreadcrumbs([
            ['label' => 'Customers', 'uri' => '/customer'],
            ['label' => sprintf('Customer ID %s', $id_customer), 'uri' => sprintf('/customer/profile?id=%s', $id_customer)]
        ]);

        $this->setMenuHighlight('/customer');

        $form = $this->getDependencyContainer()->createCustomerForm($request);
        $form->init();

        $customerTransfer = $this->getLocator()->customer()->transferCustomer();
        $customerTransfer->setIdCustomer($id_customer);
        $customerTransfer = $this->getLocator()->customer()->facade()->getCustomer($customerTransfer);

        try {
            $idShippingAddress = $this->getLocator()->customer()->facade()
                ->getDefaultShippingAddress($customerTransfer)
                ->getIdCustomerAddress();
        } catch (AddressNotFoundException $e) {
            $idShippingAddress = null;
        }

        try {
            $idBillingAddress = $this->getLocator()->customer()->facade()
                ->getDefaultBillingAddress($customerTransfer)
                ->getIdCustomerAddress();
        } catch (AddressNotFoundException $e) {
            $idBillingAddress = null;
        }

        $addresses = [];
        foreach ($customerTransfer->getAddresses() as $address) {
            $addresses[] = [
                'id' => $address->getIdCustomerAddress(),
                'rendered' => $this->getLocator()->customer()->facade()->renderAddress($address),
                'isDefaultBilling' => ($address->getIdCustomerAddress() == $idBillingAddress),
                'isDefaultShipping' => ($address->getIdCustomerAddress() == $idShippingAddress),
            ];
        }

        return [
            'id_customer' => $customerTransfer->getIdCustomer(),
            'customerJson' => json_encode($form->toArray()),
            'registered' => $customerTransfer->getRegistered(),
            'addresses' => $addresses,
        ];
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function sendPasswordRestoreTokenAction(Request $request)
    {
        $customerTransfer = $this->getLocator()->customer()->transferCustomer();
        $customerTransfer->setIdCustomer($request->query->get('id'));
        $this->getLocator()->customer()->facade()->forgotPassword($customerTransfer);

        return $this->redirectResponse('/customer/profile?id='.$request->query->get('id'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function editAction(Request $request)
    {
        $form = $this->getDependencyContainer()->createCustomerForm($request);
        $form->init();

        if ($form->isValid()) {
            $customerTransfer = $this->getLocator()->customer()->transferCustomer();
            $customerTransfer->fromArray($form->getRequestData());
            $this->getLocator()->customer()->facade()->updateCustomer($customerTransfer);
        }

        return $this->jsonResponse($form->renderData());
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addressesAction(Request $request)
    {
        $grid = $this->getDependencyContainer()->createAddressGrid($request);

        return $this->jsonResponse($grid->renderData());
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addressAction(Request $request)
    {
        $form = $this->getDependencyContainer()->createAddressForm($request);
        $form->init();

        if ($form->isValid()) {
            $addressTransfer = $this->getLocator()->customer()->transferAddress();
            $addressTransfer->fromArray($form->getRequestData());
            if ($addressTransfer->getIdCustomerAddress()) {
                $this->getLocator()->customer()->facade()->updateAddress($addressTransfer);

                return $this->jsonResponse($form->renderData());
            }

            $this->getLocator()->customer()->facade()->newAddress($addressTransfer);
        }

        return $this->jsonResponse($form->renderData());
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function setDefaultShippingAddressAction(Request $request)
    {
        $addressTransfer = $this->getLocator()->customer()->transferAddress();
        $addressTransfer->setIdCustomerAddress($request->query->get('address_id'));
        $addressTransfer->setFkCustomer($request->query->get('customer_id'));
        $this->getLocator()->customer()->facade()->setDefaultShippingAddress($addressTransfer);

        return $this->redirectResponse('/customer/profile?id='.$request->query->get('customer_id'));
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function setDefaultBillingAddressAction(Request $request)
    {
        $addressTransfer = $this->getLocator()->customer()->transferAddress();
        $addressTransfer->setIdCustomerAddress($request->query->get('address_id'));
        $addressTransfer->setFkCustomer($request->query->get('customer_id'));
        $this->getLocator()->customer()->facade()->setDefaultBillingAddress($addressTransfer);

        return $this->redirectResponse('/customer/profile?id='.$request->query->get('customer_id'));
    }
}
