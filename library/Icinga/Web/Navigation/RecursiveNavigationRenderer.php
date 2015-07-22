<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Web\Navigation;

use RecursiveIteratorIterator;
use Icinga\Web\View;

/**
 * Renderer for multi level navigation
 *
 * @method NavigationRenderer getInnerIterator() {
 *     {@inheritdoc}
 * }
 *
 * Usage example:
 * <code>
 * <?php
 *
 * namespace Icinga\Example;
 *
 * use Icinga\Web\Navigation\DropdownItem;
 * use Icinga\Web\Navigation\Navigation;
 * use Icinga\Web\Navigation\NavigationItem;
 * use Icinga\Web\Navigation\RecursiveNavigationRenderer;
 *
 * $navigation = new Navigation();
 * $navigation->setLayout(Navigation::LAYOUT_TABS);
 * $home = new NavigationItem();
 * $home
 *     ->setIcon('home')
 *     ->setLabel('Home');
 *     ->setUrl('/home');
 * $logout = new NavigationItem();
 * $logout
 *     ->setIcon('logout')
 *     ->setLabel('Logout')
 *     ->setUrl('/logout');
 * $dropdown = new DropdownItem();
 * $dropdown
 *     ->setIcon('user')
 *     ->setLabel('Preferences');
 * $preferences = new NavigationItem();
 * $preferences
 *     ->setIcon('preferences');
 *     ->setLabel('preferences')
 *     ->setUrl('/preferences');
 * $dropdown->addChild($preferences);
 * $navigation
 *     ->addItem($home)
 *     ->addItem($logout);
 *     ->addItem($dropdown);
 * $renderer = new RecursiveNavigationRenderer($navigation);
 * echo $renderer
 *     ->setCssClass('example-nav')
 *     ->render();
 * </code>
 */
class RecursiveNavigationRenderer extends RecursiveIteratorIterator implements NavigationRendererInterface
{
    /**
     * Content to render
     *
     * @var array
     */
    private $content = array();

    /**
     * Create a new recursive navigation renderer
     *
     * @param   Navigation  $navigation
     * @param   int         $flags
     */
    public function __construct(Navigation $navigation, $flags = 0)
    {
        $navigationRenderer = new NavigationRenderer($navigation, $flags & static::NAV_DISABLE);
        parent::__construct($navigationRenderer, RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * {@inheritdoc}
     */
    public function beginIteration()
    {
        $this->content[] = $this->getInnerIterator()->beginMarkup();
    }

    /**
     * {@inheritdoc}
     */
    public function endIteration()
    {
        $this->content[] = $this->getInnerIterator()->endMarkup();
    }

    /**
     * {@inheritdoc}
     */
    public function beginChildren()
    {
        $this->content[] = $this->getInnerIterator()->beginChildrenMarkup();
    }

    /**
     * {@inheritdoc}
     */
    public function endChildren()
    {
        $this->content[] = $this->getInnerIterator()->endChildrenMarkup();
    }

    /**
     * {@inheritdoc}
     */
    public function getCssClass()
    {
        return $this->getInnerIterator()->getCssClass();
    }

    /**
     * {@inheritdoc}
     */
    public function setCssClass($class)
    {
        $this->getInnerIterator()->setCssClass($class);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeading()
    {
        return $this->getInnerIterator()->getHeading();
    }

    /**
     * {@inheritdoc}
     */
    public function setHeading($heading)
    {
        $this->getInnerIterator()->setHeading($heading);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        foreach ($this as $key => $navigationItem) {
            /** @var \Icinga\Web\Navigation\NavigationItem $navigationItem */
            $this->content[] = $this->getInnerIterator()->beginItemMarkup($navigationItem);
            $this->content[] = $this->getInnerIterator()->renderItem($navigationItem);
            if (! $navigationItem->hasChildren()) {
                $this->content[] = $this->getInnerIterator()->endItemMarkup();
            }
        }
        return implode("\n", $this->content);
    }
}
