<?php

namespace PaloAlto;

class FileRepository {

    private $files = array();
    private $references = array();
    private $sharedReferences = array();

    /**
     * @param File $file
     *
     * @return FileRepository
     */
    public function addFile(File $file)
    {
        if ($file->isShared()) {
            $this->sharedReferences[$file->reference] = $file->reference;
        } else {
            $this->references[$file->reference] = $file->reference;
        }

        $this->files[$file->reference][$file->type] = $file;

        return $this;
    }


    /**
     * Returns all reference groups
     *
     * @return array
     */
    public function getReferenceGroups()
    {
        return $this->references;
    }


    /**
     * @return array
     */
    public function getSharedGroups()
    {
        return $this->sharedReferences;
    }


    /**
     * @param string $group
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getTypesByReferenceGroup($group)
    {
        $this->validateReferenceGroup($group);
        return array_keys($this->files[$group]);
    }


    /**
     * Returns all files associated with a reference group
     *
     * @param string $group
     *
     * @return mixed
     */
    public function getFilesByReferenceGroup($group)
    {
        $this->validateReferenceGroup($group);
        return $this->files[$group];
    }


    /**
     * @param string $group
     *
     * @throws \InvalidArgumentException
     */
    private function validateReferenceGroup($group)
    {
        if ( ! isset($this->files[$group])) {
            throw new \InvalidArgumentException('Unknown reference group "'. $group .'".');
        }
    }
}