<?php

namespace Icinga\Module\Conftool\Icinga2;

use Icinga\Module\Conftool\Icinga\IcingaObjectDefinition;

class Icinga2ObjectDefinition
{
    protected $properties = array();
    protected $name;
    protected $v1AttributeMap = array();
    protected $v1ArrayProperties = array();
    protected $v1RejectedAttributeMap = array();
    protected $type;
    protected $_parents = array();
    protected $is_template = false;
    protected $assigns = array();
    protected $ignores = array();
    protected $imports = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __set($key, $val)
    {
        $this->properties[$key] = $val;
    }

    public function __get($key)
    {
        return $this->properties[$key];
    }

    protected function assignWhere($assign)
    {
        $this->assigns[] = $assign;
    }

    protected function ignoreWhere($ignore)
    {
        $this->ignores[] = $ignore;
    }

    protected function imports($import)
    {
        $this->imports[] = $import;
    }

    protected function setAttributesFromIcingaObjectDefinition(IcingaObjectDefinition $object)
    {
        foreach ($object->getAttributes() as $key => $value) {

            //rejects
            if ($key !== null && in_array($key, $this->v1RejectedAttributeMap)) {
                continue;
            }

            //template
            $this->is_template = $object->isTemplate();
            $this->_parents = $object->getParents();

            //ugly 1.x hacks
            if($this->is_template && ($key == "service_description" || $key == "host_name")) {
                continue; //skip invalid template attributes
            }
            if (!$this->is_template && $key == "name") {
                continue; //skip invalid object attributes
            }

            // template imports
            if ($key == "use") {
                $this->imports = $this->migrateUseImport($value, $key);
                continue;
            }

            //conversion of different attributes
            $func = 'convert' . ucfirst($key);
            if (method_exists($this, $func)) {
                $this->$func($value);
                continue;
            }

            //mapping
            if (! array_key_exists($key, $this->v1AttributeMap)) {
                throw new Icinga2ConfigMigrationException(
                    sprintf('Cannot convert the "%s" property of given v1 object: ', $key) . print_r($object, 1)
                );
            }

            //migrate
            $value = $this->migrateValue($value, $key);

            if ($value !== null) {
                $this->{ $this->v1AttributeMap[$key] } = $value;
            }
        }
    }

    //generic conversion functions
    protected function convertCheck_interval($value)
    {
        $this->check_interval = $value.'m';
    }

    protected function convertRetry_interval($value)
    {
        $this->retry_interval = $value.'m';
    }

    protected function migrateUseImport($value, $key = null)
    {
        if ($key != "use") {
                throw new Icinga2ConfigMigrationException(
                    sprintf('Wrong key "%s" as template property of given v1 object: ', $key) . print_r($object, 1)
                );
        }

        return $this->splitComma($value);
    }

    protected function migrateValue($value, $key = null)
    {
        if ($key !== null && in_array($key, $this->v1ArrayProperties)) {
            $values = array();
            foreach ($this->splitComma($value) as $value) {
                $values[] = $this->migrateValue($value);
            }
            return $values;
        }

        //special handling for address
        if ($key == "address") {
            return $this->migrateLegacyString($value);
        }
        
        if (preg_match('/^\d+/', $value)) {
            if (preg_match('/check_interval/', $key)) { //different handling for *_interval
                return $value."m";
            }
            return $value;
        }
        return $this->migrateLegacyString($value);
    }

    public static function fromIcingaObjectDefinition(IcingaObjectDefinition $object)
    {
        switch ($object->getDefinitionType()) {
            case 'command':
                $new = new Icinga2Command((string) $object);
                $new->setAttributesFromIcingaObjectDefinition($object);
                break;

            case 'host':
                $new = new Icinga2Host((string) $object);
                $new->setAttributesFromIcingaObjectDefinition($object);
                break;

            case 'service':
                //print "Found service ".$object;
                $new = new Icinga2Service((string) $object);
                $new->setAttributesFromIcingaObjectDefinition($object);
                break;

            case 'contact':
                $new = new Icinga2User((string) $object);
                $new->setAttributesFromIcingaObjectDefinition($object);
                break;

            case 'hostgroup':
                $new = new Icinga2Hostgroup((string) $object);
                $new->setAttributesFromIcingaObjectDefinition($object);
                break;

            case 'servicegroup':
                $new = new Icinga2Servicegroup((string) $object);
                $new->setAttributesFromIcingaObjectDefinition($object);
                break;

            case 'contactgroup': // TODO: find a better rename way
                $new = new Icinga2Usergroup((string) $object);
                $new->setAttributesFromIcingaObjectDefinition($object);
                break;

            default:
                throw new Icinga2ConfigMigrationException(
                    sprintf(
                        'Cannot convert unknown object "%s" of type "%s"',
                        $object,
                        $object->getDefinitionType()
                    )
                );
        }

        return $new;
    }

    protected function splitComma($string)
    {
        return preg_split('/\s*,\s*/', $string, null, PREG_SPLIT_NO_EMPTY);
    }

    protected function str2Arr($string, $delim, $unique = false, $sort = false)
    {
        $arr = preg_split('/\s*'.$delim.'\s*/', $string, null, PREG_SPLIT_NO_EMPTY);

        if ($unique == true) {
            $arr = array_unique($arr);
        }

        if ($sort == true) {
            sort($arr);
        }

        return $arr;
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

    protected function arrayToString($array)
    {
        $string = '[ ' . implode(', ', $array) . ' ]';
        return $string;
    }

    protected function getAttributesAsString()
    {
        $str = '';
        foreach ($this->properties as $key => $val) {
            if (is_array($val)) {
                $val = $this->renderArray($val);
            }
            $str .= sprintf("    %s = %s\n", $key, $val);
        }
        return $str;
    }

    protected function getImportsAsString() {
        $str = '';
        foreach ($this->imports as $import) {
            $str .= sprintf("    import \"%s\"\n", $import);
        }
        return $str;
    }

    protected function getAssignmentsAsString()
    {
        $str = '';
        foreach ($this->assigns as $assign) {
            $str .= sprintf("    assign where %s\n", $assign);
        }
        foreach ($this->ignores as $ignore) {
            $str .= sprintf("    ignore where %s\n", $ignore);
        }
        return $str;
    }

    public function isTemplate()
    {
        return $this->is_template;
    }

    public function hasImport()
    {
        return $this->has_import;
    }

 // inherits "plugin-check-command"

    public function __toString()
    {
        $prefix = "object";
        if ($this->isTemplate()) {
            $prefix = "template";
        }
        
        return sprintf(
            "%s %s \"%s\" {\n%s%s%s}\n\n",
            $prefix,
            $this->type,
            $this->name,
            $this->getImportsAsString(),
            $this->getAttributesAsString(),
            $this->getAssignmentsAsString()
        );
    }

    public function dump()
    {
        echo $this->__toString();
    }
}


