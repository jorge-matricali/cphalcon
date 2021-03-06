<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Test\Integration\Session\Bag;

use IntegrationTester;
use Phalcon\Session\Bag;
use Phalcon\Test\Fixtures\Traits\DiTrait;
use Phalcon\Test\Fixtures\Traits\SessionBagTrait;

class SetCest
{
    use DiTrait;
    use SessionBagTrait;

    /**
     * Tests Phalcon\Session\Bag :: set()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function sessionBagSet(IntegrationTester $I)
    {
        $I->wantToTest('Session\Bag - set()');

        $collection = new Bag('BagTest');

        $collection->set('three', 'two');

        $I->assertEquals(
            'two',
            $collection->get('three')
        );


        $collection->three = 'Phalcon';

        $I->assertEquals(
            'Phalcon',
            $collection->get('three')
        );


        $collection->offsetSet('three', 123);

        $I->assertEquals(
            123,
            $collection->get('three')
        );


        $collection['three'] = true;

        $I->assertTrue(
            $collection->get('three')
        );
    }
}
