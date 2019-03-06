#!/usr/bin/env sh

O=$1
A=$2
B=$3

# Date fields to merge
export DATE_FIELDS="post_modified post_modified_gmt"


# Iterate through array of date fields
for FIELD in $DATE_FIELDS
do
    # Find Values
    A_DATE_STR=$(sed -ne "s/$FIELD = \"\([^'\"]*\)\"/\1/p" "$A")
    A_DATE_STR=$(printf "%s" "$A_DATE_STR" | tr -d '\r\n')

    # If the file does not contain value from array, we can skip it
    if [ -z "$A_DATE_STR" ]; then
        break
    fi

    B_DATE_STR=$(sed -ne "s/$FIELD = \"\([^'\"]*\)\"/\1/p" "$B")
    B_DATE_STR=$(printf "%s" "$B_DATE_STR" | tr -d '\r\n')

    # Transform them to Numbers
    A_DATE_NUM=$(printf "%s" "$A_DATE_STR" | tr -d ": -")
    B_DATE_NUM=$(printf "%s" "$B_DATE_STR" | tr -d ": -")

    # Compare and make both values same
    if [ "$A_DATE_NUM" -lt "$B_DATE_NUM" ]; then
        REPLACED_CONTENT=$(sed "s/$FIELD = \"\([^\r\n\"]*\)\"\([\r\n]*\)/$FIELD = \"$B_DATE_STR\"\2/g" "$A")
        printf "%s\n" "$REPLACED_CONTENT" > "$A"
    else
        REPLACED_CONTENT=$(sed "s/$FIELD = \"\([^\r\n\"]*\)\"\([\r\n]*\)/$FIELD = \"$A_DATE_STR\"\2/g" "$B")
        printf "%s\n" "$REPLACED_CONTENT" > "$B"
    fi


done

# Place temporary placeholder between lines to avoid merge conflicts on adjacent lines
REPLACED_CONTENT=$(sed -e ':a' -e 'N' -e '$!ba' -e 's/\n/&###VP###&/g' "$O")
printf "%s\n" "$REPLACED_CONTENT" > "$O"
REPLACED_CONTENT=$(sed -e ':a' -e 'N' -e '$!ba' -e 's/\n/&###VP###&/g' "$A")
printf "%s\n" "$REPLACED_CONTENT" > "$A"
REPLACED_CONTENT=$(sed -e ':a' -e 'N' -e '$!ba' -e 's/\n/&###VP###&/g' "$B")
printf "%s\n" "$REPLACED_CONTENT" > "$B"

# Process everything else through standard
git merge-file -L mine -L base -L theirs "$A" "$O" "$B"

# Save Git merge status exit code
GIT_MERGE_EXIT_CODE=$?

# Remove temporary placeholders
REPLACED_CONTENT=$(sed -e ':a' -e 'N' -e '$!ba' -e 's/\n###VP###//g' "$O")
printf "%s\n" "$REPLACED_CONTENT" > "$O"
REPLACED_CONTENT=$(sed -e ':a' -e 'N' -e '$!ba' -e 's/\n###VP###//g' "$A")
printf "%s\n" "$REPLACED_CONTENT" > "$A"
REPLACED_CONTENT=$(sed -e ':a' -e 'N' -e '$!ba' -e 's/\n###VP###//g' "$B")
printf "%s\n" "$REPLACED_CONTENT" > "$B"

# If Git merge fails, we should also 'fail'
if [ ${GIT_MERGE_EXIT_CODE} -ne 0 ]; then
    exit 1
fi
