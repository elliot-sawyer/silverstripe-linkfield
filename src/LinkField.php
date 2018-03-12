<?php

namespace gorriecoe\LinkField;

use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverShop\HasOneField\HasOneButtonField;
use SilverStripe\Control\HasRequestHandler;
use SilverStripe\Control\HTTPRequest;

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
     * @var string $parent
     */
    protected $parent;

    public function __construct($name, $title, $parent)
    {
        parent::__construct($name, $title, null);

        $this->name = $name;
        $this->title = $title;
        $this->parent = $parent;
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
     * @return HasOneButtonField|GridField
     */
    public function Field($properties = [])
    {
        $field = null;
        $parent = $this->parent;
        switch ($parent->getRelationType($this->name)) {
            case 'has_one':
            case 'belongs_to':
                $field = HasOneButtonField::create(
                    $this->name,
                    $this->title,
                    $parent
                )
                ->setForm($this->Form);
                break;
            case 'has_many':
            case 'many_many':
            case 'belongs_many_many':
                $field = GridField::create(
                    $this->name,
                    $this->title,
                    $parent->{$this->name}(),
                    $config = GridFieldConfig::create()
                        ->addComponent(new GridFieldButtonRow('before'))
                        ->addComponent(new GridFieldAddNewButton('buttons-before-left'))
                        ->addComponent(new GridFieldDetailForm())
                        ->addComponent(new GridFieldDataColumns())
                        ->addComponent(new GridFieldOrderableRows('Sort'))
                        ->addComponent(new GridFieldDeleteAction(false))
                )
                ->setForm($this->Form);
                break;
        }

        $field->addExtraClass('link');

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
        return $this->Field()->handleRequest($request);
    }
}
