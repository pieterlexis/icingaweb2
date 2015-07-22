<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Web\Navigation;

use ArrayIterator;
use RecursiveIterator;
use Icinga\Application\Icinga;
use Icinga\Web\View;

/**
 * Renderer for single level navigation
 *
 * Usage example:
 * <code>
 * <?php
 *
 * namespace Icinga\Example;
 *
 * use Icinga\Web\Navigation\Navigation;
 * use Icinga\Web\Navigation\NavigationItem;
 * use Icinga\Web\Navigation\NavigationRenderer;
 *
 * $navigation = new Navigation();
 * $navigation
 *     ->setLayout(Navigation::LAYOUT_TABS);
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
 * $navigation
 *     ->addItem($home)
 *     ->addItem($logout);
 * $renderer = new NavigationRenderer($navigation);
 * echo $renderer
 *     ->setCssClass('example-nav')
 *     ->render();
 * </code>
 */
class NavigationRenderer implements RecursiveIterator, NavigationRendererInterface
{
    /**
     * Content to render
     *
     * @var array
     */
    private $content = array();

    /**
     * CSS class for the navigation element
     *
     * @var string|null
     */
    protected $cssClass;

    /**
     * Flags
     *
     * @var int
     */
    private $flags;

    /**
     * The heading for the navigation
     *
     * @var string
     */
    protected $heading;

    /**
     * Flag for checking whether to call begin/endMarkup() or not
     *
     * @var bool
     */
    private $inIteration = false;

    /**
     * Iterator over navigation
     *
     * @var ArrayIterator
     */
    private $iterator;

    /**
     * Current navigation
     *
     * @var Navigation
     */
    private $navigation;

    /**
     * View
     *
     * @var View|null
     */
    protected $view;

    /**
     * Create a new navigation renderer
     *
     * @param   Navigation      $navigation
     * @param   int             $flags
     */
    public function __construct(Navigation $navigation, $flags = 0)
    {
        $this->iterator = $navigation->getIterator();
        $this->navigation = $navigation;
        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return new static($this->current()->getChildren(), $this->flags & static::NAV_DISABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return $this->current()->hasChildren();
    }

    /**
     * {@inheritdoc}
     * @return \Icinga\Web\Navigation\NavigationItem
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
        if (! (bool) ($this->flags & static::NAV_DISABLE) && ! $this->inIteration) {
            $this->content[] = $this->beginMarkup();
            $this->inIteration = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $valid = $this->iterator->valid();
        if (! (bool) ($this->flags & static::NAV_DISABLE) && ! $valid && $this->inIteration) {
            $this->content[] = $this->endMarkup();
            $this->inIteration = false;
        }
        return $valid;
    }

    /**
     * Begin navigation markup
     *
     * @return string
     */
    public function beginMarkup()
    {
        $content = array();
        if ($this->flags & static::NAV_MAJOR) {
            $el = 'nav';
        } else {
            $el = 'div';
        }
        if (($elCssClass = $this->getCssClass()) !== null) {
            $elCss = ' class="' . $elCssClass . '"';
        } else {
            $elCss = '';
        }
        $content[] = sprintf(
            '<%s%s role="navigation">',
            $el,
            $elCss
        );
        $content[] = sprintf(
            '<h%1$d class="sr-only" tabindex="-1">%2$s</h%1$d>',
            static::HEADING_RANK,
            $this->getView()->escape($this->getHeading())
        );
        $content[] = $this->beginChildrenMarkup();
        return implode("\n", $content);
    }

    /**
     * End navigation markup
     *
     * @return string
     */
    public function endMarkup()
    {
        $content = array();
        $content[] = $this->endChildrenMarkup();
        if ($this->flags & static::NAV_MAJOR) {
            $content[] = '</nav>';
        } else {
            $content[] = '</div>';
        }
        return implode("\n", $content);
    }

    /**
     * Begin children markup
     *
     * @return string
     */
    public function beginChildrenMarkup()
    {
        $ulCssClass = static::CSS_CLASS_NAV;
        if ($this->navigation->getLayout() & Navigation::LAYOUT_TABS) {
            $ulCssClass .= ' ' . static::CSS_CLASS_NAV_TABS;
        }
        if ($this->navigation->getLayout() & Navigation::LAYOUT_DROPDOWN) {
            $ulCssClass .= ' ' . static::CSS_CLASS_NAV_DROPDOWN;
        }
        return '<ul class="' . $ulCssClass . '">';
    }

    /**
     * End children markup
     *
     * @return string
     */
    public function endChildrenMarkup()
    {
        return '</ul>';
    }

    /**
     * Begin item markup
     *
     * @param   NavigationItem  $item
     *
     * @return  string
     */
    public function beginItemMarkup(NavigationItem $item)
    {
        $cssClass = array();
        if ($item->getActive()) {
            $cssClass[] = static::CSS_CLASS_ACTIVE;
        }
        if ($item->hasChildren()
            && $item->getChildren()->getLayout() === Navigation::LAYOUT_DROPDOWN
        ) {
            $cssClass[] = static::CSS_CLASS_DROPDOWN;
            $item
                ->setAttribute('class', static::CSS_CLASS_DROPDOWN_TOGGLE)
                ->setIcon(static::DROPDOWN_TOGGLE_ICON)
                ->setUrl('#');
        }
        if (! empty($cssClass)) {
            $content = sprintf('<li class="%s">', implode(' ', $cssClass));
        } else {
            $content = '<li>';
        }
        return $content;
    }

    /**
     * End item markup
     *
     * @return string
     */
    public function endItemMarkup()
    {
        return '</li>';
    }

    /**
     * {@inheritdoc}
     */
    public function getCssClass()
    {
        return $this->cssClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setCssClass($class)
    {
        $this->cssClass = trim((string) $class);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeading($heading)
    {
        $this->heading = (string) $heading;
        return $this;
    }

    /**
     * Get the view
     *
     * @return View
     */
    public function getView()
    {
        if ($this->view === null) {
            $this->view = Icinga::app()->getViewRenderer()->view;
        }
        return $this->view;
    }

    /**
     * Set the view
     *
     * @param   View    $view
     *
     * @return  $this
     */
    public function setView(View $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Render an item to HTML
     *
     * @param   NavigationItem  $item
     *
     * @return  string
     */
    public function renderItem(NavigationItem $item)
    {
        $view = $this->getView();
        $label = $view->escape($item->getLabel());
        if (($icon = $item->getIcon()) !== null) {
            $label = $view->icon($icon) . $label;
        }
        if (($url = $item->getUrl()) !== null) {
            $content = sprintf(
                '<a%s href="%s">%s</a>',
                $view->propertiesToString($item->getAttributes()),
                $view->url($item->getUrl(), $item->getUrlParameters()),
                $label
            );
        } else {
            $content = sprintf(
                '<%1$s%2$s>%3$s</%1$s>',
                static::LINK_ALTERNATIVE,
                $view->propertiesToString($item->getAttributes()),
                $label
            );
        }
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        foreach ($this as $navigationItem) {
            /** @var \Icinga\Web\Navigation\NavigationItem $navigationItem */
            $this->content[] = $this->beginItemMarkup($navigationItem);
            $this->content[] = $this->renderItem($navigationItem);
            $this->content[] = $this->endItemMarkup();
        }
        return implode("\n", $this->content);
    }
}
