<?php

namespace Icinga\Module\Conftool\Icinga2;

use Icinga\Module\Conftool\Icinga\IcingaCommand;

class Icinga2Command
{
    protected $properties = array();
    protected $name;

    public function __set($key, $val)
    {
        $this->properties[$key] = $val;
    }

    public function __get($key)
    {
        return $this->properties[$key];
    }

    public static function fromIcingaCommand(IcingaCommand $command)
    {
        $new = new Icinga2Command();
        $new->name = $command->command_name;
        $new->command = $new->migrateLegacyString($command->command_line);
        return $new;
    }

    protected function migrateLegacyString($string)
    {
        $string = preg_replace('~\\\~', '\\\\', $string);
        $string = preg_replace('~"~', '\\"', $string);
        return '"' . $string . '"';
    }

    protected function renderArray($array)
    {
        $parts = array();
        foreach ($array as $val) {
            $parts[] = $this->migrateLegacyString($val);
        }
        return '[ ' . implode(', ', $parts) . ' ]';
    }

    public function __toString()
    {
        return sprintf('object CheckCommand "%s" inherits "plugin-check-command" {'
            . "\n    command = %s"
            . "\n}\n\n",
            $this->name,
            $this->command
            );
    }

    public function dump()
    {
        echo $this->__toString();
    }
}


