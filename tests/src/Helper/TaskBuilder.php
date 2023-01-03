<?php

declare(strict_types = 1);

namespace Drupal\Tests\marvin_git\Helper;

use Consolidation\AnnotatedCommand\Output\OutputAwareInterface;
use Consolidation\Config\ConfigAwareInterface;
use Consolidation\Config\ConfigAwareTrait;
use Drupal\marvin_git\Robo\GitCommitMsgValidatorTaskLoader;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Robo\Collection\CollectionBuilder;
use Robo\Common\OutputAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\State\StateAwareInterface;
use Robo\State\StateAwareTrait;
use Robo\TaskAccessor;

class TaskBuilder implements
  BuilderAwareInterface,
  ConfigAwareInterface,
  ContainerAwareInterface,
  LoggerAwareInterface,
  OutputAwareInterface,
  StateAwareInterface {

  use TaskAccessor;
  use ConfigAwareTrait;
  use ContainerAwareTrait;
  use LoggerAwareTrait;
  use OutputAwareTrait;
  use StateAwareTrait;

  use GitCommitMsgValidatorTaskLoader {
    taskMarvinGitCommitMsgValidator as public;
  }

  public function getLogger(): LoggerInterface {
    return $this->logger;
  }

  public function collectionBuilder(): CollectionBuilder {
    // @phpstan-ignore-next-line
    return CollectionBuilder::create($this->getContainer(), NULL);
  }

}
