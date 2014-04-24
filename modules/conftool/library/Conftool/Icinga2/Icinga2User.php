<?php

namespace Icinga\Module\Conftool\Icinga2;

class Icinga2User extends Icinga2ObjectDefinition
{
    protected $type = 'CheckCommand';

    protected $v1ArrayProperties = array(
        'contactgroups',
    );
    protected $v1AttributeMap = array(
        'alias' => 'display_name',
        'contactgroups' => 'groups',
    );
}
