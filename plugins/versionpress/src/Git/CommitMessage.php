<?php

namespace VersionPress\Git;
use NStrings;

/**
 * Represents Git commit message (originally a string) as a structure with subject,
 * body and VP tags parsed from the body.
 *
 * Note about the "[VP]" prefix: this class just doesn't care. When the commit is first created,
 * external code will typically put prefix-less subject into this object. Only when
 * {@link VersionPress\Git\GitRepository::commit()} executes the commit itself will get the [VP] prefix. On the other hand,
 * when the commit is read from the repository and parsed back into this object,
 * it will typically have the [VP] prefix. It doesn't matter as VersionPress typically only
 * works with the commit body and its VP tags.
 *
 * @see Commit  Represents the whole commit
 */
class CommitMessage {

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $tags;

    /**
     *
     * @param string $subject First line of the commit message
     * @param string $body Optional, lines 3..n
     */
    function __construct($subject, $body = null) {
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * The first line of the commit message. Short description of the change
     *
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Rest of the commit message (all text except the subject). Usually
     * just the VP tags but body might also contain some other information.
     *
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Returns all VP tags in an associative array. VP tags are identified
     * as separate lines in the commit body that start with the "VP-" prefix.
     *
     * @return array Array of tagName => value (trimmed)
     */
    public function getVersionPressTags() {
        if (!$this->tags) {
            $tagLines = array_filter(
                array_map("trim", explode("\n", $this->getBody())),
                function ($line) {
                    return NStrings::startsWith($line, "VP-");
                }
            );
            $tags = array();
            foreach ($tagLines as $line) {
                list($key, $value) = array_map("trim", explode(":", $line, 2));
                $tags[$key] = $value;
            }

            $this->tags = $tags;
        }
        return $this->tags;
    }

    /**
     * Returns VP tag with given name, or an empty string if no tag of given name is found.
     *
     * @param $tagName
     * @return string
     */
    public function getVersionPressTag($tagName) {
        $tags = $this->getVersionPressTags();
        return isset($tags[$tagName]) ? $tags[$tagName] : "";
    }
}