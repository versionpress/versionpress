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

func resolveModificationDates(mine string, theirs string) (string, string) {
	dateFields := []string{"post_modified", "post_modified_gmt"}

	for _, dateFields := range dateFields {
		mine, theirs = resolveDateField(dateFields, mine, theirs)
	}

	return mine, theirs
}

func resolveDateField(dateField string, mine string, theirs string) (string, string) {
	// https://regex101.com/r/5doqo7/1
	dateMatchPattern := regexp.MustCompile(fmt.Sprintf(`%s = "([^'"]*)"`, dateField))
	// https://regex101.com/r/1G3hBV/1
	dateReplacePattern := regexp.MustCompile(fmt.Sprintf(`(%s = ")([0-9 :-]*)(")`, dateField))

	mineMatches := dateMatchPattern.FindStringSubmatch(mine)
	theirsMatches := dateMatchPattern.FindStringSubmatch(theirs)

	if len(mineMatches) == 0 || len(theirsMatches) == 0 {
		return mine, theirs
	}

	mineDateString := mineMatches[1]
	theirsDateString := theirsMatches[1]

	mineDate, err1 := time.Parse("2006-01-02 15:04:05", mineDateString)
	theirsDate, err2 := time.Parse("2006-01-02 15:04:05", theirsDateString)

	if err1 != nil || err2 != nil {
		log.Printf("Unabled to parse date, mine = %s, theirs = %s", mineDateString, theirsDateString)
		return mine, theirs
	}

	if mineDate.After(theirsDate) {
		theirs = dateReplacePattern.ReplaceAllString(theirs, "${1}"+mineDateString+"${3}")
	}

	if theirsDate.After(mineDate) {
		mine = dateReplacePattern.ReplaceAllString(mine, "${1}"+theirsDateString+"${3}")
	}

	return mine, theirs
}

// Add temporary placeholder between adjacent lines to prevent merge conflicts
func interleaveLinesWithPlaceholder(str string, placeholder string) string {
	// https://regex101.com/r/9fX2KD/1
	linePattern := regexp.MustCompile(`(?:(\r\n|\r|\n)([^$]))`)
	return linePattern.ReplaceAllString(str, "${1}"+placeholder+"\n${2}")
}

// Remove temporary placeholders, see interleaveLinesWithPlaceholder
func removePlaceholders(str string, placeholder string) string {
	// https://regex101.com/r/68Hl2i/1
	placeholderPattern := regexp.MustCompile(fmt.Sprintf("(?m)^%s\n", placeholder))
	return placeholderPattern.ReplaceAllString(str, "")
}

func readFile(filename string) string {
	file, err := ioutil.ReadFile(filename)
	checkIfError(err)
	return string(file)
}

func writeFile(filename string, contents string) {
	file, err := os.Create(filename)
	defer file.Close()
	checkIfError(err)

	file.WriteString(contents)
}

func main() {
	// File names passed via cli arguments
	checkArgs("<current-name> <base-name> <other-name>")
	O := os.Args[1]
	A := os.Args[2]
	B := os.Args[3]

	original := readFile(O)
	mine := readFile(A)
	theirs := readFile(B)

	mine, theirs = resolveModificationDates(mine, theirs)

	placeholder := "###VP###"
	original = interleaveLinesWithPlaceholder(original, placeholder)
	mine = interleaveLinesWithPlaceholder(mine, placeholder)
	theirs = interleaveLinesWithPlaceholder(theirs, placeholder)

	writeFile(O, original)
	writeFile(A, mine)
	writeFile(B, theirs)

	// Call git merge command and receive the exitcode
	cmd := exec.Command("git", "merge-file", "-L", "mine", "-L", "base", "-L", "theirs", A, O, B)
	mergeError := cmd.Run()

	writeFile(O, removePlaceholders(readFile(O), placeholder))
	writeFile(A, removePlaceholders(readFile(A), placeholder))
	writeFile(B, removePlaceholders(readFile(B), placeholder))

	if mergeError != nil {
		log.Printf("Command finished with error: %v", mergeError)
		os.Exit(1)
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
