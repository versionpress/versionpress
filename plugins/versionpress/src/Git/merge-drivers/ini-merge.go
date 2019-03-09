package main

import (
	"fmt"
	"io/ioutil"
	"log"
	"os"
	"os/exec"
	"regexp"
	"strings"
	"time"
)

func main() {
	// File names passed via cli arguments
	checkArgs("<current-name> <base-name> <other-name>")
	log.Printf("%v", os.Args)
	O := os.Args[1]
	A := os.Args[2]
	B := os.Args[3]
	// Dates fields to merge
	dates := []string{"post_modified", "post_modified_gmt"}

	mergeCommand := fmt.Sprintf("git merge-file -L mine -L base -L theirs %s %s %s", A, O, B)

	oFile, err := ioutil.ReadFile(O)
	checkIfError(err)
	oFileString := string(oFile)

	aFile, err := ioutil.ReadFile(A)
	checkIfError(err)

	aFileString := string(aFile)

	bFile, err := ioutil.ReadFile(B)
	checkIfError(err)

	bFileString := string(bFile)

	for _, date := range dates {
		//     // Find values
		dateMatchPattern := fmt.Sprintf("/%s\"([^'\"]*)\"/", date)
		dateReplacePattern := fmt.Sprintf("/(%s = \")([0-9 :-]*)(\")/", date)

		re := regexp.MustCompile(dateMatchPattern)
		re2 := regexp.MustCompile(dateReplacePattern)
		matches := re.FindStringSubmatch(aFileString)
		if len(matches) == 0 {
			break
		}
		aDateString := matches[1]

		matches = re.FindStringSubmatch(bFileString)
		bDateString := matches[1]

		aDate, err := time.Parse("2006-01-02 15:04:05", aDateString)
		checkIfError(err)

		bDate, err := time.Parse("2006-01-02 15:04:05", bDateString)
		checkIfError(err)

		difference := bDate.Sub(aDate)
		// Replace date value in both files to be more recent
		if difference > 0 {
			bFileString = re2.ReplaceAllString(bFileString, "${1} "+aDateString+"${3}")
		} else {
			aFileString = re2.ReplaceAllString(aFileString, "${1} "+bDateString+"${3}")
		}
	}

	// Add temporary placeholder between adjacent lines to prevent merge conflicts
	re3 := regexp.MustCompile("/(\r\n|\r|\n)/")
	bFileString = re3.ReplaceAllString(bFileString, "${1}###VP###\n")
	aFileString = re3.ReplaceAllString(aFileString, "${1}###VP###\n")
	oFileString = re3.ReplaceAllString(oFileString, "${1}###VP###\n")

	fa, err := os.Open(A)
	checkIfError(err)

	fa.WriteString(aFileString)

	fb, err := os.Open(B)
	checkIfError(err)

	fb.WriteString(bFileString)

	fo, err := os.Open(O)
	checkIfError(err)

	fo.WriteString(oFileString)

	path, err := exec.LookPath("ini-merge")
	if err != nil {
		log.Printf("didn't find 'ini-merge' executable\n")
		os.Exit(1)
	} else {
		log.Printf("'ini-merge' executable ini-merge in '%s'\n", path)
	}
	// Call git merge command and receive the exitcode
	cmd := exec.Command(mergeCommand)
	error := cmd.Run()
	if error != nil {
		log.Printf("Command finished with error: %v", error)
		os.Exit(1)
	} else {
		// Remove temporary placeholders
		re4 := regexp.MustCompile("###VP###\n")
		bFileString = re4.ReplaceAllString(bFileString, "")
		aFileString = re4.ReplaceAllString(aFileString, "")
		oFileString = re4.ReplaceAllString(oFileString, "")

		fa, err := os.Open(A)
		checkIfError(err)

		fa.WriteString(aFileString)

		fb, err := os.Open(B)
		checkIfError(err)

		fb.WriteString(bFileString)

		fo, err := os.Open(O)
		checkIfError(err)

		fo.WriteString(oFileString)
		os.Exit(0)
	}
}

func warning(format string, args ...interface{}) {
	log.Printf("\x1b[36;1m%s\x1b[0m\n", fmt.Sprintf(format, args...))
}

func checkArgs(arg ...string) {
	if len(os.Args) < len(arg)+1 {
		warning("Usage: %s %s", os.Args[0], strings.Join(arg, " "))
		os.Exit(1)
	}
}

func checkIfError(err error) {
	if err == nil {
		return
	}
	log.Printf("\x1b[31;1m%s\x1b[0m\n", fmt.Sprintf("error: %s", err))
	os.Exit(1)
}
