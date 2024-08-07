<?php

/**
 * Prompts the user with a question and returns their input.
 *
 * @param string $question The question to ask the user.
 * @param string $default The default answer if the user provides no input.
 * @return string The user's answer or the default value if no input is provided.
 */
function ask(string $question, string $default = ''): string
{
    $answer = readline($question . ($default ? " ({$default})" : null) . ': ');

    if (!$answer) {
        return $default;
    }

    return $answer;
}

/**
 * Prompts the user with a yes/no question and returns their confirmation.
 *
 * @param string $question The question to ask the user.
 * @param bool $default The default answer if the user provides no input (true for 'yes', false for 'no').
 * @return bool True if the user confirms (answers 'y'), false otherwise.
 */
function confirm(string $question, bool $default = false): bool
{
    $answer = ask($question . ' (' . ($default ? 'Y/n' : 'y/N') . ')');

    if (!$answer) {
        return $default;
    }

    return strtolower($answer) === 'y';
}

/**
 * Outputs a line of text followed by a newline character.
 *
 * @param string $line The line of text to output.
 */
function writeln(string $line): void
{
    echo $line . PHP_EOL;
}

/**
 * Executes a shell command and returns the trimmed output.
 *
 * @param string $command The shell command to execute.
 * @return string The trimmed output of the shell command.
 */
function run(string $command): string
{
    return trim((string) shell_exec($command));
}

/**
 * Returns the portion of a string after the last occurrence of a given search string.
 *
 * @param string $subject The string to search in.
 * @param string $search The string to search for.
 * @return string The portion of the string after the last occurrence of the search string.
 */
function str_after(string $subject, string $search): string
{
    $pos = strrpos($subject, $search);

    if ($pos === false) {
        return $subject;
    }

    return substr($subject, $pos + strlen($search));
}

/**
 * Converts a string into a URL-friendly "slug" by replacing non-alphanumeric characters with hyphens.
 *
 * @param string $subject The string to convert.
 * @return string The slugified string.
 */
function slugify(string $subject): string
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $subject), '-'));
}

/**
 * Converts a string to title case, removing spaces.
 *
 * @param string $subject The string to convert.
 * @return string The title-cased string.
 */
function title_case(string $subject): string
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $subject)));
}

/**
 * Converts a string to snake case, replacing hyphens and underscores with a specified character.
 *
 * @param string $subject The string to convert.
 * @param string $replace The character to replace hyphens and underscores with.
 * @return string The snake-cased string.
 */
function title_snake(string $subject, string $replace = '_'): string
{
    return str_replace(['-', '_'], $replace, $subject);
}

/**
 * Replaces occurrences of keys in a file with their corresponding values.
 *
 * @param string $file The path to the file.
 * @param array $replacements An associative array of replacements where the key is the string to find and the value is the string to replace it with.
 */
function replace_in_file(string $file, array $replacements): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        )
    );
}

/**
 * Removes a specified prefix from a string if it exists.
 *
 * @param string $prefix The prefix to remove.
 * @param string $content The string from which to remove the prefix.
 * @return string The string without the prefix.
 */
function remove_prefix(string $prefix, string $content): string
{
    if (str_starts_with($content, $prefix)) {
        return substr($content, strlen($prefix));
    }

    return $content;
}

/**
 * Removes specified development dependencies from the composer.json file.
 *
 * @param array $names An array of dependency names to remove.
 */
function remove_composer_deps(array $names): void
{
    $data = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);

    foreach ($data['require-dev'] as $name => $version) {
        if (in_array($name, $names, true)) {
            unset($data['require-dev'][$name]);
        }
    }

    file_put_contents(__DIR__ . '/composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

/**
 * Removes a specified script from the composer.json file.
 *
 * @param string $scriptName The name of the script to remove.
 */
function remove_composer_script(string $scriptName): void
{
    $data = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);

    foreach ($data['scripts'] as $name => $script) {
        if ($scriptName === $name) {
            unset($data['scripts'][$name]);
            break;
        }
    }

    file_put_contents(__DIR__ . '/composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

/**
 * Removes paragraphs marked with <!--delete--> tags from a README file.
 *
 * @param string $file The path to the README file.
 */
function remove_readme_paragraphs(string $file): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        preg_replace('/<!--delete-->.*<!--\/delete-->/s', '', $contents) ?: $contents
    );
}

/**
 * Safely deletes a file if it exists and is a file.
 *
 * @param string $filename The path to the file to delete.
 */
function safeUnlink(string $filename): void
{
    if (file_exists($filename) && is_file($filename)) {
        unlink($filename);
    }
}

/**
 * Determines the appropriate directory separator for a given path.
 *
 * @param string $path The path to process.
 * @return string The path with the appropriate directory separator.
 */
