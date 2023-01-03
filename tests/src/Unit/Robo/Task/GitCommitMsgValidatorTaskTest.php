<?php

declare(strict_types = 1);

namespace Drupal\Tests\marvin_git\Unit\Robo\Task;

use Drupal\Tests\marvin_git\Unit\TaskTestBase;

/**
 * @group marvin
 * @group marvin_git
 * @group robo-task
 *
 * @covers \Drupal\marvin_git\Robo\Task\GitCommitMsgValidatorTask
 * @covers \Drupal\marvin_git\Robo\GitCommitMsgValidatorTaskLoader
 */
class GitCommitMsgValidatorTaskTest extends TaskTestBase {

  /**
   * @phpstan-return array<string, mixed>
   */
  public function casesRunSuccess(): array {
    return [
      'basic' => [
        [
          'exitCode' => 0,
          'stdOutput' => '',
          'stdError' => implode("\n", [
            ' [Marvin - Git commit message validator] ',
            ' [String] replaceMatches, trim',
            '',
          ]),
        ],
        [
          'fileName' => $this->getDataBase64FileNameFromLines([
            '# My comment',
            'My subject',
            '',
          ]),
          'rules' => [
            'subjectLine' => [
              'pattern' => '/^My .+/u',
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * @phpstan-param array<string, mixed> $expected
   * @phpstan-param array<string, mixed> $options
   *
   * @dataProvider casesRunSuccess
   */
  public function testRunSuccess(array $expected, array $options): void {
    $task = $this
      ->taskBuilder
      ->taskMarvinGitCommitMsgValidator($options)
      ->setContainer($this->container);

    $result = $task->run();

    if (array_key_exists('exitCode', $expected)) {
      static::assertSame($expected['exitCode'], $result->getExitCode());
    }

    /** @var \Drupal\Tests\marvin_git\Helper\DummyOutput $stdOutput */
    $stdOutput = $this->container->get('output');
    if (array_key_exists('stdOutput', $expected)) {
      static::assertSame(
        $expected['stdOutput'],
        $stdOutput->output,
        'stdOutput',
      );
    }

    if (array_key_exists('stdError', $expected)) {
      static::assertSame(
        $expected['stdError'],
        $stdOutput->getErrorOutput()->output,
        'stdError',
      );
    }
  }

  /**
   * @param string[] $lines
   */
  protected function getDataBase64FileNameFromLines(array $lines): string {
    return 'data://text/plain;base64,' . base64_encode(implode(PHP_EOL, $lines));
  }

}
