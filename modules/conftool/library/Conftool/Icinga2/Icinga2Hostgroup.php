<?php

namespace Icinga\Module\Conftool\Icinga2;

class Icinga2Hostgroup extends Icinga2ObjectDefinition
{
    protected $type = 'HostGroup';

    protected $v1ArrayProperties = array(
        'hostgroup_members',
        'members'
    );

    protected $v1AttributeMap = array(
        'members'           => 'members',
        'alias'             => 'display_name',
        'notes'             => 'vars.notes',
        'action_url'        => 'vars.action_url',
        'notes_url'         => 'vars.notes_url',
        'hostgroup_members' => 'groups',
    );

    protected function convertMembers($members)
    {
    }
}
