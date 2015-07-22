<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Web\Navigation;

/**
 * Dropdown navigation item
 *
 * @see \Icinga\Web\Navigation\RecursiveNavigationRenderer For a usage example.
 */
class DropdownItem extends NavigationItem
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = array())
    {
        parent::__construct($properties);
        $this->children->setLayout(Navigation::LAYOUT_DROPDOWN);
    }
}

