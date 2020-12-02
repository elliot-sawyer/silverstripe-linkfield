<?php

namespace gorriecoe\LinkField\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;

/**
 * Used in conjunction with LinkField, makes the types of Links available configurable.
 */
class LinkExtension extends DataExtension
{

    public function updateCMSFields(FieldList $fields)
    {
        // Hide Title field if the config requires it.
        if (!$this->shouldDisplayTitleFields()) {
            $fields->replaceField('Title', HiddenField::create('Title'));
        }

        // Set default Type value.
        $types = array_keys($this->owner->getTypes());
        $typeField = $fields->dataFieldByName('Type');
        if (!in_array($typeField->Value(), $types)) {
            $typeField->setValue($types[0]);
        }

        parent::updateCMSFields($fields);
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // re-set the Title if title fields are not editable.
        if (!$this->shouldDisplayTitleFields()) {
            $this->resetTitle();
        }
    }

    /**
     * Only display the link types as defined by the owner's configuration.
     * @param array $types
     * @see \gorriecoe\Link\Models\Link::getTypes
     */
    public function updateTypes(&$types)
    {
        $linkSpecs = $this->owner->link_requirements;
        if (!empty($linkSpecs['types'])) {
            foreach ($types as $type => $typeName) {
                if (empty($linkSpecs['types'][$type]) && !in_array($type, $linkSpecs['types'], true)) {
                    unset($types[$type]);
                }
            }
        }
    }

    protected function shouldDisplayTitleFields()
    {
        $linkSpecs = $this->owner->link_requirements;
        return !isset($linkSpecs['title_display']) || $linkSpecs['title_display'];
    }

    protected function resetTitle()
    {
        $this->owner->Title = null;
    }
}
