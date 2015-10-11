<?php

namespace Tdn\AndroidTools\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Tdn\AndroidTools\Console\Command\RemoveSMSBRDupesCommand;

/**
 * Class Application.
 */
class Application extends BaseApplication
{
    const VERSION = '0.0.1';

    public function __construct()
    {
        parent::__construct('Android Tools', self::VERSION);

        $this->add(new RemoveSMSBRDupesCommand());
    }

    public function getLongVersion()
    {
        $version = parent::getLongVersion().' by <comment>Victor Passapera</comment>';
        $commit = '@git-commit@';

        if ('@'.'git-commit@' !== $commit) {
            $version .= ' ('.substr($commit, 0, 7).')';
        }

        return $version;
    }
}
