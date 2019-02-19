<?php
/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian.feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CaptainHook\Hook\Laravel;

use CaptainHook\App\Console\IO;
use Symfony\Component\Console\Output\Output as SymfonyOutput;

class Output extends SymfonyOutput
{
    /**
     * @var \CaptainHook\App\Console\IO
     */
    private $io;

    /**
     * Output constructor
     *
     * @param \CaptainHook\App\Console\IO $io
     */
    public function __construct(IO $io)
    {
        parent::__construct(SymfonyOutput::VERBOSITY_NORMAL, false);

        $this->io = $io;
    }

    /**
     * Writes a message to the output.
     *
     * @param string $message A message to write to the output
     * @param bool   $newline Whether to add a newline or not
     */
    protected function doWrite($message, $newline)
    {
        $this->io->write($message, $newline);
    }
}
