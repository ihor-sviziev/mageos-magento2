<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Configure maximum login failures to lockout customer.
 *
 * Steps:
 * 1. Open Magento customer login page.
 * 2. Enter incorrect password specified number of times.
 * 3. "Invalid login or password." appears after each login attempt.
 * 4. Perform all assertions.
 *
 * @ZephyrId MAGETWO-49519
 */
class LockCustomerOnLoginPageTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * CustomerAccountLogin page.
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Preparing pages for test.
     *
     * @param CustomerAccountLogin $customerAccountLogin
     * @return void
     */
    public function __inject(
        CustomerAccountLogin $customerAccountLogin
    ) {
        $this->customerAccountLogin = $customerAccountLogin;
    }

    /**
     * Run Lock customer on login page test.
     *
     * @param Customer $initialCustomer
     * @param int $attempts
     * @param FixtureFactory $fixtureFactory
     * @param $incorrectPassword
     * @param string $configData
     * @return void
     */
    public function test(
        Customer $initialCustomer,
        $attempts,
        FixtureFactory $fixtureFactory,
        $incorrectPassword,
        $configData = null
    ) {
        $this->markTestIncomplete(
            "MAGETWO-50404: Failed in Bamboo>>PS-FT-Plan. Needs to be triaged and fixed as part of MLS-13."
        );

        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();
        $initialCustomer->persist();
        $incorrectCustomer = $fixtureFactory->createByCode(
            'customer',
            ['data' => ['email' => $initialCustomer->getEmail(), 'password' => $incorrectPassword]]
        );

        // Steps
        for ($i = 0; $i < $attempts; $i++) {
            $this->customerAccountLogin->open();
            $this->customerAccountLogin->getLoginBlock()->fill($incorrectCustomer);
            $this->customerAccountLogin->getLoginBlock()->submit();
        }
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
