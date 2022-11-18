<?php

namespace Zebooka\Gedcom\Document;

trait VersionTrait
{
    use DocumentTrait;

    /**
     * Get document version from HEAD.GEDC.VERS tag
     * @return string|null
     */
    public function version()
    {
        return $this->xpath('string(/G:GEDCOM/G:HEAD/G:GEDC/G:VERS/@value)');
    }

    /**
     * Check if document has 5.5 version strictly.
     * @return bool
     */
    public function isVersion55()
    {
        return $this->version() === '5.5';
    }

    /**
     * Check if document has 5.5.1 version strictly.
     * @return bool
     */
    public function isVersion551()
    {
        return $this->version() === '5.5.1';
    }

    /**
     * Check if document has any 5.5.x version (5.5 and 5.5.1).
     * @return bool
     */
    public function isVersion55x()
    {
        return 0 <= version_compare($this->version(), '5.5') && version_compare($this->version(), '5.6') < 0;
    }

    /**
     * Check if document has any 7.x.x+ version.
     * @return bool
     */
    public function isVersion7x()
    {
        return 0 <= version_compare($this->version(), '7.0.0');
    }
}
