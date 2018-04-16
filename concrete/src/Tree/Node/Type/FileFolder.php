<?php

namespace Concrete\Core\Tree\Node\Type;

use Concrete\Core\Entity\File\StorageLocation\StorageLocation;
use Concrete\Core\File\FolderItemList;
use Concrete\Core\File\Search\ColumnSet\FolderSet;
use Concrete\Core\Tree\Node\Node as TreeNode;
use Concrete\Core\Tree\Node\Type\Formatter\CategoryListFormatter;
use Concrete\Core\Tree\Node\Type\Menu\CategoryMenu;
use Concrete\Core\Tree\Node\Type\Menu\FileFolderMenu;
use Concrete\Core\User\User;
use Gettext\Translations;
use Loader;
use Symfony\Component\HttpFoundation\Request;
use Core;

class FileFolder extends Category
{

    public function getPermissionResponseClassName()
    {
        return '\\Concrete\\Core\\Permission\\Response\\FileFolderResponse';
    }

    public function getPermissionAssignmentClassName()
    {
        return '\\Concrete\\Core\\Permission\\Assignment\\FileFolderAssignment';
    }

    public function getPermissionObjectKeyCategoryHandle()
    {
        return 'file_folder';
    }

    public function getTreeNodeTypeName()
    {
        return t('Folder');
    }

    public function getTreeNodeMenu()
    {
        return new FileFolderMenu($this);
    }

    public function getTreeNodeJSON()
    {
        $node = TreeNode::getTreeNodeJSON();
        if ($node) {
            $node->isFolder = true;
            $node->resultsThumbnailImg = $this->getListFormatter()->getIconElement();
        }
        return $node;
    }

    public function getTreeNodeName()
    {
        if ($this->getTreeNodeParentID() == 0) {
            return t('File Manager');
        }
        return parent::getTreeNodeName();
    }

    public function getFolderItemList(User $u = null, Request $request)
    {
        $available = new FolderSet();
        $sort = false;
        $list = new FolderItemList();
        $list->filterByParentFolder($this);
        if (is_object($u)) {
            if (($column = $request->get($list->getQuerySortColumnParameter())) && ($direction = $request->get($list->getQuerySortDirectionParameter()))) {
                if (is_object($available->getColumnByKey($column)) && ($direction == 'asc' || $direction == 'desc')) {
                    $sort = array($column, $direction);
                    $u->saveConfig(sprintf('file_manager.sort.%s', $this->getTreeNodeID()), json_encode($sort));
                }
            } else {
                $sort = $u->config(sprintf('file_manager.sort.%s', $this->getTreeNodeID()));
                if ($sort) {
                    $sort = json_decode($sort);
                }
            }
            if (is_array($sort)) {
                if ($sortColumn = $available->getColumnByKey($sort[0])) {
                    $sortColumn->setColumnSortDirection($sort[1]);
                    $list->sortBySearchColumn($sortColumn);
                }
            }
        }
        return $list;
    }

    public function exportTranslations(Translations $translations)
    {
        return false;
    }

    public function addFolderStorageLocation(StorageLocation $sL)
    {
        $db = Core::make('database');
        if(!$this->getFolderStorageLocation()){
            $db->executeQuery('insert into FileFolderStorageLocations (folderID, storageLocationID) values (?, ?)', [$this->getTreeNodeID(), $sL->getID()]);
        }
    }

    public function getFolderStorageLocation()
    {
        $db = Core::make('database');
        $r = $db->executeQuery('select * from FileFolderStorageLocations WHERE folderID=?', [$this->getTreeNodeID()]);
        if ($r instanceof Statement) {
            return $r->fetch();
        }
        return false;
    }


}
