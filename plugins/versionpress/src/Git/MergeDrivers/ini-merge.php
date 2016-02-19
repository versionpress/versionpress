<?php

$O = $argv[1];
$A = $argv[2];
$B = $argv[3];

$dates = array("post_modified", "post_modified_gmt");

$mergeCommand = 'git merge-file -L mine -L base -L theirs ' . $A . ' ' . $O . ' ' . $B;

$oFile = file_get_contents($O);
$aFile = file_get_contents($A);
$bFile = file_get_contents($B);


foreach ($dates as $date) {
    $dateMatchPattern = "/" . $date . " = \"([^'\"]*)\"/";
    $dateReplacePattern = "/(" . $date . " = \")([0-9 :-]*)(\")/";
    $matches = array();
    preg_match($dateMatchPattern, $aFile, $matches);
    if (count($matches) == 0) {
        break;
    }
    $aDateString = $matches[1];

    preg_match($dateMatchPattern, $bFile, $matches);
    $bDateString = $matches[1];

    $aDate = new DateTime($aDateString);
    $bDate = new DateTime($bDateString);
    if ($aDate->getTimestamp() > $bDate->getTimestamp()) {
        $bFile = preg_replace($dateReplacePattern, '${1}' . $aDateString . '${3}', $bFile);
    } else {

        $aFile = preg_replace($dateReplacePattern, '${1}' . $bDateString . '${3}', $aFile);
    }

}

file_put_contents($B, preg_replace('/(\r\n|\r|\n)/', "$1#######\n", $bFile));
file_put_contents($A, preg_replace('/(\r\n|\r|\n)/', "$1#######\n", $aFile));
file_put_contents($O, preg_replace('/(\r\n|\r|\n)/', "$1#######\n", $oFile));

exec($mergeCommand, $dummy, $mergeExitCode);

file_put_contents($B, str_replace("#######\n", '', file_get_contents($B)));
file_put_contents($A, str_replace("#######\n", '', file_get_contents($A)));
file_put_contents($O, str_replace("#######\n", '', file_get_contents($O)));

exit ($mergeExitCode);
