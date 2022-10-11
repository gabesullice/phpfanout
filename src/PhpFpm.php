<?php

declare(strict_types=1);

namespace GabeSullice\PhpFanout;

final class PhpFpm {

    /**
     * @var resource
     */
    protected $procResource;

    protected int $pid;

    public function __construct(
        protected string $sockFile,
    ) {}

    public function start(): void {
        $pid_file = tempnam(sys_get_temp_dir(), 'phpfanout');
        $conf_file = tempnam(sys_get_temp_dir(), 'phpfanout');
        $log_file = tempnam(sys_get_temp_dir(), 'phpfanout');
        printf("Log file: %s\n", $log_file);
        $conf = fopen($conf_file, 'rw+');
        fwrite($conf, "[global]\n");
        fwrite($conf, sprintf("pid = %s\n", $pid_file));
        fwrite($conf, sprintf("error_log = %s\n", $log_file));
        //fwrite($conf, "error_log = syslog\n");
        fwrite($conf, "[fanout]\n");
        fwrite($conf, sprintf("listen = %s\n", $this->sockFile));
        fwrite($conf, "pm = ondemand\n");
        fwrite($conf, "pm.max_children = 32\n");
        fclose($conf);
        $cmd = sprintf('php-fpm --no-php-ini --nodaemonize --fpm-config %s', $conf_file);
        $pipes = [];
        $this->procResource = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
        while (empty($this->pid)) {
            $this->pid = (int) file_get_contents($pid_file);
            sleep(1);
        }
    }

    public function stop(): void {
        posix_kill($this->pid, SIGQUIT);
        proc_close($this->procResource);
    }

}
