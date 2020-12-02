<?php

namespace gorriecoe\LinkField\Forms\GridField;

use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataObject;

class GridFieldLinkDetailForm extends GridFieldDetailForm
{

    /**
     * @var array
     */
    protected $linkConfig;

    public function __construct($linkConfig = array(), $name = null, $showPagination = null, $showAdd = null)
    {
        parent::__construct($name, $showPagination, $showAdd);

        $this->setLinkConfig($linkConfig);
    }

    /**
     * Get the record from the http request.
     * When creating a new record, see if the HasOneLinkField has already given us a new record instance.
     *
     * {@inheritDoc}
     * @see \SilverStripe\Forms\GridField\GridFieldDetailForm::getRecordFromRequest()
     */
    protected function getRecordFromRequest(GridField $gridField, HTTPRequest $request): ?DataObject
    {
        /** @var Filterable $dataList */
        $dataList = $gridField->getList();
        $id = $request->param('ID');
        $record = null;

        /** @var DataObject $record */
        if (is_numeric($id)) {
            $record = $dataList->byID($id);
        } else {
            if ($id == 'new') {
                $record = $dataList->byID(0);
            }
            if (!$record) {
                $record = Injector::inst()->create($gridField->getModelClass());
            }
        }
        // Set the config on the record if we have one.
        if ($record) {
            $record->link_requirements = $this->getLinkConfig();
        }

        return $record;
    }

    /**
     * Set the configuration for this Link relationship.
     *
     * @param array $linkConfig
     * @return $this
     */
    public function setLinkConfig($linkConfig)
    {
        $this->linkConfig = $linkConfig;
        return $this;
    }

    /**
     * Get the configuration for this Link relationship.
     *
     * @return array
     */
    public function getLinkConfig()
    {
        return $this->linkConfig;
    }
}
