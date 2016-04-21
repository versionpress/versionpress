#!/usr/bin/env sh

O=$1
A=$2
B=$3


# Date fields to merge
declare -a datesArray=("post_modified" "post_modified_gmt")


# Iterate through array of date fields
for i in "${datesArray[@]}"
do
    # Find Values
    aDateString=$(sed -ne "s/$i = \"\([^'\"]*\)\"/\1/p" $A)
    aDateString=${aDateString//[$'\r\n']/}

    # If the file does not contain value from array, we can skip it
    if [[ -z "$aDateString" ]]; then
        break
    fi

    bDateString=$(sed -ne "s/$i = \"\([^'\"]*\)\"/\1/p" $B)
    bDateString=${bDateString//[$'\r\n']/}

    # Transform them to Numbers
    aDateNumber=${aDateString//[-: ]/}
    bDateNumber=${bDateString//[-: ]/}

    # Compare and make both values same
    if [ "$aDateNumber" -lt "$bDateNumber" ]; then
        sed -i '' "s/$i = \"\([^\r\n\"]*\)\"\([\r\n]*\)/$i = \"$bDateString\"\2/g" $A
    else
          sed -i '' "s/$i = \"\([^\r\n\"]*\)\"\([\r\n]*\)/$i = \"$aDateString\"\2/g" $B
    fi


done

# Place temporary placeholder between lines to avoid merge conflicts on adjacent lines
sed -i '' -e ':a' -e 'N' -e '$!ba' -e 's/\n/&###VP###&/g' $O
sed -i '' -e ':a' -e 'N' -e '$!ba' -e 's/\n/&###VP###&/g' $A
sed -i '' -e ':a' -e 'N' -e '$!ba' -e 's/\n/&###VP###&/g' $B

# Process everything else through standard
git merge-file -L mine -L base -L theirs $A $O $B

# Save Git merge status exit code
GIT_MERGE_EXIT_CODE=$?

# Remove temporary placeholders
sed -e ':a' -e 'N' -e '$!ba' -e 's/\n###VP###//g' -i '' $A
sed -e ':a' -e 'N' -e '$!ba' -e 's/\n###VP###//g' -i '' $B
sed -e ':a' -e 'N' -e '$!ba' -e 's/\n###VP###//g' -i '' $O

# If Git merge fails, we should also 'fail'
if [ $GIT_MERGE_EXIT_CODE -ne 0 ]; then
    exit 1
fi
