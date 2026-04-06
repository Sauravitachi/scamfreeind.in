<?php

return [
    // Disable the pager completely by using 'cat' or false
    'pager' => 'cat',
    
    // Explicitly disable features that might use shell_exec
    'useReadline' => false,
    'usePcntl' => false,
    'updateCheck' => 'never',
    'startupMessage' => '', // Avoid triggering messages that might call shell
    
    // Some versions of PsySh use shell_exec to find the terminal width
    'terminalWidth' => 80,
    'terminalHeight' => 24,
];
