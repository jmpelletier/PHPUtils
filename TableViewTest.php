<?php

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


/**
 * Import the TableView
 */
require('TableView.php');

/**
 * The TableView class is defined in the JMP namespace
 */
use JMP\TableView;

/**
 * Generate some dummy data
 */
$testData = array();

$rows = 12;
$columns = 6;

for($i = 0; $i < $columns; $i++) {
    $testData[] = array();
    for($j = 0; $j < $columns; $j++) {
        $testData[$i][] = rand(0,100);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>TableView Tests</title>

    <style>    

        /**
         * Use the tableClass property to add a 
         * custom class to the table element.
         */
        table.arrayView {
            border-collapse: collapse;
        }

        /**
         * Use the tableId property to add a 
         * custom id to the table element.
         */
        table#myTable td{
            border: 1px solid gray;
            padding: 3px;
        }

        /**
         * Use the addHeaderClasses, and
         * classPrefix properties to 
         * generate selectors for each row or column
         * of data. Here we select the second (index==1)
         * array using the default prefix of 'data'.
         */
        td.data1 {
            background-color: yellow;
        }

        /**
         * Use the front or back classes on the th element
         * to select headers at the front or back of the table.
         */
        th.back {
            background-color: pink;
        }

        th.front {
            background-color: LightBlue;
        }

        /**
         * Use the horizontal or vertical classes on the table element
         * to select tables in either orientation.
         */
        table.horizontal th.back {
            color: red;
        }

        table.horizontal th.front {
            color: purple;
        }

    </style>
</head>
<body>
<?php

    /**
     * Generate a new TableView from our test data.
     */
    $tableView = new TableView($testData);

    /**
     * Add classes on each td and th element.
     */
    $tableView->addHeaderClasses = true;

    /**
     * Add custom selectors to the table element
     */
    $tableView->tableClass = 'arrayView';
    $tableView->tableId = 'myTable';
    
    /**
     * To output HTML, simply echo the TableView 
     * instance. HTML is generated when the object
     * is cast to string. You can also save the HTML
     * like this:
     * $html = (string)$tableView;
     */
    echo $tableView;

    echo '<hr>';

    /**
     * The data can either be presented horizontally 
     * or vertically. The default is TableView::VERTICAL,
     * where each table element becomes a row.
     * Here, we output the data horizontally; each element
     * is presented as a column.
     */
    $tableView->orientation = TableView::HORIZONTAL;
    echo $tableView;

    echo '<hr>';

    /**
     * By default the showHeaders parameter is set to 
     * TableView::NONE, and no headers are shown.
     * Here we display the array keys before the data,
     * inside thead, because the table is still horizontal.
     */
    $tableView->showHeaders = TableView::FRONT;
    echo $tableView;

    echo '<hr>';

    /**
     * Here, instead, we show the keys after the data,
     * inside the tfoot element.
     */
    $tableView->showHeaders = TableView::BACK;
    echo $tableView;

    echo '<hr>';

    /**
     * We can also show both thead and tfoot.
     */
    $tableView->showHeaders = TableView::BOTH;
    echo $tableView;

    echo '<hr>';

    /**
     * For a vertical table, the array keys are added as
     * extra th elements preceding the data on each row.
     */
    $tableView->orientation = TableView::VERTICAL;
    $tableView->showHeaders = TableView::FRONT;
    echo $tableView;

    echo '<hr>';

    /**
     * If the keys are shown in the back, then the th
     * element is added after the data.
     */
    $tableView->showHeaders = TableView::BACK;
    echo $tableView;

    echo '<hr>';

    /**
     * Front and back.
     */
    $tableView->showHeaders = TableView::BOTH;
    echo $tableView;

    echo '<hr>';

    $tableView->extraHeaders = array(0, 1);
    echo $tableView;

    echo '<hr>';

    $tableView->orientation = TableView::HORIZONTAL;
    $tableView->extraHeaders = array(0);
    echo $tableView;

    echo '<hr>';

    /**
     * By passing an array of keys to the sortOrder
     * parameter, you can change the order in which the 
     * rows or columns will be displayed, without having
     * to modify your source array.
     */
    $tableView->showHeaders = TableView::FRONT;
    $tableView->extraHeaders = null; // remove the extra headers
    $keys = array_keys($testData);
    shuffle($keys); // The columns will be in random order
    $tableView->sortOrder = $keys;
    echo $tableView;

    echo '<hr>';

    /**
     * Here is an example of a single row matrix with headers
     * generated from the associative array keys.
     */
    $rowView = new TableView(array('foo'=>1, 'bar'=>2, 'hoge'=>3));
    $rowView->orientation = TableView::HORIZONTAL;
    $rowView->showHeaders = TableView::FRONT;
    echo $rowView;

    echo '<hr>';

    $rowView->orientation = TableView::VERTICAL;
    echo $rowView;

    

?>
</body>
</html>