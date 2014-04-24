<?php

namespace Icinga\Module\Conftool\Icinga2;

class Icinga2Service extends Icinga2ObjectDefinition
{
    protected $type = 'Service';

    protected $v1ArrayProperties = array(
        'servicegroups',
    );

    protected $v1AttributeMap = array(
        'alias' => 'display_name',
        'servicegroups' => 'groups',
    );
}
