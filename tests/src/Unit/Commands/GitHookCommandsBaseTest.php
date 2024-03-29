<?php

declare(strict_types = 1);

namespace Drupal\Tests\marvin_git\Unit\Commands;

use Drush\Commands\marvin_git\GitHookCommandsBase;
use Robo\Config\Config;

/**
 * @group marvin
 * @group marvin_git
 * @group drush-command
 *
 * @covers \Drush\Commands\marvin_git\GitHookCommandsBase
 */
class GitHookCommandsBaseTest extends CommandsTestBase {

  public function testGetClassKey(): void {
    $commands = new GitHookCommandsBase($this->composerInfo);

    $methodName = 'getClassKey';
    $class = new \ReflectionClass($commands);
    $method = $class->getMethod($methodName);
    $method->setAccessible(TRUE);

    static::assertSame('marvin.gitHook.a', $method->invokeArgs($commands, ['a']));
  }

  public function testGetConfigValue(): void {
    $configData = [
      'marvin' => [
        'gitHook' => [
          'my_key' => 'my_value',
        ],
      ],
    ];

    $configData = array_replace_recursive(
      $this->getDefaultConfigData(),
      $configData
    );
    $config = new Config($configData);

    $commands = new GitHookCommandsBase($this->composerInfo);
    $commands->setConfig($config);

    $methodName = 'getConfigValue';
    $class = new \ReflectionClass($commands);
    $method = $class->getMethod($methodName);
    $method->setAccessible(TRUE);

    static::assertSame('my_value', $method->invokeArgs($commands, ['my_key']));
  }

  public function testGetCustomEventNamePrefix(): void {
    $commands = new GitHookCommandsBase($this->composerInfo);
    $methodName = 'getCustomEventNamePrefix';
    $class = new \ReflectionClass($commands);
    $method = $class->getMethod($methodName);
    $method->setAccessible(TRUE);

    static::assertSame('marvin:git-hook', $method->invokeArgs($commands, []));
  }

}
