<?php

// Make sure our environment it sane
error_reporting(E_ALL & E_STRICT);
ini_set('display_errors', 1 );
date_default_timezone_set('Etc/UTC');

// All classes will be automatically loaded.
spl_autoload_register(function ($className) {
   require __DIR__ . DIRECTORY_SEPARATOR .
           'src' . DIRECTORY_SEPARATOR . str_replace(
               array('_', '\\'),
               DIRECTORY_SEPARATOR,
           $className) . '.php';
});

// Config, where can we find the XML files and how should our output file be named
$path = __DIR__ . DIRECTORY_SEPARATOR . 'configs';
$outputFileName = 'normalized.'. time() .'.csv'; // The filename is prefixed with the reference group name


if (is_file($outputFileName)) {
    unlink($outputFileName);
}

// Debug wrapper, if you don't want any output, just comment the output directive.
function dbug($str) {
    #echo $str, "\n";
}


// --
// -- After this, it's all mayhem and chaos.
// --


// Find. Sie. Files
$files = new RecursiveDirectoryIterator($path);
$fileRepository = new PaloAlto\FileRepository();


/* @var $file SplFileInfo */
foreach ($files as $file) {

    if ( ! $file->isFile()) {
        continue;
    }

    if ($file->getExtension() !== 'xml') {
        continue;
    }

    // Determining the type of file
    $paFile = PaloAlto\File::createAndParse($file->getPathname());
    $fileRepository->addFile($paFile);
}
unset($files, $file);


// Gathering all shared data, we'll be using that as fall-back over all references
foreach ($fileRepository->getSharedGroups() as $sharedGroup) {
    $sharedFiles = $fileRepository->getFilesByReferenceGroup($sharedGroup);

    $sharedAddresses = $sharedFiles['address']->getAddresses();
    $sharedAddressesGroups = $sharedFiles['address-group']->getGroups();
}
unset($sharedFiles);



// NON-SHARED
foreach ($fileRepository->getReferenceGroups() as $referenceGroup) {

    $files = $fileRepository->getFilesByReferenceGroup($referenceGroup);

    // Could be shared
    if ( ! isset($files['pre-rulebase-security'])) {
        continue;
    }

    // - Get addresses -- /response/result/address/entry (by name, element: ip-mask)
    $addresses = $files['address']->getAddresses($sharedAddresses);

    // - Get address-groups.
    // Address groups work a little different, we want the value of group members to be translated to an address.
    // For applications and services, the value of the group members is wanted. So here we need to take an
    // additional step.
    $addressGroups = $files['address-group']->getGroups();
    $addressGroupAlias = $files['address-group']->getGroupNestedMembers();

    // - Get service-group references -- /response/result/services/entry
    $serviceGroups = $files['service-group']->getGroupNestedMembers();

    // - Get application-group references -- /response/result/applications/entry
    $applicationGroups = $files['application-group']->getGroupNestedMembers();


    renderCSV(
        $files['pre-rulebase-security'],
        $addresses,
        $addressGroups,
        $serviceGroups,
        $applicationGroups,
        $addressGroupAlias
    );
}

/**
 * @param \PaloAlto\FilePreRuleBaseSecurity $file
 * @param array $addresses
 * @param array $addressGroups
 * @param array $serviceGroups
 * @param array $applicationGroups
 * @param array $addressGroupAlias
 */
