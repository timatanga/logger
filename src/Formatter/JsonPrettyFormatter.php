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

use Monolog\Formatter\JsonFormatter;

/**
 * A variation of the Monolog JsonFormatter which pretty-prints the JSON output.
 */
class JsonPrettyFormatter extends JsonFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(array $record): string
    {
        dump($record);
        return json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).($this->appendNewline ? "\n" : '');
    }
}