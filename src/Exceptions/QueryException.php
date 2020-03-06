<?php

namespace Elixir\Exceptions;

class QueryException extends \Exception
{
    public static function unknownAlias($alias, $registeredAliases) {
        return new self("The given alias '" . $alias . "' is not part of " .
            'any FROM or JOIN clause table. The currently registered ' .
            'aliases are: ' . implode(', ', $registeredAliases) . '.');
    }

    public static function nonUniqueAlias($alias, $registeredAliases) {
        return new self("The given alias '" . $alias . "' is not unique " .
            'in FROM and JOIN clause table. The currently registered ' .
            'aliases are: ' . implode(', ', $registeredAliases) . '.');
    }
}
