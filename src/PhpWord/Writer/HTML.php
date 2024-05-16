<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 *
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Writer;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Validate;
use PhpOffice\PhpWord\Style\Font;

/**
 * HTML writer.
 *
 * Not supported: PreserveText, PageBreak, Object
 *
 * @since 0.10.0
 */
class HTML extends AbstractWriter implements WriterInterface
{
    /**
     * Is the current writer creating PDF?
     *
     * @var bool
     */
    protected $isPdf = false;

    /**
     * Footnotes and endnotes collection.
     *
     * @var array
     */
    protected $notes = [];

    /**
     * Callback for editing generated html.
     *
     * @var null|callable
     */
    private $editCallback;

    /**
     * Default generic name for default font for html.
     *
     * @var string
     */
    private $defaultGenericFont = '';

    /**
     * Default white space style for html.
     *
     * @var string
     */
    private $defaultWhiteSpace = '';

    /**
     * Create new instance.
     */
    public function __construct(?PhpWord $phpWord = null)
    {
        $this->setPhpWord($phpWord);

        $this->parts = ['Head', 'Body'];
        foreach ($this->parts as $partName) {
            $partClass = 'PhpOffice\\PhpWord\\Writer\\HTML\\Part\\' . $partName;
            if (class_exists($partClass)) {
                /** @var \PhpOffice\PhpWord\Writer\HTML\Part\AbstractPart $part Type hint */
                $part = new $partClass();
                $part->setParentWriter($this);
                $this->writerParts[strtolower($partName)] = $part;
            }
        }
    }

    /**
     * Save PhpWord to file.
     */
    public function save(string $filename): void
    {
        $this->writeFile($this->openFile($filename), $this->getContent());
    }

    public function getContent()
    {
		$content = '';
        $content .= '<!DOCTYPE html>' . PHP_EOL;
        $content .= '<!-- Generated by PHPWord -->' . PHP_EOL;
        $content .= '<html>' . PHP_EOL;
        $content .= $this->getWriterPart('Head')->write();
        $content .= $this->getWriterPart('Body')->write();
        $lines = explode(PHP_EOL, $content);
        $content .= '</html>' . PHP_EOL;
        $newcontent = '';
		$prev_was_li = false;
		$list_type = '';
        foreach ($lines as $line)
        {
			//$current-level = 0;
            if (preg_match('/( |^)<li data-depth/', $line))
            {
            /** use the data-depth, data-liststyle and data-numid to add <ul> </ul> <ol></ol>
               * where needed
               */

			   $tags = $this->parseTag($line, 'li');
			   //dd($tags['data-numid'] == '8');
			   if (!$prev_was_li) {
					if($tags['data-numid'] == '8') {
						//$line = '<ul>' . $line . '</ul>';
						$newcontent .= '<ul>' . PHP_EOL . $line  . PHP_EOL;
						$list_type = 'ul';
					} else {
						$newcontent .= '<ol>' . PHP_EOL . $line  . PHP_EOL;
						$list_type = 'ol';
					}
				} else {
					$newcontent .= $line . PHP_EOL;
				}
				$prev_was_li = true;
			   //$newcontent .= $line;
           }
           else {
				if($prev_was_li) {
					$newcontent .= '</'.$list_type.'>' . PHP_EOL . $line . PHP_EOL;
				} else {
					$newcontent .= $line . PHP_EOL;
				}
				$prev_was_li = false;
           }
        }
        $content = $newcontent;

        return $content;
    }

    function parseTag($content,$tg)
	{
        $dom = new \DOMDocument;
        $dom->loadHTML($content);
        $attr = array();
        foreach ($dom->getElementsByTagName($tg) as $tag) {
            foreach ($tag->attributes as $attribName => $attribNodeVal)
            {
            $attr[$attribName]=$tag->getAttribute($attribName);
            }
        }
        return $attr;
	}

    /**
     * Return the callback to edit the entire HTML.
     */
    public function getEditCallback(): ?callable
    {
        return $this->editCallback;
    }

    /**
     * Set a callback to edit the entire HTML.
     *
     * The callback must accept the HTML as string as first parameter,
     * and it must return the edited HTML as string.
     */
    public function setEditCallback(?callable $callback): self
    {
        $this->editCallback = $callback;

        return $this;
    }

    /**
     * Get is PDF.
     *
     * @return bool
     */
    public function isPdf()
    {
        return $this->isPdf;
    }

    /**
     * Get notes.
     *
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Add note.
     *
     * @param int $noteId
     * @param string $noteMark
     */
    public function addNote($noteId, $noteMark): void
    {
        $this->notes[$noteId] = $noteMark;
    }

    /**
     * Get generic name for default font for html.
     */
    public function getDefaultGenericFont(): string
    {
        return $this->defaultGenericFont;
    }

    /**
     * Set generic name for default font for html.
     */
    public function setDefaultGenericFont(string $value): self
    {
        $this->defaultGenericFont = Validate::validateCSSGenericFont($value);

        return $this;
    }

    /**
     * Get default white space style for html.
     */
    public function getDefaultWhiteSpace(): string
    {
        return $this->defaultWhiteSpace;
    }

    /**
     * Set default white space style for html.
     */
    public function setDefaultWhiteSpace(string $value): self
    {
        $this->defaultWhiteSpace = Validate::validateCSSWhiteSpace($value);

        return $this;
    }

    /**
     * Escape string or not depending on setting.
     */
    public function escapeHTML(string $txt): string
    {
        if (Settings::isOutputEscapingEnabled()) {
            return htmlspecialchars($txt, ENT_QUOTES | (defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0), 'UTF-8');
        }

        return $txt;
    }
}
