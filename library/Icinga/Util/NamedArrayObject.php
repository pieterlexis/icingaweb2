<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Util;

use ArrayObject;

/**
 * Like ArrayObject, but can be converted to string
 */
class NamedArrayObject extends ArrayObject
{
    /**
     * string representation for $this
     *
     * @var string
     */
    protected $name;

    /**
     * @param string $name  value for $this->name
     * @param mixed ...     elements to be inserted into $this initially
     */
    public function __construct($name) {
        $argc = func_num_args();
        $argv = func_get_args();
        $args = array();

        for ($argi = 1; $argi < $argc; ++$argi) {
            $args[] = $argv[$argi];
        }

        parent::__construct($args);
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->name;
    }
}
