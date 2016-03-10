<?php

// File names passed via cli arguments
$O = $argv[1];
$A = $argv[2];
$B = $argv[3];

// Dates fields to merge
$dates = array("post_modified", "post_modified_gmt");

$mergeCommand = 'git merge-file -L mine -L base -L theirs ' . $A . ' ' . $O . ' ' . $B;

$oFile = file_get_contents($O);
$aFile = file_get_contents($A);
$bFile = file_get_contents($B);


foreach ($dates as $date) {

    // Find values
    $dateMatchPattern = "/" . $date . " = \"([^'\"]*)\"/";
    $dateReplacePattern = "/(" . $date . " = \")([0-9 :-]*)(\")/";
    $matches = array();
    preg_match($dateMatchPattern, $aFile, $matches);

    // If file does not contain field, we will skip date replacement
    if (count($matches) == 0) {
        break;
    }
    $aDateString = $matches[1];

    preg_match($dateMatchPattern, $bFile, $matches);
    $bDateString = $matches[1];

    $aDate = new DateTime($aDateString);
    $bDate = new DateTime($bDateString);
    // Replace date value in both files to be more recent
    if ($aDate->getTimestamp() > $bDate->getTimestamp()) {
        $bFile = preg_replace($dateReplacePattern, '${1}' . $aDateString . '${3}', $bFile);
    } else {

        $aFile = preg_replace($dateReplacePattern, '${1}' . $bDateString . '${3}', $aFile);
    }

}
// Add temporary placeholder between adjacent lines to prevent merge conflicts
file_put_contents($B, preg_replace('/(\r\n|\r|\n)/', "$1###VP###\n", $bFile));
file_put_contents($A, preg_replace('/(\r\n|\r|\n)/', "$1###VP###\n", $aFile));
file_put_contents($O, preg_replace('/(\r\n|\r|\n)/', "$1###VP###\n", $oFile));

// Call git merge command and receive the exitcode
exec($mergeCommand, $dummy, $mergeExitCode);

// Remove temporary placeholders
file_put_contents($B, str_replace("###VP###\n", '', file_get_contents($B)));
file_put_contents($A, str_replace("###VP###\n", '', file_get_contents($A)));
file_put_contents($O, str_replace("###VP###\n", '', file_get_contents($O)));

exit ($mergeExitCode);
