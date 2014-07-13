<?php

namespace PaloAlto;

class FilePreRuleBaseSecurity extends File
{
    private $rules = array();

    public function parse()
    {
        $entries = $this->xmlParser->xpath('/response/result/security/rules/entry');
        if ( ! is_array($entries)) {
            throw new \RuntimeException('XML format is not what I expected, fix me!');
        }

        /* @var $xmlEntry \SimpleXMLElement */
        foreach ($entries as $xmlEntry) {

            $entryName = (string) $xmlEntry->attributes()->name;

            foreach ($xmlEntry->source->children() as $member) {
                $sources[] = (string) $member;
            }

            foreach ($xmlEntry->destination->children() as $member) {
                $destinations[] = (string) $member;
            }

            $this->rules[ $entryName ]['sources'] = $sources;
            $this->rules[ $entryName ]['destinations'] = $sources;
        }
    }
}
