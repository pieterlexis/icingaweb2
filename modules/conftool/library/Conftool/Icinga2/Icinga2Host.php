<?php

namespace Icinga\Module\Conftool\Icinga2;

class Icinga2Host extends Icinga2ObjectDefinition
{
    protected $type = 'Host';

    protected $v1ArrayProperties = array(
        'hostgroups',
    );

    protected $v1AttributeMap = array(
        //keep
        'display_name' => 'display_name',
        'address' => 'address',
        'address6' => 'address6',
        'notes' => 'notes',
        'notes_url' => 'notes_url',
        'action_url' => 'action_url',
        'icon_image' => 'icon_image',
        'icon_image_alt' => 'icon_image_alt',
        'check_interval' => 'check_interval',
        'retry_interval' => 'retry_interval',
        'check_period' => 'check_period',
        'max_check_attempts' => 'max_check_attempts',
        'check_command' => 'check_command',
        //rename
        'alias' => 'display_name',
        'hostgroups' => 'groups',
        'active_checks_enabled' => 'enable_active_checks',
        'passive_checks_enabled' => 'enable_passive_checks',
        'event_handler_enabled' => 'enable_event_handler',
        'event_handler' => 'event_command',
        'low_flap_threshold' => 'flapping_threshold',
        'high_flap_threshold' => 'flapping_threshold',
        'flap_detection_enabled' => 'enaple_flapping',
        'process_perf_data' => 'enable_perfdata',
        'notifications_enabled' => 'enable_notifications',
        'is_volatile' => 'volatile',


        //legacy attributes (nagios2)
        'normal_check_interval' => 'check_interval',
        'retry_check_interval' => 'retry_interval',
    );

    protected $v1RejectedAttributeMap = array(
        'host_name',
        'name',
        'initial_state',
        'obsess_over_host',
        'check_freshness',
        'freshness_threshold',
        'flap_detection_options',
        'failure_prediction_enabled',
        'retain_status_information',
        'retain_nonstatus_information',
        'stalking_options',
        'statusmap_image',
        '2d_coords',
        'parallelize_check',
        //ignore,
        'notification_interval',
        'first_notification_delay',
        'notification_period',
        'notification_options',
        'contacts',
        'contact_groups'
    );

    // TODO: Figure out how to handle
    // - notification_interval, first_notification_delay, notification_period, notification_options
    // in a new notification object

    // TODO
    protected function convertCheck_interval($check_interval)
    {

    }

    protected function convertParents($parents)
    {
        foreach ($this->splitComma($parents) as $parent) {
            // TODO: create a new host dependency
        }
    }

    protected function convertContacts($contacts)
    {
        // TODO: create notification objects and commands
    }

    protected function convertContactgroups($contactgroups)
    {
        // TODO: create notification objects and commands
    }
}
