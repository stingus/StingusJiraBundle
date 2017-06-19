<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Command;

use Stingus\JiraBundle\Command\GenerateCertCommand;
use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\Exception\QuestionException;
use Stingus\JiraBundle\Exception\RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GenerateCertCommandTest
 *
 * @package Stingus\JiraBundle\Tests\Command
 */
class GenerateCertCommandTest extends TestCase
{
    const CERTS_DIR = 'tmp_certs';

    /**
     * Tear down method
     */
    public function tearDown()
    {
        $this->clearCertDir();
    }

    public function testGenerateCertSuccess()
    {
        $commandTester = $this->getCommandTester();
        $commandTester->setInputs($this->getValidData());
        $exitCode = $commandTester->execute([], ['decorated' => false, 'interactive' => true]);

        $certsDir = $this->getCertPath();

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($certsDir.DIRECTORY_SEPARATOR.'private.key');
        $this->assertFileExists($certsDir.DIRECTORY_SEPARATOR.'public.key');
        $this->assertFileExists($certsDir.DIRECTORY_SEPARATOR.'cert.pem');
    }

    /**
     * @param array  $dn
     * @param string $exceptionMessage
     *
     * @dataProvider failDataProvider
     */
    public function testGenerateCertFail($dn, $exceptionMessage)
    {
        $this->expectException(QuestionException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $commandTester = $this->getCommandTester();
        $commandTester->setInputs($dn);
        $commandTester->execute([], ['decorated' => false, 'interactive' => true]);
    }

    public function testNonExistingCertDirectory()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not create certs directory (non_existing_dir/'.self::CERTS_DIR.')');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();

        $container
            ->method('getParameter')
            ->withConsecutive(
                [$this->equalTo('kernel.project_dir')],
                [$this->equalTo('stingus_jira.cert_path')]
            )
            ->willReturn('non_existing_dir', self::CERTS_DIR);

        $command = new GenerateCertCommand();
        $command->setContainer($container);

        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        $commandTester = new CommandTester($application->find('stingus_jira:generate:cert'));
        $commandTester->setInputs($this->getValidData());
        $commandTester->execute([], ['decorated' => false, 'interactive' => true]);
    }

    /**
     * @return array
     */
    public function failDataProvider()
    {
        return
            [
                'Empty country code' => [
                    [
                        '',
                        '',
                        '',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        1,
                    ],
                    'Invalid country code',
                ],
                'Country code not allowed' => [
                    [
                        'XX',
                        'XX',
                        'XX',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        1,
                    ],
                    'Invalid country code',
                ],
                'Lowercase country code' => [
                    [
                        'us',
                        'us',
                        'us',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        1,
                    ],
                    'Invalid country code',
                ],
                'Empty state' => [
                    [
                        'US',
                        '',
                        '',
                        '',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        1,
                    ],
                    'This value cannot be empty',
                ],
                'Empty locality' => [
                    [
                        'US',
                        'State',
                        '',
                        '',
                        '',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        1,
                    ],
                    'This value cannot be empty',
                ],
                'Empty organization name' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        '',
                        '',
                        '',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        1,
                    ],
                    'This value cannot be empty',
                ],
                'Empty organization unit name' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        '',
                        '',
                        '',
                        'Common name',
                        'email@example.com',
                        1,
                    ],
                    'This value cannot be empty',
                ],
                'Empty common name' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        '',
                        '',
                        '',
                        'email@example.com',
                        1,
                    ],
                    'This value cannot be empty',
                ],
                'Empty email' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        '',
                        '',
                        '',
                        1,
                    ],
                    'Invalid email',
                ],
                'Invalid email 1' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email',
                        'email',
                        'email',
                        1,
                    ],
                    'Invalid email',
                ],
                'Invalid email 2' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example',
                        'email@example',
                        'email@example',
                        1,
                    ],
                    'Invalid email',
                ],
                'Empty validity days' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        '',
                        '',
                        '',
                        '',
                    ],
                    'Invalid value, enter an integer value greater than zero',
                ],
                'Negative validity days' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        -1,
                        -1,
                        -1,
                    ],
                    'Invalid value, enter an integer value greater than zero',
                ],
                'String validity days' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        'a',
                        'a',
                        'a',
                    ],
                    'Invalid value, enter an integer value greater than zero',
                ],
                'Zero validity days' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        0,
                        0,
                        0,
                    ],
                    'Invalid value, enter an integer value greater than zero',
                ],
                'Float validity days' => [
                    [
                        'US',
                        'State',
                        'Locality',
                        'Organization name',
                        'Organization unit name',
                        'Common name',
                        'email@example.com',
                        0.1,
                        0.1,
                        0.1,
                    ],
                    'Invalid value, enter an integer value greater than zero',
                ],
            ];
    }

    private function getCommandTester()
    {
        $command = new GenerateCertCommand();
        $command->setContainer($this->createContainerMock());

        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        return new CommandTester($application->find('stingus_jira:generate:cert'));
    }

    private function createContainerMock()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();

        $container
            ->method('getParameter')
            ->withConsecutive(
                [$this->equalTo('kernel.project_dir')],
                [$this->equalTo('stingus_jira.cert_path')]
            )
            ->willReturn(__DIR__, self::CERTS_DIR);

        return $container;
    }

    private function clearCertDir()
    {
        $directory = $this->getCertPath();

        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        $it->rewind();
        while ($it->valid()) {
            /** @var \RecursiveDirectoryIterator $it */
            if (!$it->isDot() && $it->isFile()) {
                unlink($it->getRealPath());
            }
            $it->next();
        }
    }

    private function getCertPath()
    {
        return __DIR__.DIRECTORY_SEPARATOR.self::CERTS_DIR;
    }

    private function getValidData()
    {
        return [
            'US',
            'State',
            'Locality',
            'Organization name',
            'Organization unit name',
            'Common name',
            'email@example.com',
            1,
        ];
    }
}
