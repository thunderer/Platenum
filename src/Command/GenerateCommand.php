<?php
declare(strict_types=1);
namespace Thunder\Platenum\Command;

use Composer\Autoload\ClassLoader;
use Thunder\Platenum\Enum\ConstantsEnumTrait;
use Thunder\Platenum\Enum\DocblockEnumTrait;
use Thunder\Platenum\Exception\PlatenumException;
use Thunder\Platenum\Enum\StaticEnumTrait;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @codeCoverageIgnore
 * @internal
 */
final class GenerateCommand
{
    /** @var ClassLoader */
    private $classLoader;

    public function __construct(ClassLoader $loader)
    {
        $this->classLoader = $loader;
    }

    /**
     * @param int $argc
     * @param string[] $argv
     */
    public function execute(int $argc, array $argv): void
    {
        $this->writeln('Platenum (c) 2019 Tomasz Kowalczyk.');
        $this->writeln('');

        if($argc < 4) {
            $this->writeln('usage:    bin/generate <source> <class> MEMBER=value,MEMBER=value,...');
            $this->writeln('examples: bin/generate constants UserStatus ACTIVE=1,INACTIVE=2');
            $this->writeln('          bin/generate docblock PaymentType INTERNAL,EXTERNAL');
            $this->writeln('          bin/generate static "Project\\Namespace\\Currency" PLN=10,EUR=12,USD=14');
            exit(1);
        }
        if(false === in_array($argv[1], ['constants', 'docblock', 'static'], true)) {
            $this->writeln(sprintf('Unrecognized type `%s`. Allowed: `%s`.', $argv[1], 'constants,docblock,static'));
            exit(1);
        }

        $path = $this->computeClassPath($argv[2]);
        $code = $this->generateClassCode($argv[1], $argv[2], $argv[3]);

        if(file_exists($path)) {
            $this->writeln("\e[0;31mFile already exists.\e[0m");
            return;
        }
        $this->writeFile($path, $code);

        $this->writeln('');
        $this->writeln('--- BEGIN CLASS CODE ---');
        $this->writeln("\e[0;33m$code\e[0m");
        $this->writeln('--- END CLASS CODE ---');
    }

    private function writeFile(string $path, string $content): void
    {
        if(!$path) {
            return;
        }

        $directory = \dirname($path);
        if(!file_exists($directory) && !\mkdir($directory, 0777, true) && !\is_dir($directory)) {
            throw new PlatenumException(sprintf('Failed to create target directory `%s`.', $directory));
        }

        $bytes = file_put_contents($path, $content);
        if($bytes !== \strlen($content)) {
            throw new PlatenumException(sprintf('Failed to write target file at path `%s`.', $path));
        }
    }

    private function computeClassPath(string $fqcn): string
    {
        $prefix = '';
        $path = '';
        /**
         * @var string $ns
         * @var string[] $paths
         */
        foreach($this->classLoader->getPrefixesPsr4() as $ns => $paths) {
            if(0 === strpos($fqcn, $ns)) {
                $prefix = $ns;
                $path = (string)realpath($paths[0]);
                break;
            }
        }

        if(false === ($prefix && $path)) {
            $this->writeln(sprintf("Namespace `\e[0;32m%s\e[0m` is not mapped in Composer autoloader.", $fqcn));
            $this->writeln('Generated code will be written below. No files will be written to disk.');
            return '';
        }

        $fullPath = $path.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, str_replace($prefix, '', $fqcn)).'.php';
        $this->writeln(sprintf("Namespace prefix `\e[0;32m%s\e[0m` is mapped to `\e[0;32m%s\e[0m`.", $prefix, $path));
        $this->writeln(sprintf("Class file will be written to path: `\e[0;32m%s\e[0m`", $fullPath));

        return $fullPath;
    }

    private function generateClassCode(string $type, string $fqcn, string $keys): string
    {
        $namespace = $fqcn;
        $lastSlash = strrpos($namespace, '\\');
        $class = substr($namespace, $lastSlash ? $lastSlash + 1 : 0);
        $namespace = $lastSlash ? substr($namespace, 0, $lastSlash) : 'X';

        preg_match_all('~(?<key>[a-zA-Z]+)(=(?<value>[^,$]+),?)?~', $keys, $matches);
        if(PREG_NO_ERROR !== preg_last_error()) {
            throw new PlatenumException(sprintf('Failed to parse keys, `%s`.', preg_last_error()));
        }

        $index = 1;
        $docblockEntries = [];
        $constantsEntries = [];
        $staticEntries = [];
        /** @var array<string,array<int,string>> $matches */
        $count = \count($matches['key']);
        for($i = 0; $i < $count; $i++) {
            $key = $matches['key'][$i];
            $value = $matches['value'][$i] ?: $index++;
            if(false === ctype_digit((string)$value)) {
                $value = '\''.$value.'\'';
            }

            $constantsEntries[] = '    private const '.strtoupper($key).' = '.$value.';';
            $docblockEntries[] = ' * @method static static '.strtoupper($key).'()';
            $staticEntries[] = '        \''.strtoupper($key).'\' => '.$value.';';
        }

        $values = [
            'constants' => ['template' => static::CONSTANTS_TEMPLATE, 'trait' => ConstantsEnumTrait::class, 'members' => implode("\n", $constantsEntries)],
            'docblock'  => ['template' => static::DOCBLOCK_TEMPLATE,  'trait' => DocblockEnumTrait::class,  'members' => ''],
            'static'    => ['template' => static::STATIC_TEMPLATE,    'trait' => StaticEnumTrait::class,    'members' => implode("\n", $staticEntries)],
        ];
        $replaces = [
            '<NS>' => $namespace,
            '<CLASS>' => $class,
            '<DOCBLOCK>' => implode("\n", $docblockEntries),
            '<TRAIT>' => substr($values[$type]['trait'], (int)strrpos($values[$type]['trait'], "\\") + 1),
            '<TRAIT_NS>' => $values[$type]['trait'],
            '<MEMBERS>' => $values[$type]['members'],
        ];

        return str_replace(array_keys($replaces), array_values($replaces), $values[$type]['template']);
    }

    private function writeln(string $message): void
    {
        echo $message."\n";
    }

    private const CONSTANTS_TEMPLATE = <<<EOF
<?php
declare(strict_types=1);
namespace <NS>;

use <TRAIT_NS>;

/**
<DOCBLOCK>
 */
final class <CLASS>
{
    use <TRAIT>;

<MEMBERS>
}

EOF;

    private const DOCBLOCK_TEMPLATE = <<<EOF
<?php
declare(strict_types=1);
namespace <NS>;

use <TRAIT_NS>;

/**
<DOCBLOCK>
 */
final class <CLASS>
{
    use <TRAIT>;
}

EOF;

    private const STATIC_TEMPLATE = <<<EOF
<?php
declare(strict_types=1);
namespace <NS>;

use <TRAIT_NS>;

/**
<DOCBLOCK>
 */
final class <CLASS>
{
    use <TRAIT>;

    private static \$mapping = [
<MEMBERS>
    ];
}

EOF;
}