function determineSeparator(string $path): string
{
    return str_replace('/', DIRECTORY_SEPARATOR, $path);
}

/**
 * Replaces specific patterns in files for Windows OS.
 *
 * @return array An array of file paths that match the specified patterns.
 */
function replaceForWindows(): array
{
    return preg_split('/\\r\\n|\\r|\\n/', run('dir /S /B * | findstr /v /i .git\ | findstr /v /i vendor | findstr /v /i ' . basename(__FILE__) . ' | findstr /r /i /M /F:/ ":author :vendor :package VendorName skeleton migration_table_name vendor_name vendor_slug author@domain.com"'));
}

/**
 * Replaces specific patterns in files for all other OSes.
 *
 * @return array An array of file paths that match the specified patterns.
 */
function replaceForAllOtherOSes(): array
{
    return explode(PHP_EOL, run('grep -E -r -l -i ":author|:vendor|:package|VendorName|skeleton|migration_table_name|vendor_name|vendor_slug|author@domain.com" --exclude-dir=vendor ./* ./.github/* | grep -v ' . basename(__FILE__)));
}

/**
 * Gets the GitHub API endpoint response.
 *
 * @param string $endpoint The GitHub API endpoint to query.
 * @return stdClass|null The response from the GitHub API, or null if the request fails.
 */
function getGitHubApiEndpoint(string $endpoint): ?stdClass
{
    try {
        $curl = curl_init("https://api.github.com/{$endpoint}");
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: spatie-configure-script/1.0',
            ],
        ]);

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($statusCode === 200) {
            return json_decode($response);
        }
    } catch (Exception $e) {
        // ignore
    }

    return null;
}

/**
 * Searches Git commits for the GitHub username of the author.
 *
 * @return string The GitHub username of the author, or an empty string if not found.
 */
function searchCommitsForGitHubUsername(): string
{
    $authorName = strtolower(trim(shell_exec('git config user.name')));

    $committersRaw = shell_exec("git log --author='@users.noreply.github.com' --pretty='%an:%ae' --reverse");
    $committersLines = explode("\n", $committersRaw ?? '');
    $committers = array_filter(array_map(function ($line) use ($authorName) {
        $line = trim($line);
        [$name, $email] = explode(':', $line) + [null, null];

        return [
            'name' => $name,
            'email' => $email,
            'isMatch' => strtolower($name) === $authorName && !str_contains($name, '[bot]'),
        ];
    }, $committersLines), fn ($item) => $item['isMatch']);

    if (empty($committers)) {
        return '';
    }

    $firstCommitter = reset($committers);

    return explode('@', $firstCommitter['email'])[0] ?? '';
}

/**
 * Attempts to guess the GitHub username using the GitHub CLI.
 *
 * @return string The GitHub username, or an empty string if not found.
 */
function guessGitHubUsernameUsingCli(): string
{
    try {
        if (preg_match('/ogged in to github\.com as ([a-zA-Z-_]+).+/', shell_exec('gh auth status -h github.com 2>&1'), $matches)) {
            return $matches[1];
        }
    } catch (Exception $e) {
        // ignore
    }

    return '';
}

/**
 * Attempts to guess the GitHub username by searching commits and using the GitHub CLI.
 *
 * @return string The GitHub username, or an empty string if not found.
 */
function guessGitHubUsername(): string
{
    $username = searchCommitsForGitHubUsername();
    if (!empty($username)) {
        return $username;
    }

    $username = guessGitHubUsernameUsingCli();
    if (!empty($username)) {
        return $username;
    }

    // fall back to using the username from the git remote
    $remoteUrl = shell_exec('git config remote.origin.url');
    $remoteUrlParts = explode('/', str_replace(':', '/', trim($remoteUrl)));

    return $remoteUrlParts[1] ?? '';
}

/**
 * Attempts to guess the GitHub vendor information using the GitHub API.
 *
 * @param string $authorName The author's name.
 * @param string $username The GitHub username.
 * @return array An array containing the vendor name and username.
 */
function guessGitHubVendorInfo($authorName, $username): array
{
    $remoteUrl = shell_exec('git config remote.origin.url');
    $remoteUrlParts = explode('/', str_replace(':', '/', trim($remoteUrl)));

    $response = getGitHubApiEndpoint("orgs/{$remoteUrlParts[1]}");

    if ($response === null) {
        return [$authorName, $username];
    }

    return [$response->name ?? $authorName, $response->login ?? $username];
}

$gitName = run('git config user.name');
$authorName = ask('Author name', $gitName);

$gitEmail = run('git config user.email');
$authorEmail = ask('Author email', $gitEmail);
$authorUsername = ask('Author username', guessGitHubUsername());

