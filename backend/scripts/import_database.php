<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$command = $argv[1] ?? 'all';
$supportedCommands = ['schema', 'seed', 'all'];

if (!in_array($command, $supportedCommands, true)) {
    fwrite(STDERR, "Unsupported command: {$command}" . PHP_EOL);
    fwrite(STDERR, 'Use one of: schema, seed, all' . PHP_EOL);
    exit(1);
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
$dsn = "mysql:host={$host};port={$port};charset=utf8mb4";

try {
    $pdo = new \PDO($dsn, $username, $password, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ]);
} catch (\Throwable $exception) {
    fwrite(STDERR, 'Failed to connect to MySQL: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

$files = match ($command) {
    'schema' => ['schema.sql'],
    'seed' => ['seed.sql'],
    default => ['schema.sql', 'seed.sql'],
};

try {
    foreach ($files as $fileName) {
        $filePath = __DIR__ . '/../database/' . $fileName;

        if (!is_file($filePath)) {
            throw new RuntimeException("SQL file not found: {$fileName}");
        }

        runSqlFile($pdo, $filePath, $fileName);
    }
} catch (\Throwable $exception) {
    fwrite(STDERR, 'Database import failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Database import completed successfully.' . PHP_EOL);
exit(0);

function runSqlFile(\PDO $pdo, string $filePath, string $fileName): void
{
    $sql = file_get_contents($filePath);

    if ($sql === false) {
        throw new RuntimeException("Unable to read SQL file: {$fileName}");
    }

    fwrite(STDOUT, "Running {$fileName}..." . PHP_EOL);

    foreach (splitSqlStatements($sql) as $statement) {
        if ($statement === '') {
            continue;
        }

        $pdo->exec($statement);
    }
}

function splitSqlStatements(string $sql): array
{
    $statements = [];
    $current = '';
    $inSingleQuote = false;
    $inDoubleQuote = false;
    $length = strlen($sql);

    for ($index = 0; $index < $length; $index++) {
        $character = $sql[$index];
        $nextCharacter = $index + 1 < $length ? $sql[$index + 1] : '';
        $previousCharacter = $index > 0 ? $sql[$index - 1] : '';

        if (!$inSingleQuote && !$inDoubleQuote) {
            if ($character === '-' && $nextCharacter === '-') {
                while ($index < $length && $sql[$index] !== "\n") {
                    $index++;
                }

                continue;
            }

            if ($character === '#') {
                while ($index < $length && $sql[$index] !== "\n") {
                    $index++;
                }

                continue;
            }
        }

        if ($character === "'" && !$inDoubleQuote && $previousCharacter !== '\\') {
            $inSingleQuote = !$inSingleQuote;
        } elseif ($character === '"' && !$inSingleQuote && $previousCharacter !== '\\') {
            $inDoubleQuote = !$inDoubleQuote;
        }

        if ($character === ';' && !$inSingleQuote && !$inDoubleQuote) {
            $trimmed = trim($current);

            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }

            $current = '';
            continue;
        }

        $current .= $character;
    }

    $trimmed = trim($current);

    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }

    return $statements;
}
