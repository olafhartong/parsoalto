<?php

namespace PaloAlto;

class FileAddress extends File
{
    private $addresses = array();

    protected function parse()
    {
        $entries = $this->xmlParser->xpath('/response/result/address/entry');
        if ( ! is_array($entries)) {
            throw new \RuntimeException('XML format is not what I expected, fix me!');
        }

        /* @var $xmlEntry \SimpleXMLElement */
        foreach ($entries as $xmlEntry) {

            $name = (string) $xmlEntry->attributes()->name;
            $ipNetMask = (string) $xmlEntry->children()->{'ip-netmask'};

            // For host names, $ipNetMask will be empty and the name should be used instead
            $this->addresses[ $name ] = (!$ipNetMask) ? $name : $ipNetMask;
        }
    }


    /**
     * Return all the addresses, parsed as <name> (attribute) => <ip netmask> (element value)
     *
     * @param array $addresses
     *
     * @return array
     */
    public function getAddresses(array $addresses = array())
    {
        if ($addresses) {
            $result = array_merge($addresses, $this->addresses);
        } else {
            $result = $this->addresses;
        }

        return $result;
    }

}