$guessGitHubVendorInfo = guessGitHubVendorInfo($authorName, $authorUsername);

$vendorName = ask('Vendor name', $guessGitHubVendorInfo[0]);
$vendorUsername = ask('Vendor username', $guessGitHubVendorInfo[1] ?? slugify($vendorName));
$vendorSlug = slugify($vendorUsername);

$vendorNamespace = str_replace('-', '', ucwords($vendorName));
$vendorNamespace = ask('Vendor namespace', $vendorNamespace);

$currentDirectory = getcwd();
$folderName = basename($currentDirectory);

$packageName = ask('Package name', $folderName);
$packageSlug = slugify($packageName);
$packageSlugWithoutPrefix = remove_prefix('laravel-', $packageSlug);

$className = title_case($packageName);
$className = ask('Class name', $className);
$variableName = lcfirst($className);
$description = ask('Package description', "This is my package {$packageSlug}");

$useLaravelPint = confirm('Enable Laravel Pint?', true);
$useDependabot = confirm('Enable Dependabot?', true);
$useUpdateChangelogWorkflow = confirm('Use automatic changelog updater workflow?', true);

writeln('------');
writeln("Author     : {$authorName} ({$authorUsername}, {$authorEmail})");
writeln("Vendor     : {$vendorName} ({$vendorSlug})");
writeln("Package    : {$packageSlug} <{$description}>");
writeln("Namespace  : {$vendorNamespace}\\{$className}");
writeln("Class name : {$className}");
writeln('---');
writeln('Packages & Utilities');
writeln('Use Laravel/Pint     : ' . ($useLaravelPint ? 'yes' : 'no'));
writeln('Use Dependabot       : ' . ($useDependabot ? 'yes' : 'no'));
writeln('Use Auto-Changelog   : ' . ($useUpdateChangelogWorkflow ? 'yes' : 'no'));
writeln('------');

writeln('This script will replace the above values in all relevant files in the project directory.');

if (!confirm('Modify files?', true)) {
    exit(1);
}

$files = (str_starts_with(strtoupper(PHP_OS), 'WIN') ? replaceForWindows() : replaceForAllOtherOSes());

foreach ($files as $file) {
    replace_in_file($file, [
        ':author_name' => $authorName,
        ':author_username' => $authorUsername,
        'author@domain.com' => $authorEmail,
        ':vendor_name' => $vendorName,
        ':vendor_slug' => $vendorSlug,
        'VendorName' => $vendorNamespace,
        ':package_name' => $packageName,
        ':package_slug' => $packageSlug,
        ':package_slug_without_prefix' => $packageSlugWithoutPrefix,
        'Skeleton' => $className,
        'skeleton' => $packageSlug,
        'migration_table_name' => title_snake($packageSlug),
        'variable' => $variableName,
        ':package_description' => $description,
    ]);

    match (true) {
        str_contains($file, determineSeparator('src/Skeleton.php')) => rename($file, determineSeparator('./src/' . $className . '.php')),
        str_contains($file, determineSeparator('src/SkeletonServiceProvider.php')) => rename($file, determineSeparator('./src/' . $className . 'ServiceProvider.php')),
        str_contains($file, determineSeparator('src/Facades/Skeleton.php')) => rename($file, determineSeparator('./src/Facades/' . $className . '.php')),
        str_contains($file, determineSeparator('src/Commands/SkeletonCommand.php')) => rename($file, determineSeparator('./src/Commands/' . $className . 'Command.php')),
        str_contains($file, determineSeparator('database/migrations/create_skeleton_table.php.stub')) => rename($file, determineSeparator('./database/migrations/create_' . title_snake($packageSlugWithoutPrefix) . '_table.php.stub')),
        str_contains($file, determineSeparator('config/skeleton.php')) => rename($file, determineSeparator('./config/' . $packageSlugWithoutPrefix . '.php')),
        str_contains($file, 'README.md') => remove_readme_paragraphs($file),
        default => [],
    };
}

if (!$useLaravelPint) {
    safeUnlink(__DIR__ . '/.github/workflows/fix-php-code-style-issues.yml');
    safeUnlink(__DIR__ . '/pint.json');
}

if (!$useDependabot) {
    safeUnlink(__DIR__ . '/.github/dependabot.yml');
    safeUnlink(__DIR__ . '/.github/workflows/dependabot-auto-merge.yml');
}

if (!$useUpdateChangelogWorkflow) {
    safeUnlink(__DIR__ . '/.github/workflows/update-changelog.yml');
}

confirm('Execute `composer install` and run tests?') && run('composer install && composer test');

confirm('Let this script delete itself?', true) && unlink(__FILE__);
