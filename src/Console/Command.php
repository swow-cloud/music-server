<?php
/**
 * This file is part of SwowCloud
 * @license  https://github.com/swow-cloud/websocket-server/blob/main/LICENSE
 */

declare(strict_types=1);

namespace SwowCloud\WsServer\Console;

use Nette\Utils\Strings;
use Swow;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    /**
     * The name of the command.
     */
    protected string|null $name = null;

    protected InputInterface $input;

    protected SymfonyStyle $output;

    /**
     * The default verbosity of output commands.
     */
    protected int $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * The mapping between human readable verbosity levels and Symfony's OutputInterface.
     */
    protected array $verbosityMap = [
        'v' => OutputInterface::VERBOSITY_VERBOSE,
        'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv' => OutputInterface::VERBOSITY_DEBUG,
        'quiet' => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    public function __construct(string $name = null)
    {
        if (!$name && $this->name) {
            $name = $this->name;
        }
        parent::__construct($name);
    }

    /**
     * Run the console command.
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->output = new SymfonyStyle($input, $output);

        return parent::run($this->input = $input, $this->output);
    }

    /**
     * Format input to textual table.
     */
    public function table(
        array $headers,
        array $rows,
        null|string $tableStyle = 'default',
        array $columnStyles = []
    ): void {
        $table = new Table($this->output);

        $table->setHeaders($headers)
            ->setRows($rows)
            ->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * Write a string as standard output.
     */
    public function line(mixed $string, mixed $style = null, mixed $verbosity = null): void
    {
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        $styled = $style ? "<{$style}>{$string}</{$style}>" : $string;
        $this->output->writeln($styled, $this->parseVerbosity($verbosity));
    }

    /**
     * Write a string as information output.
     */
    public function info(mixed $string, mixed $verbosity = null): void
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as comment output.
     */
    public function comment(mixed $string, mixed $verbosity = null): void
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     */
    public function question(mixed $string, mixed $verbosity = null): void
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     */
    public function error(mixed $string, mixed $verbosity = null): void
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     */
    public function warn(mixed $string, mixed $verbosity = null): void
    {
        if (!$this->output->getFormatter()
            ->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()
                ->setStyle('warning', $style);
        }
        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Write a string in an alert box.
     */
    public function alert(mixed $string): void
    {
        $length = Strings::length(strip_tags($string)) + 12;
        $this->comment(str_repeat('*', $length));
        $this->comment('*     ' . $string . '     *');
        $this->comment(str_repeat('*', $length));
        $this->output->newLine();
    }

    protected function parseVerbosity($level = null): int
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (!is_int($level)) {
            $level = $this->verbosity;
        }

        return $level;
    }

    protected function execute(InputInterface $input, OutputInterface $output): mixed
    {
        $callback = function () {
            return call([$this, 'handle']);
        };

        return $callback();
    }

    abstract protected function bootStrap(): void;

    /**
     * Handle the current command.
     */
    abstract public function handle(): mixed;

    protected function showLogo(): void
    {
        $this->output->table(['APP_NAME', 'PHP_VERSION', 'SWOW_VERSION', 'APP_VERSION'], [
            [
                env('APP_NAME'), PHP_VERSION, Swow::VERSION, env('APP_VERSION'),
            ],
        ]);
        $this->output->writeln(sprintf('<info>%s</info>', <<<'LOGO'
 ____                             ____ _                 _ 
/ ___|_      _______      __     / ___| | ___  _   _  __| |
\___ \ \ /\ / / _ \ \ /\ / /____| |   | |/ _ \| | | |/ _` |
 ___) \ V  V / (_) \ V  V /_____| |___| | (_) | |_| | (_| |
|____/ \_/\_/ \___/ \_/\_/       \____|_|\___/ \__,_|\__,_|
LOGO));
        $this->output->writeln([
            '<info>WebSocket For Swow-Cloud</info>',
            '<info>===================================</info>',
            '',
        ]);
        $this->output->writeln([
            '<comment>If You Want To Exit, You Can Press Ctrl + C To Exit#.<comment>',
            '<info>====================================================</info>',
        ]);
    }
}
