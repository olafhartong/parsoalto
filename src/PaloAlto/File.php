<?php
namespace PaloAlto;

class File {

    private $fileName;
    private $fileNameParts = array();
    protected $xmlParser;

    public $reference;
    public $type;
    public $timestamp;


    /**
     */
    private function __construct()
    {
    }

    public function isShared()
    {
        return substr($this->fileNameParts['reference'], 0-strlen('.shared')) === '.shared';
    }


    /**
     * @return \SimpleXMLElement
     */
    public function getParser()
    {
        return $this->xmlParser;
    }


    /**
     * Parse the structure into a workable format.
     *
     * @throws \RuntimeException
     */
    protected function parse()
    {
        #throw new \RuntimeException('Implement '. __NAMESPACE__  .'\\'. get_class($this) .'::parse()');
    }


    /**
     * Create an empty file concrete, based on the type
     *
     * @param string $type Predefined type indicator
     * @param string $path
     *
     * @return File
     * @throws \InvalidArgumentException
     */
    static public function createFromType($type, $path)
    {
        $className = __NAMESPACE__ .'\File';
        switch ($type) {
            case 'address':
                $className .= 'Address';
                break;
            case 'address-group':
                $className .= 'AddressGroup';
                break;
            case 'application-group':
                $className .= 'ApplicationGroup';
                break;
            case 'pre-rulebase-security':
                $className .= 'PreRuleBaseSecurity';
                break;
            case 'service-group':
                $className .= 'ServiceGroup';
                break;

            default:
                throw new \InvalidArgumentException('Unsupported type "'. $type .'".');
        }

        $file = new $className;
        $file->xmlParser = new \SimpleXMLElement($path, LIBXML_NONET|LIBXML_NSCLEAN, true);

        // Initiate the parser
        $file->parse();

        return $file;
    }


    /**
     * @param string $path Path to the XML file
     *
     * @return File
     * @throws \InvalidArgumentException
     */
    static public function createAndParse($path)
    {
        $realPath = realpath($path);
        if ($realPath === false || !is_readable($realPath)) {
            throw new \InvalidArgumentException('Unable to read "'. $path .'".');
        }

        $fileName = basename($realPath);

        $fileNameParts = static::identifyFileName($fileName);

        /* @var $file File */
        $file = static::createFromType($fileNameParts['type'], $realPath);

        $file->fileName = $fileName;
        $file->fileNameParts = $fileNameParts;

        $file->reference = $fileNameParts['reference'];
        $file->type = $fileNameParts['type'];
        $file->timestamp = $fileNameParts['timestamp'];

        return $file;
    }


    /**
     * Parse, validate and returns an object
     *
     * @param $fileName
     *
     * @return \PaloAlto\File
     */
    static public function identifyFileName($fileName)
    {
        /* @var $file File */
        $file = new static();
        return $file->parseFileName($fileName);
    }


    /**
     * @param string $fileName
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    private function parseFileName($fileName)
    {
        // expected syntax: <reference>.<predefined type indicator>.<timestamp>.xml
        // Concrete example: foo-bar.address.20250101.xml
        if ( ! preg_match('/(?P<reference>[\w.-]+?)\.(?P<type>[\w-]+)\.(?P<timestamp>[\d]+)\.xml/', $fileName, $matches)) {
            throw new \InvalidArgumentException('Unsupported filename "'. $fileName .'".');
        }

        // Sanity check
        switch ($matches['type']) {
            case 'address':
            case 'address-group':
            case 'application-group':
            case 'pre-rulebase-security':
            case 'service-group':
                break;

            case 'rulebase-security':
                $matches['type'] = 'pre-rulebase-security';
                break;
            default:
                throw new \InvalidArgumentException('Unknown type "'. $matches['type'] .'".');
        }

        return array(
            'reference' => $matches['reference'],
            'type' => $matches['type'],
            'timestamp' => $matches['timestamp'],
        );
    }
}