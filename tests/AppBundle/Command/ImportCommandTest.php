<?php

namespace Tests\AppBundle\Command;

use AppBundle\Command\ImportCommand;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportCommandTest extends KernelTestCase
{
    /**
     * @var $commandTester CommandTester
     */
    public $commandTester;
    private $expected;

    public function setUp()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $application->add(new ImportCommand());

        $command = $application->find('import:csv');
        $this->commandTester = new CommandTester($command);
        $this->commandTester->setInputs(array('-n' => true));
        $this->expected = <<<'EXPECTED'
 ! [NOTE] The imported file must meet the following conditions:                 

 * The file extension must be "*.csv"
 * The first column must consist of the product code.
 * All fields must be filled

 Do you want to continue? (yes/no) [yes]:
 > 

EXPECTED;
    }

    public function testExecuteWithNonexistentFile()
    {
        $this->commandTester->execute(
            array(
                'filename' => __DIR__.'/../Fixture/nonexistent_file.csv',
                '--test' => true,
            )
        );

        $this->assertEquals(PHP_EOL.$this->expected.'The file "'.__DIR__.'/../Fixture/nonexistent_file.csv"'.' does not exist'.PHP_EOL, $this->commandTester->getDisplay());
    }

    public function testExecuteWithTrueFile()
    {
        $this->commandTester->execute(
            array(
                'filename' => __DIR__.'/../Fixture/stock.csv',
                '--test' => true,
            )
        );

        $this->assertEquals(PHP_EOL.$this->expected.PHP_EOL.'Total processed: 29 product. Imported: 23 product. Fail: 6'.PHP_EOL,
            $this->commandTester->getDisplay());
    }

    public function testExecuteWithBadPriceAndStock()
    {
        $this->commandTester->execute(
            array(
                'filename' => __DIR__.'/../Fixture/stock_invalid_price.csv',
                '--test' => true,
                '--detailed' => true,
            )
        );

        $this->assertEquals(PHP_EOL.$this->expected.PHP_EOL.
            'Total processed: 6 product. Imported: 4 product. Fail: 2'.PHP_EOL.
            'Next item was not imported:'.PHP_EOL.
            'P0005 - Stock must be of type integer && Price must be of type float'.PHP_EOL.
            'P0004 - Invalid item in CSV file'.PHP_EOL,
            $this->commandTester->getDisplay());
    }
}