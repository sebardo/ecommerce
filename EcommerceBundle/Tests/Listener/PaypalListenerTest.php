<?php

namespace EcommerceBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @class  PlanTest
 * @brief Test the  ReportBase entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Listener/PaypalListenerTest.php
 * @endcode
 */
class PaypalListenerTest  extends WebTestCase
{

    protected function getContainer()
    {
        return static::$kernel->getContainer();
    }
    
    /**
     * @code
     * phpunit -v --filter testListener -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Listener/PaypalListenerTest.php
     * @endcode
     * 
     */
    public function testListener()
    {
        $this->markTestSkipped('must be revisited.');
        $client = static::createClient();
        $container = $client->getContainer();
        
        $post = array(
                'period_type' => 'Regular',
                'outstanding_balance' => 0.00,
                'next_payment_date' => '02:00:00 Dec 19, 2012 PST',
                'protection_eligibility' => 'Ineligible',
                'payment_cycle' => 'Monthly',
                'tax' => 0.00,
                'payer_id' => 'E7BTGVXBFSUAU',
                'payment_date' => '05:38:59 Nov 19, 2012 PST',
                'payment_status' => 'Completed',
                'product_name' => 'ElderHelpers.org',
                'charset' => 'windows-1252',
                'recurring_payment_id' => 'I-PFSGNJYBXBH5',
                'first_name' => 'Drew',
                'mc_fee' => 0.65,
                'notify_version' => 3.7,
                'amount_per_cycle' => 12.00,
                'payer_status' => 'verified',
                'currency_code' => 'USD',
                'business' => 'sandbo_1215254764_biz@angelleye.com',
                'verify_sign' => 'AUivUYns031-2-dNgZdEkr51EzGcAF5d4-6xZ2neOdkff7tDdERk1R9k',
                'payer_email' => 'sandbo_1204199080_biz@angelleye.com',
                'initial_payment_amount' => 0.00,
                'profile_status' => 'Active',
                'amount' => 12.00,
                'txn_id' => '3GN39710BA809992V',
                'payment_type' => 'instant',
                'payer_business_name' => "Drew Angell's Test Store",
                'last_name' => 'Angell',
                'receiver_email' => 'sandbo_1215254764_biz@angelleye.com',
                'payment_fee' => 0.65,
                'receiver_id' => 'ATSCG2QMC9KAU',
                'txn_type' => 'recurring_payment',
                'mc_currency' => 'USD',
                'residence_country' => 'US',
                'test_ipn' => 1,
                'transaction_subject' => 'ElderHelpers.org',
                'payment_gross' => 12.00,
                'shipping' => 0.00,
                'product_type' => 1,
                'time_created' => '21:19:38 Dec 19, 2011 PST',
                'ipn_track_id' => 'b6f7576ff1e68'
            );

        $crawler = $client->request('POST', '/listener', $post);

        $this->assertTrue($crawler->filter('html:contains("Invalid IPN")')->count() == 1);


    }

}
