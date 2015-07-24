<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Web\Navigation;

use Countable;
use IteratorAggregate;
use Icinga\Web\Url;

/**
 * A navigation item
 *
 * @see \Icinga\Web\Navigation\NavigationRenderer For a usage example.
 * @see \Icinga\Web\Navigation\RecursiveNavigationRenderer For a usage example.
 */
class NavigationItem implements Countable, IteratorAggregate
{
    /**
     * Whether the item is active
     *
     * @var bool
     */
    protected $active = false;

    /**
     * Attributes of the item's element
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Item's children
     *
     * @var Navigation
     */
    protected $children;

    /**
     * Icon
     *
     * @var string|null
     */
    protected $icon;

    /**
     * Item's ID
     *
     * @var mixed
     */
    protected $id;

    /**
     * Label
     *
     * @var string
     */
    protected $label;

    /**
     * Parent
     *
     * @var NavigationItem
     */
    private $parent;

    /**
     * URL
     *
     * @var Url
     */
    protected $url;

    /**
     * URL parameters
     *
     * @var array
     */
    protected $urlParameters = array();

    /**
     * Create a new navigation item
     *
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        if (! empty($properties)) {
            $this->setProperties($properties);
        }
        $this->children = new Navigation();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->children->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->children;
    }

    /**
     * Get whether the item is active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set whether the item is active
     *
     * Bubbles active state.
     *
     * @param   bool    $active
     *
     * @return  $this
     */
    public function setActive($active = true)
    {
        $this->active = (bool) $active;
        $parent = $this;
        while (($parent = $parent->parent) !== null) {
            $parent->setActive($active);
        }
        return $this;
    }

    /**
     * Get an attribute's value of the item's element
     *
     * @param   string      $name       Name of the attribute
     * @param   mixed       $default    Default value
     *
     * @return  mixed
     */
    public function getAttribute($name, $default = null)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return $default;
    }

    /**
     * Set an attribute of the item's element
     *
     * @param   string  $name   Name of the attribute
     * @param   mixed   $value  Value of the attribute
     *
     * @return  $this
     */
    public function setAttribute($name, $value)
    {
        $name = (string) $name;
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Get the item's attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the item's attributes
     *
     * @param   array   $attributes
     *
     * @return  $this
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
        return $this;
    }

    /**
     * Add a child item to this item
     *
     * Bubbles active state.
     *
     * @param   NavigationItem  $child  The child to add
     *
     * @return  $this
     */
    public function addChild(NavigationItem $child)
    {
        $child->parent = $this;
        $this->children->addItem($child);
        if ($child->getActive()) {
            $this->setActive();
        }
        return $this;
    }

    /**
     * Get the item's children
     *
     * @return Navigation
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get whether the item has children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return ! $this->children->isEmpty();
    }

    /**
     * Set children
     *
     * @param   Navigation  $children
     *
     * @return  $this
     */
    public function setChildren(Navigation $children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Get the icon
     *
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the icon
     *
     * @param   string  $icon
     *
     * @return  $this
     */
    public function setIcon($icon)
    {
        $this->icon = (string) $icon;
        return $this;
    }

    /**
     * Get the item's ID
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the item's ID
     *
     * @param   mixed   $id ID of the item
     *
     * @return  $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label
     *
     * @param   string  $label
     *
     * @return  $this
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;
        return $this;
    }

    /**
     * Get the URL
     *
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the URL
     *
     * @param   Url|string $url
     *
     * @return  $this
     */
    public function setUrl($url)
    {
        if (! $url instanceof Url && strpos($url, '://') === false) {
            $url = Url::fromPath((string) $url);
        }
        $this->url = $url;
        return $this;
    }

    /**
     * Get the URL parameters
     *
     * @return array
     */
    public function getUrlParameters()
    {
        return $this->urlParameters;
    }

    /**
     * Set the URL parameters
     *
     * @param   array   $urlParameters
     *
     * @return  $this
     */
    public function setUrlParameters(array $urlParameters)
    {
        $this->urlParameters = $urlParameters;
        return $this;
    }

    /**
     * Set properties for the item
     *
     * @param   array   $properties
     *
     * @return  $this
     */
    public function setProperties(array $properties = array())
    {
        foreach ($properties as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
        return $this;
    }
}
