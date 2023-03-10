<?php

declare(strict_types = 1);

namespace Drupal\Tests\marvin_git\Integration;

/**
 * @group marvin
 * @group drush-command
 *
 * @covers \Drush\Commands\marvin_git\GitHookCommandsBase<extended>
 * @covers \Drupal\marvin\CommandDelegatorTrait
 */
class GitHookCommandsTest extends UnishIntegrationTestCase {

  public function testMarvinGitHookPreCommit(): void {
    $this->drush(
      'marvin:git-hook:pre-commit',
      [],
      $this->getCommonCommandLineOptions(),
      NULL,
      NULL,
      0,
      NULL,
      $this->getCommonCommandLineEnvVars()
    );

    $actualStdOutput = $this->getOutput();
    $actualStdError = $this->getErrorOutput();

    static::assertSame('', $actualStdError, 'StdError');
    static::assertSame('GitHookSubscriberCommands::onEventMarvinGitHookPreCommit called', $actualStdOutput, 'StdOutput');
  }

}
