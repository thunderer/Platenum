<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

use Thunder\Platenum\Exception\PlatenumException;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
trait DocblockEnumTrait
{
    use EnumTrait;

    private static function resolve(): array
    {
        $class = static::class;
        $doc = (new \ReflectionClass($class))->getDocComment();
        if(false === $doc) {
            throw PlatenumException::fromMissingDocblock($class);
        }

        if(\preg_match_all('~^\s+\*\s+@method\s+static\s+(?:self|static)\s+(?<key>\w+)\(\)$~m', $doc, $matches) < 1) {
            throw PlatenumException::fromEmptyDocblock($class);
        }
        /** @var array<string,array<int,string>> $matches */
        $matches = $matches;
        if(\count($matches['key']) !== substr_count($doc, '@method')) {
            throw PlatenumException::fromMalformedDocblock($class);
        }

        $values = [];
        /** @var array<string,array<int,string>> $matches */
        foreach($matches['key'] as $key) {
            $values[$key] = $key;
        }

        return $values;
    }
}
