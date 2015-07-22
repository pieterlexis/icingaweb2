<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Web\Navigation;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Container for navigation items
 *
 * @see \Icinga\Web\Navigation\NavigationRenderer For a usage example.
 * @see \Icinga\Web\Navigation\RecursiveNavigationRenderer For a usage example.
 */
class Navigation implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Flag for dropdown layout
     *
     * @var int
     */
    const LAYOUT_DROPDOWN = 1;

    /**
     * Flag for tabs layout
     *
     * @var int
     */
    const LAYOUT_TABS = 2;

    /**
     * Navigation items
     *
     * @var NavigationItem[]
     */
    protected $items = array();

    /**
     * Navigation layout
     *
     * @var int
     */
    protected $layout;

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Ad a navigation item
     *
     * @param   NavigationItem  $item  The item to append
     *
     * @return  $this
     */
    public function addItem(NavigationItem $item)
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Get the item with the given ID
     *
     * @param   mixed   $id
     * @param   mixed   $default
     *
     * @return  NavigationItem|mixed
     */
    public function getItem($id, $default = null)
    {
        if (isset($this->items[$id])) {
            return $this->items[$id];
        }
        return $default;
    }

    /**
     * Get the items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get whether the navigation has items
     *
     * @return bool
     */
    public function hasItems()
    {
        return ! empty($this->items);
    }

    /**
     * Get whether the navigation is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Get the layout
     *
     * @return int
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set the layout
     *
     * @param   int $layout
     *
     * @return  $this
     */
    public function setLayout($layout)
    {
        $this->layout = (int) $layout;
        return $this;
    }
}

