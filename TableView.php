<?php
namespace JMP;

/*
 Copyright (c) 2015 Jean-Marc Pelletier



Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:



The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.



THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

class TableView {
    // The orientation of the table
    const HORIZONTAL = 0;
    const VERTICAL = 1;

    // Where to place labels
    const NONE = 0;
    const FRONT = 1;
    const BACK = 2;
    const BOTH = 3;

    /**
     * Where to show data labels. 
     * 
     * If TableView::FRONT, the labels will be shown inside the thead element
     * for TableView::HORIZONTAL tables, or as the first td of each row for
     * TableView::VERTICAL tables.
     *
     * If TableView::BACK, the labels will be shown inside the tfoot element
     * for TableView::HORIZONTAL tables, or as the last td of each row for
     * TableView::VERTICAL tables.
     *
     * If TableView::BOTH, TableView::FRONT and TableView::BACK will both be 
     * used.
     *
     * For labels displayed with TableView::FRONT, the class 'front',
     * and for labels displayed with TableView::BACK, the class 'back' 
     * will be added to the th element.
     * 
     * @var int
     */
    public $showHeaders = self::NONE;
    
    /**
     * Whether to display the array elements as table rows (TableView::VERTICAL) 
     * or columns (TableView::HORIZONTAL)
     * @var int
     */
    public $orientation = self::VERTICAL;
    
    /**
     * If this is an array of keys, the data will be shown in the order of these keys.
     * This must either be null, or an array with the same number of elements as $data.
     * @var array
     */
    public $sortOrder = null;

    /**
     * If this is an array of keys, cells belonging to arrays indexed by these keys
     * will be displayed in th rather than td elements.
     * @var array
     */
    public $extraHeaders = array();

    /**
     * If this is true, td elements will be created with classes that correspond
     * to the associative arrays' keys. If the arrays are not associative, indices
     * will be used instead. The class names are formed by prepending the 
     * $classPrefix parameter.
     * @var boolean
     */
    public $addHeaderClasses = false;

    /**
     * The prefix that will be appended to the class names of td elements.
     * @var string
     */
    public $classPrefix = 'data';

    /**
     * The id of the table element. If empty, no id is added
     * @var string
     */
    public $tableId = '';

    /**
     * The class of the table element. This will be added in addition to the
     * 'horizontal' or 'vertical' classes, denoting the orientation of the table.
     * If empty, only 'horizontal' or 'vertical' will be used.
     * @var string
     */
    public $tableClass = '';

    // The array that will be formatted as a table
    protected $data = null;

    private $rows = 0;
    private $columns = 0;
    private $labels = array();

    /**
     * Constructs a view of an array as an HTML table element.
     * If the array consists of non-array values, the table will have a single row
     * of column depending on the value of the orientation parameter.
     * 
     * If each of the elements are themselves arrays, the data is interpreted as
     * a matrix, with each array's contents displayed as a row or column, again
     * depending on the orientation. When passing an array of arrays, make sure 
     * that each array has the same size, otherwise an exception is thrown.
     *
     * @param array $data The data to represent as an HTML table.
     */
    function __construct(array $data) {
        $this->data = $data;

        // Make sure data is valid, if $data is null, assume an empty array
        if ($this->data === null || sizeof($this->data) === 0) {
            $this->rows = 0;
            $this->columns = 0;
        }
        else {
            // Verify that all inner arrays are the same size, and collect
            // necessary statistics for writing table.
            foreach ($this->data as $label => $cols) {
                $s = is_array($cols) ? sizeof($cols) : 1;
                $this->labels[] = $label;
                if ($this->orientation === self::VERTICAL) {
                    if ($this->columns > 0 && $s !== $this->columns) {
                        throw new \Exception("Size mismatch: row $this->rows has $s elements, $this->columns expected");
                    }
                    $this->columns = $s;
                    $this->rows += 1;
                }
                else {
                    if ($this->rows > 0 && $s !== $this->rows) {
                        throw new \Exception("Size mismatch: column $this->columns has $s elements, $this->rows expected");
                    }
                    $this->rows = $s;
                    $this->columns += 1;
                }
            }
        }
    }

    private $tableString = null;

    /**
     * Whenever an instance of the TableView class is cast to a string, HTML code
     * for a table element is returned.
     * 
     * All that is needed for outputting a simple table is code like:
     * echo $tableViewInstance; 
     * 
     * @return string A string containing the table HTML code.
     */
    public function __toString() {
        static $showHeaders = self::NONE;
        static $orientation = self::VERTICAL;
        static $sortOrder = null;
        static $extraHeaders = null;

        // Only regenerate the table if parameters have changed
        if ($showHeaders != $this->showHeaders ||
            $orientation != $this->orientation ||
            $sortOrder != $this->sortOrder || 
            $extraHeaders != $this->extraHeaders ||
            !$this->tableString) {
            
            $showHeaders = $this->showHeaders;
            $orientation = $this->orientation;
            $sortOrder =& $this->sortOrder;
            $extraHeaders =& $this->extraHeaders;

            if (!is_array($this->extraHeaders)) {
                $this->extraHeaders = array();
            }

            $this->tableString = '';

            // Validate sort order
            if ($this->sortOrder) {
                if (!is_array($this->sortOrder)) {
                    // Can't throw exceptions inside of __toString(). Log error and output empty string instead.
                    error_log('Invalid sort order for table: not an array');
                    return '';
                }
                else if (sizeof($this->sortOrder) != sizeof($this->data)) { 
                    error_log('Invalid sort order for table: size is not the same as source data');
                    return '';
                }
                foreach ($this->sortOrder as $orderedKey) {
                    if (!array_key_exists($orderedKey, $this->data)) {
                        error_log('Invalid sort order for table: key ' . $orderedKey . ' does not exist in source data');
                        return '';
                    }
                }
            }

            $order = $this->sortOrder ? $this->sortOrder : $this->labels;

            // Print the table
            $this->tableString .= '<table ';
            if ($this->tableId) {
                $this->tableString .= 'id="' . $this->tableId . '" ';
            }
            $this->tableString .= 'class="' . ($this->orientation === self::VERTICAL ? 'vertical' : 'horizontal');
            if ($this->tableClass) {
                $this->tableString .= ' ' . $this->tableClass;
            }
            $this->tableString .= '">';

            if ($this->orientation === self::HORIZONTAL) {
                // Print the header
                if ($this->showHeaders & self::FRONT) {
                    $this->tableString .= '<thead> <tr>';
                    foreach ($order as $label) {
                        $this->tableString .= '<th class="front' . 
                            ($this->addHeaderClasses ? ' ' . $this->classPrefix . $label . '"' : '') . 
                            '">' . $label . '</th>';
                    }
                    $this->tableString .= '</tr> </thead>';
                }

                // Print the footer
                if ($this->showHeaders & self::BACK) {
                    $this->tableString .= '<tfoot> <tr>';
                    foreach ($order as $label) {
                        $this->tableString .= '<th class="back' . 
                            ($this->addHeaderClasses ? ' ' . $this->classPrefix . $label . '"' : '') . 
                            '">' . $label . '</th>';
                    }
                    $this->tableString .= '</tr> </tfoot>';
                }

                // Print the data
                $this->tableString .= '<tbody>';
                for($i = 0; $i < $this->rows; $i++) {
                    $this->tableString .= '<tr>';
                    for($j = 0; $j < $this->columns; $j++) {
                        $key = $order[$j];
                        if (in_array($key, $this->extraHeaders)) {
                            $this->tableString .= '<th';
                        }
                        else {
                            $this->tableString .= '<td';
                        }
                        $this->tableString .= ($this->addHeaderClasses ? ' class="' . $this->classPrefix . $key . '"' : '') . '>' . 
                            $this->data[$key][$i] . '</td>';
                    }
                    $this->tableString .= '</tr>';
                }
                $this->tableString .= '</tbody>';
            }
            else {

                $this->tableString .= '<tbody>';
                for($i = 0; $i < $this->rows; $i++) {
                    $this->tableString .= '<tr>';
                    if ($this->showHeaders & self::FRONT) {
                        $this->tableString .= '<th class="front' . 
                            ($this->addHeaderClasses ? ' ' . $this->classPrefix . $order[$i] . '"' : '') .
                            '">' . $order[$i] . '</th>';
                    }
                    $key = $order[$i];
                    $rowData = $this->data[$key];
                    $el = in_array($key, $this->extraHeaders) ? '<th' : '<td';
                    foreach($rowData as $d) {
                        $this->tableString .= $el . 
                            ($this->addHeaderClasses ? ' class="' . $this->classPrefix . $key . '"' : '') . '>' . 
                            $d . '</td>';
                    }
                    if ($this->showHeaders & self::BACK) {
                        $this->tableString .= '<th class="back' . 
                            ($this->addHeaderClasses ? ' ' . $this->classPrefix . $order[$i] . '"' : '') .
                            '">' . $order[$i] . '</th>';
                    }
                    $this->tableString .= '</tr>';
                }
                $this->tableString .= '</tbody>';
            }

            $this->tableString .= '</table>';
        }

        return $this->tableString;
    }
}
?>