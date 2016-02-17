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

	if [[ -z "$aDateString" ]]; then

	

	bDateString=$(sed -ne "s/$i = \"\([^'\"]*\)\"/\1/p" $B)

	# Transform them to Numbers
	aDateNumber=${aDateString//[-: ]/}
	bDateNumber=${bDateString//[-: ]/}

	# Compare and make both values same
	if [ "$aDateNumber" -lt "$bDateNumber" ]; then
		sed -i '' "s/$i =.*/$i = \"$bDateString\"/" $A
	else
  		sed -i '' "s/$i =.*/$i = \"$aDateString\"/" $B
	fi

done

# Process everything else through standard
git merge-file $A $O $B


