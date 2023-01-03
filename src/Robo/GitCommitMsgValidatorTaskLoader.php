<?php

declare(strict_types = 1);

namespace Drupal\marvin_git\Robo;

use Drupal\marvin_git\Robo\Task\GitCommitMsgValidatorTask;
use League\Container\ContainerAwareInterface;

trait GitCommitMsgValidatorTaskLoader {

  /**
   * @return \Robo\Collection\CollectionBuilder|\Drupal\marvin_git\Robo\Task\GitCommitMsgValidatorTask
   */
  protected function taskMarvinGitCommitMsgValidator(array $options = []) {
    /** @var \Drupal\marvin_git\Robo\Task\GitCommitMsgValidatorTask $task */
    $task = $this->task(GitCommitMsgValidatorTask::class);

    if ($this instanceof ContainerAwareInterface) {
      // @todo Check hasContainer().
      $task->setContainer($this->getContainer());
    }

    $task->setOptions($options);

    return $task;
  }

}
