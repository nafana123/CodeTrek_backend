<?php

namespace App\Service;

class CodeExecutionService
{
    private array $containers = [
        'javaScript' => 'user_code_node',
        'typeScript' => 'user_code_ts', // Отдельный контейнер для TS
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
                $command = sprintf('docker exec %s node -e %s', escapeshellarg($containerName), escapeshellarg($code));
                break;

            case 'typeScript':
                $encodedCode = base64_encode($code);
                $command = sprintf(
                'docker exec %s sh -c "echo %s | base64 --decode > /tmp/script.ts && npm install -g typescript --silent && tsc /tmp/script.ts && node /tmp/script.js"',
                escapeshellarg($containerName),
                escapeshellarg($encodedCode)
            );
                break;


            case 'python':
                $command = sprintf('docker exec %s python -c %s', escapeshellarg($containerName), escapeshellarg($code));
                break;

            case 'php':
                $encodedCode = base64_encode($code);
                $command = sprintf('docker exec %s sh -c "echo %s | base64 --decode | php"', escapeshellarg($containerName), escapeshellarg($encodedCode));
                break;

            case 'c++':
                $encodedCode = base64_encode($code);
                $command = sprintf('docker exec %s sh -c "echo %s | base64 --decode | g++ -x c++ -o /tmp/a.out - && /tmp/a.out"', escapeshellarg($containerName), escapeshellarg($encodedCode));
                break;

            case 'c#':
                $encodedCode = base64_encode($code);
                $command = sprintf(
                    'docker exec %s sh -c "echo %s | base64 --decode > /tmp/Program.cs && dotnet run --nologo"',
                    escapeshellarg($containerName),
                    escapeshellarg($encodedCode)
                );
                break;

            case 'java':
                $encodedCode = base64_encode($code);
                $command = sprintf(
                    'docker exec %s sh -c "echo %s | base64 --decode > /tmp/Main.java && javac /tmp/Main.java && java -cp /tmp Main"',
                    escapeshellarg($containerName),
                    escapeshellarg($encodedCode)
                );
                break;

            case 'go':
                $encodedCode = base64_encode($code);
                $command = sprintf(
                    'docker exec %s sh -c "echo %s | base64 --decode > /tmp/main.go && go run /tmp/main.go"',
                    escapeshellarg($containerName),
                    escapeshellarg($encodedCode)
                );
                break;

            default:
                return [null, 'Unsupported language'];
        }

        $descriptorspec = [
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            proc_close($process);

            return [$output, $error];
        }

        return [null, 'Ошибка выполнения кода. Попробуйте позже.'];
    }
}
