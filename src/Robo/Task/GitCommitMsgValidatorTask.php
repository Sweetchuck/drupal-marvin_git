<?php

declare(strict_types = 1);

namespace Drupal\marvin_git\Robo\Task;

use Drupal\marvin\Robo\Task\BaseTask;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\TaskInterface;
use Robo\State\Data as RoboStateData;
use Sweetchuck\Robo\String\StringTaskLoader;
use Sweetchuck\Utils\Filter\EnabledFilter;
use Sweetchuck\Utils\Filter\FilterInterface;

/**
 * @todo This task shouldn't deal with a file, just with the commit message string directly.
 */
class GitCommitMsgValidatorTask extends BaseTask implements BuilderAwareInterface {

  use StringTaskLoader;

  protected string $taskName = 'Marvin - Git commit message validator';

  protected string $fileName = '';

  public function getFileName(): string {
    return $this->fileName;
  }

  public function setFileName(string $fileName): static {
    $this->fileName = $fileName;

    return $this;
  }

  /**
   * @phpstan-var array<string, marvin-git-commit-msg-validator-rule>
   */
  protected array $rules = [];

  /**
   * @phpstan-return array<string, marvin-git-commit-msg-validator-rule>
   */
  public function getRules(): array {
    return $this->rules;
  }

  /**
   * @phpstan-param array<string, marvin-git-commit-msg-validator-rule> $rules
   */
  public function setRules(array $rules): static {
    $this->rules = $rules;

    return $this;
  }

  /**
   * @phpstan-param marvin-git-commit-msg-validator-rule $rule
   */
  public function addRule(array $rule): static {
    $this->rules[$rule['name']] = $rule;

    return $this;
  }

  /**
   * @phpstan-param string|marvin-git-commit-msg-validator-rule $rule
   */
  public function removeRule($rule): static {
    $ruleName = is_array($rule) ? $rule['name'] : (string) $rule;
    unset($this->rules[$ruleName]);

    return $this;
  }

  /**
   * @phpstan-param marvin-git-robo-task-commit-msg-validator-options $options
   */
  public function setOptions(array $options): static {
    parent::setOptions($options);

    if (array_key_exists('fileName', $options)) {
      $this->setFileName($options['fileName']);
    }

    if (array_key_exists('rules', $options)) {
      $this->setRules($options['rules']);
    }

    return $this;
  }

  protected function runAction(): static {
    $result = $this
      ->collectionBuilder()
      ->addCode($this->getTaskRead($this->getFileName()))
      ->addTask($this->getTaskSanitize())
      ->addCode($this->getTaskValidate())
      ->run();

    $this->actionExitCode = $result->getExitCode();
    $this->actionStdOutput = $result->getOutputData() ?? '';
    $this->actionStdError = $result->getMessage();

    return $this;
  }

  protected function getTaskRead(string $commitMsgFileName): \Closure {
    return function (RoboStateData $data) use ($commitMsgFileName): int {
      $content = @file_get_contents($commitMsgFileName);
      if ($content === FALSE) {
        throw new \RuntimeException(
          sprintf('Read file content from "%s" file failed', $commitMsgFileName),
          1
        );
      }

      $data['commitMsg'] = $content;

      return 0;
    };
  }

  protected function getTaskSanitize(): TaskInterface {
    return $this
      ->taskStringUnicode()
      ->callReplaceMatches('/(^|(\r\n)|(\n\r)|\r|\n)#([^\r\n]*)|$/', '')
      ->callTrim("\n\r")
      ->setAssetNamePrefix('commitMsg.')
      ->deferTaskConfiguration('setString', 'commitMsg');
  }

  protected function getTaskValidate(): \Closure {
    return function (RoboStateData $data): int {
      $exitCode = 0;
      foreach ($this->getPreparedRules() as $rule) {
        // @todo Pattern validation.
        if (preg_match($rule['pattern'], $data['commitMsg.string']) !== 1) {
          $logEntry = $this->getRuleErrorLogEntry($rule);
          $this->logger->error($logEntry['message'], $logEntry['context']);
          $exitCode = 1;
        }
      }

      if ($exitCode) {
        $this->logger->error(
          "The actual commit message is:\nBEGIN\n<info>{commitMessage}</info>\nEND",
          [
            'commitMessage' => $data['commitMsg.string'],
          ]
        );
      }

      return $exitCode;
    };
  }

  /**
   * @phpstan-return array<marvin-git-commit-msg-validator-rule>
   */
  protected function getPreparedRules(): array {
    $rules = $this->getRules();
    foreach (array_keys($rules) as $ruleName) {
      $this->applyDefaultsToRule($ruleName, $rules[$ruleName]);
    }

    return array_filter($rules, $this->getRuleFilter());
  }

  /**
   * @phpstan-param marvin-git-commit-msg-validator-rule $rule
   */
  protected function applyDefaultsToRule(string $ruleName, array &$rule): static {
    $rule['name'] = $ruleName;
    $rule += [
      'enabled' => TRUE,
      'description' => '- Missing -',
      'examples' => [],
    ];

    return $this;
  }

  /**
   * @phpstan-return \Sweetchuck\Utils\Filter\FilterInterface<marvin-git-commit-msg-validator-rule>
   */
  protected function getRuleFilter(): FilterInterface {
    return new EnabledFilter();
  }

  /**
   * @phpstan-param marvin-git-commit-msg-validator-rule $rule
   *
   * @phpstan-return array{
   *   context: array<string, string>,
   *   message: string,
   * }
   */
  protected function getRuleErrorLogEntry(array $rule): array {
    $entry = [
      'context' => [
        'ruleName' => $rule['name'],
      ],
      'message' => [
        'Commit message validation with rule <info>{ruleName}</info> failed.',
        $rule['description'],
      ],
    ];

    $examples = array_filter($rule['examples'] ?? [], new EnabledFilter());
    foreach ($examples as $example) {
      $example += [
        'is_valid' => TRUE,
      ];
      $example += [
        'description' => ($example['is_valid'] ?
          'The following example is valid.'
          : 'The following example is in valid.'
        ),
      ];
      $entry['message'][] = $example['description'];
      $entry['message'][] = $example['example'];
    }

    $entry['message'] = implode(PHP_EOL, $entry['message']);

    return $entry;
  }

}
