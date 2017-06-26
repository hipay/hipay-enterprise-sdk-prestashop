cd example

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$(curl -q https://composer.github.io/installer.sig)') { echo 'Installer verified' . PHP_EOL; } else { echo 'Installer corrupt' . PHP_EOL;}"
php composer-setup.php
php -r "unlink('composer-setup.php');"

php composer.phar install

cd ..

npm install

echo ""
echo "Setup completed! Do not forget to add your HiPay Fullservice credentials into the example/credentials.php file."