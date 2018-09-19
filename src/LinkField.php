<?php

namespace gorriecoe\LinkField;

use gorriecoe\Link\Models\Link;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Control\HasRequestHandler;
use SilverStripe\Control\HTTPRequest;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverShop\HasOneField\HasOneButtonField;

/**
 * LinkField
 *
 * @package silverstripe-linkfield
 */
class LinkField extends FormField
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var DataObject $parent
     */
    protected $parent;

    /**
     * @var DataObject $parent
     */
    protected $record;

    /**
     * The column to be used for sorting
     * @var string
     */
    protected $sortColumn = 'Sort';

    public function __construct($name, $title, $parent)
    {
        parent::__construct($name, $title, null);

        $this->name = $name;
        $this->title = $title;
        $this->parent = $parent;
        if ($this->isOneOrMany() == 'one') {
            $this->record = $parent->{$name}();
        }
        $this->setForm($parent->Form);
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param array $properties
     * @return CompositeField|GridField
     */
    public function Field($properties = [])
    {
        Requirements::css('gorriecoe/silverstripe-linkfield: client/dist/linkfield.css');
        $field = null;
        $parent = $this->parent;
        switch ($this->isOneOrMany()) {
            case 'one':
                $relationship = $parent->{$this->name}();
                $field = CompositeField::create(
                    $this->getHasOneField(),
                    LiteralField::create(
                        $this->name . 'View',
                        ($relationship->exists()) ? $relationship->Layout : ''
                    )->addExtraClass('linkfield__view')
                );
                break;
            case 'many':
                $field = $this->getManyField();
                break;
            default:
                $field = LiteralField::create(
                    $this->name . 'Save',
                    _t(
                        __CLASS__ . '.PLEASESAVEOBJECTTOADDLINKS',
                        'Please save {object} first to add {links}',
                        [
                            'object' => $parent->i18n_singular_name(),
                            'links' => singleton(Link::class)->i18n_plural_name()
                        ]
                    )
                );
                break;
        }
        $field->addExtraClass('linkfield');

        $this->extend('updateField', $field);
        return $field;
    }

    /**
     * @param HTTPRequest $request
     * @return array|RequestHandler|HTTPResponse|string
     * @throws HTTPResponse_Exception
     */
    public function handleRequest(HTTPRequest $request)
    {
        switch ($this->isOneOrMany()) {
            case 'one':
                return $this->getHasOneField()->handleRequest($request);
            case 'many':
                return $this->getManyField()->handleRequest($request);
        }

    }

    /**
     * @return string|null
     */
    public function isOneOrMany()
    {
        $parent = $this->parent;
        if (!$parent->exists()) {
            return false;
        }
        switch ($parent->getRelationType($this->name)) {
            case 'has_one':
            case 'belongs_to':
                return 'one';
            case 'has_many':
            case 'many_many':
            case 'belongs_many_many':
                return 'many';
        }
    }

    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @return HasOneButtonField
     */
    public function getHasOneField()
    {
        return HasOneButtonField::create(
            $this->name,
            $this->title,
            $this->parent
        )->setForm($this->Form);
    }

    /**
     * @return GridField
     */
    public function getManyField()
    {
        $config = GridFieldConfig::create()
            ->addComponent(new GridFieldButtonRow('before'))
            ->addComponent(new GridFieldAddNewButton('buttons-before-left'))
            ->addComponent(new GridFieldDetailForm())
            ->addComponent(new GridFieldDataColumns())
            ->addComponent(new GridFieldOrderableRows($this->getSortColumn()))
            ->addComponent(new GridFieldEditButton())
            ->addComponent(new GridFieldDeleteAction(false));
        $config->getComponentByType(GridFieldDataColumns::class)
            ->setDisplayFields([
                'Layout' => _t(__CLASS__ . '.LINK', 'Link')
            ]);
        return GridField::create(
            $this->name,
            $this->title,
            $this->parent->{$this->name}(),
            $config
        )->setForm($this->Form);
    }

    /**
     * Set the column to be used for sorting
     * @param string $sortColumn
     * @return $this
     */
    public function setSortColumn($sortColumn)
    {
        $this->sortColumn = $sortColumn;
        return $this;
    }

    /**
     * Returns the column to be used for sorting
     * @return string
     */
    public function getSortColumn()
    {
        return $this->sortColumn;
    }
}
