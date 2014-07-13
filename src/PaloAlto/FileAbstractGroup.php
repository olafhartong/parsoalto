<?php

namespace PaloAlto;

class FileAbstractGroup extends File
{
    const XPATH = "undefined";

    protected $groups = array();
    protected $members = array();

    protected function parse()
    {
        $entries = $this->xmlParser->xpath(static::XPATH);
        if ( ! is_array($entries)) {
            throw new \RuntimeException(
                'XML format is not what I expected, used xpath "'. static::XPATH .'", fix me!'
            );
        }

        /* @var $xmlEntry \SimpleXMLElement */
        foreach ($entries as $xmlEntry) {

            $name = (string) $xmlEntry->attributes()->name;
            $this->groups[$name] = $name;
            if ( ! isset($this->members[$name])) {
                $this->members[$name] = array();
            }

            foreach ($xmlEntry->children() as $member) {
                $this->members[$name][] = (string) $member;
            }
        }
    }


    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }


    /**
     * @param string $group
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getMembersForGroup($group)
    {
        if ( ! isset($this->members[ $group ])) {
            throw new \InvalidArgumentException('Unknown group "'. $group .'".');
        }

        return $this->members[ $group ];
    }


    /**
     * Returns a multidimensional array, structure: $array[<group>][] = <member>
     *
     * @return array
     */
    public function getGroupNestedMembers()
    {
        return $this->members;
    }
}
