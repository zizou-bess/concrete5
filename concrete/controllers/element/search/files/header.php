<?php

namespace Concrete\Controller\Element\Search\Files;

use Concrete\Core\Controller\ElementController;
use Concrete\Core\Entity\Search\Query;
use Concrete\Core\Search\ProviderInterface;
use Core;
use Concrete\Core\File\StorageLocation\StorageLocationFactory;

class Header extends ElementController
{

    protected $query;
    protected $includeBreadcrumb = false;

    /**
     * @param boolean $includeBreadcrumb
     */
    public function setIncludeBreadcrumb($includeBreadcrumb)
    {
        $this->includeBreadcrumb = $includeBreadcrumb;
    }

    public function __construct(Query $query = null)
    {
        $this->query = $query;
        parent::__construct();
    }

    public function getElement()
    {
        return 'files/search_header';
    }

    public function view()
    {
        /**
         * @var $sLS StorageLocationFactory
         */
        $sLS = Core::make(StorageLocationFactory::class);
        $cSL = $sLS->fetchDefault();
        $this->set('currentStorageLocation', $cSL->getID());
        $this->set('storageLocations', $this->parseStorageLocations($sLS->fetchList()));
        $this->set('currentFolder', 0);
        $this->set('includeBreadcrumb', $this->includeBreadcrumb);
        $this->set('addFolderAction', \URL::to('/ccm/system/file/folder/add'));
        $this->set('query', $this->query);
        $this->set('form', \Core::make('helper/form'));
        $this->set('token', \Core::make('token'));
    }

    private function parseStorageLocations(array $storageLocations)
    {
        $pSLs = array();
        if (count($storageLocations)) {
            foreach ($storageLocations as $sl) {
                $pSLs[$sl->getID()] = $sl->getName();
            }
        }
        return $pSLs;
    }
}
