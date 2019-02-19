<?php
/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian.feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CapatainHook\Hooks\Laravel;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Action;
use CaptainHook\Hook\Laravel\Output;
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
        $options   = $action->getOptions();
        $bootstrap = $options->get('bootstrap');
        $command   = $options->get('command');
        $args      = array_merge([$command], $this->transformArgs($options->get('args', '')));

        $kernel = $this->getArtisanKernel($bootstrap);
        $input  = new ArrayInput($args);
        $output = new Output($io);
        $status = $kernel->handle($input, $output);

        $kernel->terminate($input, $status);

        if ($status !== 0) {
            throw new \Exception('Artisan command failed');
        }
    }

    /**
     * Transform argument string into array
     *
     * @param  string $args
     * @return array
     */
    private function transformArgs(string $args): array
    {
        return explode(' ', $args);
    }

    /**
     * Return the Artisan kernel
     *
     * @param  string $appBootstrap
     * @return \Illuminate\Contracts\Console\Kernel
     * @throws \Exception
     */
    private function getArtisanKernel(string $appBootstrap): Kernel
    {
        if (!self::$app) {
            $this->setupLaravelApp($appBootstrap);
        }
        return self::$app->make(Illuminate\Contracts\Console\Kernel::class);
    }

    /**
     * Create the Laravel application and kernel
     *
     * @param  string $bootstrap
     * @return void
     * @throws \Exception
     */
    private function setupLaravelApp(string $bootstrap): void
    {
        if (!file_exists($bootstrap)) {
            throw new \Exception('Laravel application bootstrap file not found');
        }
        self::$app = require_once $bootstrap;
    }
}
