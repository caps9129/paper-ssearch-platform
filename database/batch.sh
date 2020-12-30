echo 'caps4936' | sudo -S /volume1/@appstore/py3k/usr/local/bin/python3.8 /volume1/web/database/backend.py
echo 'caps4936' | sudo -S /volume1/@appstore/PHP7.2/usr/local/bin/php72 /volume1/web/database/parse2DB.php
echo 'caps4936' | sudo -S chmod -R 777 /volume1/web/
echo 'caps4936' | sudo -S find /volume1/web/ -type d -name "@eaDir" -print0 | xargs -0 rm -rf
echo 'caps4936' | sudo -S /volume1/@appstore/py3k/usr/local/bin/python3.8 /volume1/web/database/dict.py
echo 'caps4936' | sudo -S /volume1/@appstore/py3k/usr/local/bin/python3.8 /volume1/web/database/en.py
echo 'caps4936' | sudo -S /volume1/@appstore/py3k/usr/local/bin/python3.8 /volume1/web/summary.py
echo 'caps4936' | sudo -S /volume1/@appstore/py3k/usr/local/bin/python3.8 /volume1/web/database/check.py
