<?php
declare(strict_types=1);

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalconphp.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Test\Integration\Mvc\Model\Transaction;

use IntegrationTester;
use Phalcon\Mvc\Model\Transaction\Failed;
use Phalcon\Test\Fixtures\Traits\DiTrait;
use Phalcon\Test\Models\Personas;

/**
 * Class RollbackCest
 */
class RollbackCest
{
    use DiTrait;

    private $records = [];

    public function _before(IntegrationTester $I)
    {
        $this->setNewFactoryDefault();
        $this->records = [];
    }

    public function _after(IntegrationTester $I)
    {
        $db = $this->container->get('db');

        foreach ($this->records as $record) {
            $db->execute('DELETE FROM personas WHERE cedula = "' . $record . '"');
        }

        $this->records = [];
    }

    /**
     * Tests Phalcon\Mvc\Model\Transaction :: rollback()
     *
     * @dataProvider getFunctions
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalconphp.com>
     * @since  2018-11-13
     */
    public function mvcModelTransactionRollback(IntegrationTester $I, Example $function)
    {
        $I->wantToTest('Mvc\Model\Transaction - rollback()');

        $this->$function();

        $tm = $this->container->getShared('transactionManager');

        $count = Personas::count();
        $transaction = $tm->get();

        for ($i = 0; $i < 10; $i++) {
            $persona = new Personas();
            $persona->setTransaction($transaction);
            $persona->cedula            = 'T-Cx' . $i;
            $persona->tipo_documento_id = 1;
            $persona->nombres           = 'LOST LOST';
            $persona->telefono          = '2';
            $persona->cupo              = 0;
            $persona->estado            = 'A';

            $result = $persona->save();

            $this->records[] = $persona->cedula;
            $I->assertTrue($result);
        }

        $transaction->rollback();
        $I->assertEquals($count, Personas::count());

    }

    /**
     * Tests Phalcon\Mvc\Model\Transaction :: rollback() - exception
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalconphp.com>
     * @since  2018-11-13
     */
    public function mvcModelTransactionRollbackException(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Model\Transaction - rollback() - exception');

        $tm = $this->container->getShared('transactionManager');

        $count = Personas::count();
        $transaction = $tm
            ->get()
            ->throwRollbackException(true)
        ;

        for ($i = 0; $i < 10; $i++) {
            $persona = new Personas();
            $persona->setTransaction($transaction);
            $persona->cedula            = 'T-Cx' . $i;
            $persona->tipo_documento_id = 1;
            $persona->nombres           = 'LOST LOST';
            $persona->telefono          = '2';
            $persona->cupo              = 0;
            $persona->estado            = 'A';

            $result = $persona->save();

            $this->records[] = $persona->cedula;
            $I->assertTrue($result);
        }

        try {
            $transaction->rollback();
            $I->assertTrue(
                false,
                "The transaction's rollback didn't throw an expected exception. Emergency stop"
            );
        } catch (Failed $e) {
            $I->assertEquals($e->getMessage(), "Transaction aborted");
        }

        $I->assertEquals($count, Personas::count());
    }

    /**
     * @return array
     */
    private function getFunctions(): array
    {
        return [
            ['setDiMysql']
            ['setDiPostgresql']
        ];
    }
}
