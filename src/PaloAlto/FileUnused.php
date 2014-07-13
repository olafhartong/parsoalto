<?php

namespace PaloAlto;

class FileUnused extends File
{
    private $rules = array();

    protected function parse()
    {
        $entries = $this->xmlParser->xpath('/response/result/rules/entry');
        if ( ! is_array($entries)) {
            throw new \RuntimeException('XML format is not what I expected, fix me!');
        }

        /* @var $xmlEntry \SimpleXMLElement */
        foreach ($entries as $xmlEntry) {

            $value = (string) $xmlEntry;
            $this->rules[ $value ] = $value;
        }
    }


    /**
     * @param array $addresses
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

}
