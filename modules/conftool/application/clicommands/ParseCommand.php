<?php

namespace Icinga\Module\Conftool\Clicommands;

use Icinga\Cli\Command;
use Icinga\Module\Conftool\Icinga\IcingaConfig;
use Icinga\Module\Conftool\Icinga2\Icinga2ObjectDefinition;

class ParseCommand extends Command
{
    public function v1Action()
    {
        $configfile = $this->params->shift();
        $config = IcingaConfig::parse($configfile);
        foreach ($config->getDefinitions('command') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object)->dump();
        }
        foreach ($config->getDefinitions('hostgroup') as $object) {
            Icinga2ObjectDefinition::fromIcingaObjectDefinition($object)->dump();
        }
    }
}
