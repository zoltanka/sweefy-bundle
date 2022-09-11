<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Tests\Functional\Service;

use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use ZFekete\SweefyBundle\Service\ObjectHydrator;
use ZFekete\SweefyBundle\Service\ResultHydrator;
use ZFekete\SweefyBundle\Service\RSOMetaDataBuilder;
use ZFekete\SweefyBundle\Service\Sweefy;
use function file_get_contents;

class SweefyTest extends TestCase
{
    protected Sweefy $service;

    public function setUp(): void
    {
        $connectionParams = [
            'dbname'   => 'default',
            'user'     => 'root',
            'password' => 'root',
            'host'     => 'mysql',
            'driver'   => 'pdo_mysql',
        ];

        $conn = DriverManager::getConnection($connectionParams);

        $resultHydrator = new ResultHydrator(
            new ObjectHydrator(new RSOMetaDataBuilder())
        );

        $this->service = new Sweefy($conn, $resultHydrator);
    }

    public function tearDown(): void
    {
        $this->service->getConnection()->executeQuery(<<<SQL
            SET FOREIGN_KEY_CHECKS = 0;

             SELECT @str := CONCAT('TRUNCATE TABLE ', table_schema, '.', table_name, ';')
             FROM   information_schema.tables
             WHERE  table_type   = 'BASE TABLE'
               AND  table_schema IN ('default');
             
             PREPARE stmt FROM @str;
             
             EXECUTE stmt;
             
             DEALLOCATE PREPARE stmt;
             
             SET FOREIGN_KEY_CHECKS = 1;
SQL
        );
    }

    public function testQueryExecution(): void
    {
        $this->service->getConnection()->executeQuery(file_get_contents(__DIR__ . '/../../fixtures/Service/Sweefy/queryExecution.sql'));

        $query = $this->service->createQuery('select * from test_table');

        self::assertCount(2, $query->fetchAllAssociative());
    }
}
