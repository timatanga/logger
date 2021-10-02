<?php 
declare(strict_types=1);

/*
 * This file is part of the Logger package.
 *
 * (c) Mark Fluehmann dbiz.apps@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\Logger\Formatter;

use Monolog\Formatter\LineFormatter;

/**
 * A variation of the Monolog LineFormatter which pretty-prints the JSON message.
 */
class LinePrettyFormatter extends LineFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(array $record): string
    {
        $datetime = \DateTime::createFromImmutable($record['datetime']);
        $datetime = $datetime->format('Y-m-d H:i:s');

        $output = '[' . $datetime . '] ' . $record['channel'].'.'.$record['level_name'] .':'. PHP_EOL;
        $output .= $record['message'] . PHP_EOL;

        return $output;
    }
}