<?php
declare(strict_types=1);
SessionManager::webSessionDestroy();
msg_set("logged Out");
redir_loc("/");