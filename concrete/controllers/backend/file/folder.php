<?php
namespace Concrete\Controller\Backend\File;

use Concrete\Core\Application\EditResponse;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\File\Filesystem;
use Concrete\Core\File\FolderItemList;
use Concrete\Core\File\Search\ColumnSet\FolderSet;
use Concrete\Core\File\Search\Result\Result;
use Concrete\Core\File\StorageLocation\StorageLocationFactory;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Tree\Node\Type\FileFolder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Core;

class Folder extends AbstractController
{

    public function add()
    {
        $filesystem = new Filesystem();
        if ($this->request->request->has('currentFolder')) {
            $node = Node::getByID($this->request->request->get('currentFolder'));
            if (is_object($node) && $node instanceof FileFolder) {
                $folder = $node;
            }
        }

        if (!isset($folder)) {
            $folder = $filesystem->getRootFolder();
        }
        $permissions = new \Permissions($folder);
        $error = $this->app->make('error');
        $response = new EditResponse();
        $response->setError($error);
        if (!$permissions->canAddTreeSubNode()) {
            $error->add(t('You do not have permission to add a folder here.'));
        }
        $folderName = $this->request->request->get('folderName');
        if (!is_string($folderName) || trim($folderName) === '') {
            $error->add(t('Folder Name can not be empty.'));
        }

        $storageLocation = Core::make(StorageLocationFactory::class)->fetchByID($this->request->request->get('storageLocation'));

        if (!$error->has()) {
            $folder = $filesystem->addFolder($folder, $folderName);
            $fFolder = FileFolder::getByID($folder->getTreeNodeID());
            $fFolder->addFolderStorageLocation($storageLocation);
            $response->setMessage(t('Folder added.'));
            $response->setAdditionalDataAttribute('folder', $folder);
        }
        $response->outputJSON();
    }


}
