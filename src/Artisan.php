<?php
/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian.feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CaptainHook\Hooks\Laravel;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Action;
use Illuminate\Contracts\Console\Kernel;
use SebastianFeldmann\Git\Repository;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class Artisan
 *
 * @package CapatainHook\Hook\Laravel
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhookphp/captainhook
 * @since   Class available since Release 0.9.0
 */
class Artisan implements Action
{
    /**
     * Laravel application instance
     *
     * @var \Illuminate\Foundation\Application
     */
    private static $app;

    /**
     * @var string
     */
    private $bootstrap;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $args;

    /**
     * Executes the action.
     *
     * Options for this actions are:
     *  - bootstrap | required
     *  - command   | required
     *  - args      | optional
     *
     * @param  \CaptainHook\App\Config           $config
     * @param  \CaptainHook\App\Console\IO       $io
     * @param  \SebastianFeldmann\Git\Repository $repository
     * @param  \CaptainHook\App\Config\Action    $action
     * @return void
     * @throws \Exception
     */
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $this->setup($action->getOptions());

        $kernel = $this->getArtisanKernel();
        $input  = new ArrayInput($this->getArgs());
        $output = new Output($io);
        $status = $kernel->handle($input, $output);

        $kernel->terminate($input, $status);

        if ($status !== 0) {
            throw new \Exception('Artisan command failed');
        }
    }

    /**
     * Sets and validates the required options
     *
     * @param  \CaptainHook\App\Config\Options $options
     * @throws \Exception
     */
    private function setup(Config\Options $options)
    {
        $this->bootstrap = $options->get('bootstrap', '');
        $this->command   = $options->get('command', '');
        $this->args      = $options->get('args', '');

        if (empty($this->bootstrap)) {
            throw new \Exception('Option \'bootstrap\' is missing');
        }
        if (empty($this->command)) {
            throw new \Exception('Option \'command\' is missing');
        }
    }

    /**
     * Put command and arguments in an array
     *
     * @return array
     */
    private function getArgs()
    {
        return array_filter(
            array_merge([$this->command], $this->optionArgsAsArray()),
            function($arg) {
                return !empty($arg);
            }
        );
    }

    /**
     * Return the configured arguments as array
     *
     * @return array
     */
    private function optionArgsAsArray(): array
    {
        if (empty($this->args)) {
            return [];
        }
        return explode('', $this->args);
    }

    /**
     * Return the Artisan kernel
     *
     * @return \Illuminate\Contracts\Console\Kernel
     * @throws \Exception
     */
    private function getArtisanKernel(): Kernel
    {
        if (!self::$app) {
            $this->setupLaravelApp();
        }
        return self::$app->make(Kernel::class);
    }

    /**
     * Create the Laravel application and kernel
     *
     * @return void
     * @throws \Exception
     */
    private function setupLaravelApp(): void
    {
        if (!file_exists($this->bootstrap)) {
            throw new \Exception('Laravel application bootstrap file not found');
        }
        self::$app = require_once $this->bootstrap;
    }
}
