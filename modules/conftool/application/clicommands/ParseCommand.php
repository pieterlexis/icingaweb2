<?php

namespace Icinga\Module\Conftool\Clicommands;

use Icinga\Cli\Command;
use Icinga\Module\Conftool\Icinga\IcingaConfig;
use Icinga\Module\Conftool\Icinga2\Icinga2Command;

class ParseCommand extends Command
{
    public function v1Action()
    {
        $configfile = $this->params->shift();
        $config = IcingaConfig::parse($configfile);
        foreach ($config->getDefinitions('command') as $command) {
            Icinga2Command::fromIcingaCommand($command)->dump();
        }
    }
}
