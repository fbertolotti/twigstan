<?php

declare(strict_types=1);

namespace TwigStan\EndToEnd\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TwigStan\Application\AnalyzeCommand;
use TwigStan\DependencyInjection\ContainerFactory;

/**
 * Runs the command through Symfony's CommandTester, so exit codes and printed output are covered too.
 */
final class AnalyzeCommandTest extends TestCase
{
    public function testSucceedsWhenThereAreNoErrors(): void
    {
        $tester = $this->createTester();
        $exitCode = $tester->execute(['paths' => ['tests/EndToEnd/MoreTags']]);

        self::assertSame(Command::SUCCESS, $exitCode, $tester->getDisplay());
        self::assertStringContainsString('No errors found', $tester->getDisplay());
    }

    public function testFailsAndPrintsErrorsWithTwigLocation(): void
    {
        $tester = $this->createTester();
        $exitCode = $tester->execute(['paths' => ['tests/EndToEnd/LoaderError']]);

        self::assertSame(Command::FAILURE, $exitCode, $tester->getDisplay());
        self::assertMatchesRegularExpression('/Found \d+ errors?/', $tester->getDisplay());
        self::assertStringContainsString('🌱', $tester->getDisplay());
    }

    public function testBaselineFileMustHavePhpExtension(): void
    {
        $tester = $this->createTester();
        $exitCode = $tester->execute(['--generate-baseline' => 'baseline.json']);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('Baseline file must have .php extension', $tester->getDisplay());
    }

    private function createTester(): CommandTester
    {
        $container = ContainerFactory::fromFile(dirname(__DIR__, 3), __DIR__ . '/../../twigstan.php')->create();

        return new CommandTester($container->getByType(AnalyzeCommand::class));
    }
}
