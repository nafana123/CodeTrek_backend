<?php

namespace App\Service;

class CodeExecutionService
{
    private array $containers = [
        'javaScript' => 'user_code_node',
        'typeScript' => 'user_code_ts',
        'python' => 'user_code_python',
        'php' => 'user_code_php',
        'c++' => 'user_code_gcc',
        'c#' => 'user_code_dotnet',
        'java' => 'user_code_java',
        'go' => 'user_code_go',
    ];

    public function executeUserCode(string $code, string $language)
    {
        if (!isset($this->containers[$language])) {
            return [null, 'Неизвестный язык'];
        }

        $containerName = $this->containers[$language];

        switch ($language) {
            case 'javaScript':
                $command = sprintf(
                    'docker exec %s sh -c %s 2>&1',
                    escapeshellarg($containerName),
                    escapeshellarg('timeout 5s node -e ' . escapeshellarg($code))
                );
                break;

            case 'typeScript':
                $encodedCode = base64_encode($code);
                $innerCommand = sprintf(
                    'echo %s | base64 --decode > /tmp/script.ts && npm install -g typescript --silent && tsc /tmp/script.ts && timeout 5s node /tmp/script.js',
                    escapeshellarg($encodedCode)
                );
                $command = sprintf(
                    'docker exec %s sh -c %s 2>&1',
                    escapeshellarg($containerName),
                    escapeshellarg($innerCommand)
                );
                break;

            case 'python':
                $command = sprintf(
                    'docker exec %s sh -c %s 2>&1',
                    escapeshellarg($containerName),
                    escapeshellarg('timeout 5s python -c ' . escapeshellarg($code))
                );
                break;

            case 'php':
                $encodedCode = base64_encode($code);
                $innerCommand = sprintf(
                    'echo %s | base64 --decode | timeout 5s php',
                    escapeshellarg($encodedCode)
                );
                $command = sprintf(
                    'docker exec %s sh -c %s 2>&1',
                    escapeshellarg($containerName),
                    escapeshellarg($innerCommand)
                );
                break;

            case 'c++':
                $encodedCode = base64_encode($code);
                $innerCommand = sprintf(
                    'echo %s | base64 --decode | g++ -x c++ -o /tmp/a.out - && timeout 5s /tmp/a.out',
                    escapeshellarg($encodedCode)
                );
                $command = sprintf(
                    'docker exec %s sh -c %s 2>&1',
                    escapeshellarg($containerName),
                    escapeshellarg($innerCommand)
                );
                break;

            case 'c#':
                $encodedCode = base64_encode($code);
                $innerCommand = sprintf(
                    'echo %s | base64 --decode > /tmp/Program.cs && cd /tmp && timeout 5s dotnet run --nologo',
                    escapeshellarg($encodedCode)
                );
                $command = sprintf(
                    'docker exec %s sh -c %s 2>&1',
                    escapeshellarg($containerName),
                    escapeshellarg($innerCommand)
                );
                break;

            case 'java':
                $encodedCode = base64_encode($code);
                $innerCommand = sprintf(
                    'echo %s | base64 --decode > /tmp/Main.java && javac /tmp/Main.java && timeout 5s java -cp /tmp Main',
                    escapeshellarg($encodedCode)
                );
                $command = sprintf(
                    'docker exec %s sh -c %s 2>&1',
                    escapeshellarg($containerName),
                    escapeshellarg($innerCommand)
                );
                break;

            case 'go':
                $encodedCode = base64_encode($code);
                $innerCommand = sprintf(
                    'echo %s | base64 --decode > /tmp/main.go && timeout 5s go run /tmp/main.go',
                    escapeshellarg($encodedCode)
                );
                $command = sprintf(
                    'docker exec %s sh -c %s 2>&1',
                    escapeshellarg($containerName),
                    escapeshellarg($innerCommand)
                );
                break;

            default:
                return [null, 'Unsupported language'];
        }

        $output = shell_exec($command);

        return [$output, $output !== null && str_contains($output, 'Parse error') ? $output : ''];
    }
}
