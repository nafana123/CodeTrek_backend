<?php

namespace App\Service;

class CodeExecutionService
{
    public function executeUserCode(string $code)
    {
        $tempFilePath = sys_get_temp_dir() . '/user_code_' . uniqid() . '.js';
        file_put_contents($tempFilePath, $code);

        $command = escapeshellcmd("node $tempFilePath");

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
            unlink($tempFilePath);

            return [$output, $error];
        }

        unlink($tempFilePath);
        return [null, 'Ошибка выполнения кода. Попробуйте позже.'];
    }
}