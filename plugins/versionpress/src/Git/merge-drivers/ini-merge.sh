#!/usr/bin/env sh

O=$1
A=$2
B=$3


# Date fields to merge
declare -a datesArray=("post_modified" "post_modified_gmt")

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

# Place placeholder between lines
awk '{printf("%s\n#######\n",$0)}' $A > $A.tmp && mv $A.tmp $A
awk '{printf("%s\n#######\n",$0)}' $B > $B.tmp && mv $B.tmp $B
awk '{printf("%s\n#######\n",$0)}' $O > $O.tmp && mv $O.tmp $O

# Process everything else through standard
git merge-file -L mine -L base -L theirs $A $O $B

# Save Git merge status
GIT_MERGE_STATUS=$?

# Remove placeholders
sed -e ':a' -e 'N' -e '$!ba' -e 's/\n#######//g' -i '' $A
sed -e ':a' -e 'N' -e '$!ba' -e 's/\n#######//g' -i '' $B
sed -e ':a' -e 'N' -e '$!ba' -e 's/\n#######//g' -i '' $O

# If Git merge fails, we should also 'fail'
if [ $GIT_MERGE_STATUS -ne 0 ]; then
	exit 1
fi