function renderCSV(
    \PaloAlto\FilePreRuleBaseSecurity $file,
    $addresses,
    $addressGroups,
    $serviceGroups,
    $applicationGroups,
    $addressGroupAlias
) {
    global $outputFileName;

    /**
     * - from
     * - to
     * - source
     * - destination
     * - service
     * - application
     */

    $xmlEntries = $file->getParser()->xpath('/response/result/security/rules/entry');
    $lines = array();
    foreach ($xmlEntries as $ruleEntry) {
        $line = array();

        // ------------------------------------------------------------------------------------------
        // The output of the file is determined by the order of the elements in $line
        // If you want to add a field, simply define it in the order you want it, or
        // Add the elements in the declaration of $line (above this comment) to change
        // the order. Attributes must be fetched using ->attributes()->[attribute name]
        // elements can be accessed directly
        // ------------------------------------------------------------------------------------------


        dbug("Starting with the rule name");
        $line['rule-name'] = (string) $ruleEntry->attributes()->name;


        dbug("Starting with FROM members");
        $line['from'] = array();
        foreach ($ruleEntry->from->member as $member) {
            $line['from'][] = (string) $member;
        }


        dbug("Starting with TO members");
        $line['to'] = array();
        foreach ($ruleEntry->to->member as $member) {
            $line['to'][] = (string) $member;
        }


        dbug("Starting with SOURCE members");
        foreach ($ruleEntry->source->member as $member) {
            $strMember = (string) $member;

            if (isset($addressGroups[$strMember])) {
                $value = array();
                foreach ($addressGroupAlias[$strMember] as $groupAlias) {
                    dbug("      $strMember => $groupAlias => {$addresses[ $groupAlias ]}");
                    $value[] = $addresses[ $groupAlias ];
                }
            } else if (isset($addresses[$strMember])) {
                dbug("  $strMember is a direct association to an address ({$addresses[$strMember]})");
                $value = $addresses[$strMember];
            } else {
                dbug('  No match for "'. $strMember .'", using value as output.');
                $value = $strMember;
            }

            $line['source'][] = $value;
        }


        dbug("Starting with DESTINATION members");
        $line['destination'] = array();
        foreach ($ruleEntry->destination->member as $member) {
            $strMember = (string) $member;

            if (isset($addressGroups[$strMember])) {
                dbug("  $strMember is part of a group, translating it..");
                $value = array();
                foreach ($addressGroupAlias[$strMember] as $groupAlias) {
                    dbug("      $strMember => $groupAlias => {$addresses[ $groupAlias ]}");
                    $value[] = $addresses[ $groupAlias ];
                }
            } else if (isset($addresses[$strMember])) {
                dbug("  $strMember is a direct association to an address ({$addresses[$strMember]})");
                $value = $addresses[$strMember];
            } else {
                dbug('  No match for "'. $strMember .'", using value as output.');
                $value = $strMember;
            }

            $line['destination'][] = $value;
        }


        dbug("Starting with APPLICATION members");
        $line['application'] = array();
        foreach ($ruleEntry->application->member as $member) {
            $strMember = (string) $member;

            if (isset($applicationGroups[$strMember])) {
                dbug("  Found a group match for \"$strMember\", using it's members as value");
                /* @todo map to => alias => application */
                $value = $applicationGroups[$strMember];
            } else {
                dbug('  No match for "'. $strMember .'", using value as output.');
                $value = $strMember;
            }

            $line['application'][] = $value;
        }


        dbug("Starting with SERVICE members");
        $line['service'] = array();
        foreach ($ruleEntry->service->member as $member) {
            $strMember = (string) $member;

            if (isset($serviceGroups[$strMember])) {
                dbug("  Found a group match for \"$strMember\", using it's members as value");
                $value = $serviceGroups[$strMember];
            } else {
                dbug('  No match for "'. $strMember .'", using value as output.');
                $value = $strMember;
            }

            $line['service'][] = $value;
        }

        $lines[] = $line;
    }


    $fd = fopen($file->reference .'.'. $outputFileName, 'at');
    foreach ($lines as $line) {

        // Write a line to our output file
        fputcsv($fd, flattenLineColumns($line));
    }

    fclose($fd);
}


/**
 * This method makes sure that any multidimensional array is flattened and treated as a single column.
 *
 * @param array $line
 *
 * @return array
 */
function flattenLineColumns(array $line)
{
    // Flatten any nested arrays
    foreach ($line as & $column) {
        if (is_array($column)) {
            $return = '';

            // Here we concatenate all values
            array_walk_recursive(
                $column,
                function($a) use (&$return) {
                    $return .= ($return === '') ? $a : ", $a";
                }
            );

            $column = $return;
        }
    }

    return $line;
}
