## MITM logging proxy

Small http proxy forwarding requests to configured remote and logging traffic to log. Remote base uri is passes by `ENV['mitm_remote']`.
 
Usage on windows:

    set mitm_remote=http://192.168.1.2/ && php -d variables_order=EGPCS -S 0.0.0.0:80 app.php
    
Usage on linux:
    
    export mitm_remote=http://192.168.1.2/ && php -d variables_order=EGPCS -S 0.0.0.0:80 app.php