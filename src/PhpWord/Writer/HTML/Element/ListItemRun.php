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

namespace PhpOffice\PhpWord\Writer\HTML\Element;

/**
 * ListItem element HTML writer.
 *
 * @since 0.10.0
 */
class ListItemRun extends TextRun
{
    public function write()
    {
        if (!$this->element instanceof \PhpOffice\PhpWord\Element\ListItemRun) {
            return '';
        }
        $content = '';
        $content .= sprintf(
            '<li data-depth="%s" data-liststyle="%s" data-numId="%s">',
            $this->element->getDepth(),
            $this->getListFormat($this->element->getDepth()),
            $this->getListId()
        );

        $namespace = 'PhpOffice\\PhpWord\\Writer\\HTML\\Element';
        $container = $this->element;

        $elements = $container->getElements();
        foreach ($elements as $element) {
            $elementClass = get_class($element);
            $writerClass = str_replace('PhpOffice\\PhpWord\\Element', $namespace, $elementClass);
            if (class_exists($writerClass)) {
                /** @var \PhpOffice\PhpWord\Writer\HTML\Element\AbstractElement $writer Type hint */
                $writer = new $writerClass($this->parentWriter, $element, true);
                $content .= $writer->write();
            }
        }

        $content .= '</li>';
        $content .= "\n";
        return $content;
    }

    public function getListFormat($depth)
    {
        return $this->element->getStyle()->getNumStyle();
    }

    public function getListId()
    {
        return $this->element->getStyle()->getNumId();
    }
}
