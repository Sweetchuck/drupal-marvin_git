<?php

declare(strict_types = 1);

namespace Drush\Commands\marvin_git;

use Consolidation\AnnotatedCommand\Events\CustomEventAwareInterface;
use Drush\Commands\marvin\CommandsBase;
use Robo\Collection\CollectionBuilder;
use Sweetchuck\GitHooksStdInputReader\ReaderFactory;

class GitHookCommandsBase extends CommandsBase implements CustomEventAwareInterface {

  protected static string $classKeyPrefix = 'marvin.gitHook';

  protected string $customEventNamePrefix = 'marvin:git-hook';

  protected function delegatePrepareCollectionBuilder(CollectionBuilder $cb, string $eventBaseName, array $args): static {
    $cb
      ->getState()
      ->offsetSet(
        'gitHooks.stdInputReader',
        ReaderFactory::createInstance($eventBaseName, $this->getStdInputFileHandler())
      );

    return $this;
  }

  /**
   * @return resource
   */
  protected function getStdInputFileHandler() {
    return STDIN;
  }

}
