package main

import (
	"strings"
	"testing"
	"unicode"
)

func TestResolveModificationDates(t *testing.T) {
	t.Run("don't modify ini without dates", func(t *testing.T) {
		mine := stripIndents(`
            [GUID]
            title = "Default title"`)
		theirs := stripIndents(`
            [GUID]
            title = "Another title"`)

		newMine, newTheirs := resolveModificationDates(mine, theirs)

		if newMine != mine {
			t.Errorf("Mine file was modified, %s", newMine)
		}

		if newTheirs != theirs {
			t.Errorf("Theirs file was modified, %s", newTheirs)
		}
	})

	t.Run("don't modify ini with identical post_modified", func(t *testing.T) {
		mine := stripIndents(`
            [GUID]
            title = "Default title"
            post_modified = "2011-11-11 11:11:11"`)
		theirs := stripIndents(`
            [GUID]
            title = "Default title"
            post_modified = "2011-11-11 11:11:11"`)

		newMine, newTheirs := resolveModificationDates(mine, theirs)

		if newMine != mine {
			t.Errorf("Mine file was modified, %s", newMine)
		}

		if newTheirs != theirs {
			t.Errorf("Theirs file was modified, %s", newTheirs)
		}
	})

	t.Run("use post_modified from theirs if it's newer", func(t *testing.T) {
		mine := stripIndents(`
            [GUID]
            post_modified = "2011-11-11 11:11:11"`)
		theirs := stripIndents(`
            [GUID]
            post_modified = "2012-12-12 12:12:12"`)

		newMine, newTheirs := resolveModificationDates(mine, theirs)

		if newMine != theirs {
			t.Errorf("Mine post_modified wasn't updated, %s", newMine)
		}

		if newTheirs != theirs {
			t.Errorf("Theirs file was modified, %s", newTheirs)
		}
	})

	t.Run("use post_modified from mine if it's newer", func(t *testing.T) {
		mine := stripIndents(`
            [GUID]
            post_modified = "2012-12-12 12:12:12"`)
		theirs := stripIndents(`
            [GUID]
            post_modified = "2010-10-10 10:10:10"`)

		newMine, newTheirs := resolveModificationDates(mine, theirs)

		if newMine != mine {
			t.Errorf("Mine file was modified, %s", newMine)
		}

		if newTheirs != mine {
			t.Errorf("Theirs post_modified wasn't updated, %s", newTheirs)
		}
	})

	t.Run("use post_modified_gmt from theirs if it's newer", func(t *testing.T) {
		mine := stripIndents(`
            [GUID]
            post_modified_gmt = "2011-11-11 11:11:11"`)
		theirs := stripIndents(`
            [GUID]
            post_modified_gmt = "2012-12-12 12:12:12"`)

		newMine, newTheirs := resolveModificationDates(mine, theirs)

		if newMine != theirs {
			t.Errorf("Mine post_modified wasn't updated, %s", newMine)
		}

		if newTheirs != theirs {
			t.Errorf("Theirs file was modified, %s", newTheirs)
		}
	})

	t.Run("use post_modified_gmt from mine if it's newer", func(t *testing.T) {
		mine := stripIndents(`
            [GUID]
            post_modified_gmt = "2012-12-12 12:12:12"`)
		theirs := stripIndents(`
            [GUID]
            post_modified_gmt = "2010-10-10 10:10:10"`)

		newMine, newTheirs := resolveModificationDates(mine, theirs)

		if newMine != mine {
			t.Errorf("Mine file was modified, %s", newMine)
		}

		if newTheirs != mine {
			t.Errorf("Theirs post_modified wasn't updated, %s", newTheirs)
		}
	})
}

func TestInterleaveLinesWithPlaceholders(t *testing.T) {
	t.Run("do not change a single line string", func(t *testing.T) {
		input := "single line"
		output := interleaveLinesWithPlaceholder(input, "###VP###")

		if input != output {
			t.Errorf("Output was modified, %s", output)
		}
	})

	t.Run("do not change a single line string with newline", func(t *testing.T) {
		input := "single line\n"
		output := interleaveLinesWithPlaceholder(input, "###VP###")

		if input != output {
			t.Errorf("Output was modified, %s", output)
		}
	})

	t.Run("add placeholder between every two lines", func(t *testing.T) {
		input := stripIndents(`
	        line one
            line two
            line three
            line four`)

		expectedOutput := stripIndents(`
	        line one
	        ###VP###
            line two
            ###VP###
            line three
            ###VP###
            line four`)

		output := interleaveLinesWithPlaceholder(input, "###VP###")

		if output != expectedOutput {
			t.Errorf("Incorrect ouput, %s", output)
		}
	})

	t.Run("support CRLF newlines", func(t *testing.T) {
		input := "line one\r\nline two"
		expectedOutput := "line one\r\n###VP###\nline two"

		output := interleaveLinesWithPlaceholder(input, "###VP###")

		if output != expectedOutput {
			t.Errorf("Incorrect ouput, %s", output)
		}
	})
}

func TestRemovePlaceholders(t *testing.T) {
	t.Run("do not change a single line string", func(t *testing.T) {
		input := "single line"
		output := removePlaceholders(input, "###VP###")

		if input != output {
			t.Errorf("Output was modified, %s", output)
		}
	})

	t.Run("do not change a single line string with newline", func(t *testing.T) {
		input := "single line\n"
		output := removePlaceholders(input, "###VP###")

		if input != output {
			t.Errorf("Output was modified, %s", output)
		}
	})

	t.Run("remove placeholder from between every two lines", func(t *testing.T) {
		input := stripIndents(`
	        line one
	        ###VP###
            line two
            ###VP###
            line three
            ###VP###
            line four`)

		expectedOutput := stripIndents(`
	        line one
            line two
            line three
            line four`)

		output := removePlaceholders(input, "###VP###")

		if output != expectedOutput {
			t.Errorf("Incorrect ouput, %s", output)
		}
	})

	t.Run("support CRLF newlines", func(t *testing.T) {
		input := "line one\r\n###VP###\nline two"
		expectedOutput := "line one\r\nline two"

		output := removePlaceholders(input, "###VP###")

		if output != expectedOutput {
			t.Errorf("Incorrect ouput, %s", output)
		}
	})
}

func stripIndents(str string) string {
	var sb strings.Builder
	lines := strings.Split(str, "\n")[1:]
	for _, line := range lines {
		sb.WriteString(strings.TrimLeftFunc(line, unicode.IsSpace))
		sb.WriteString("\n")
	}

	return sb.String()
}
