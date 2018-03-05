<?php


namespace rollun\amazon\Installer;


use rollun\installer\Command;
use rollun\installer\Install\InstallerAbstract;

class MWSClientInstaller extends InstallerAbstract
{
    const AMAZON_CONFIG_PATH = "amazon/client/amazon-config.php";

    const AMAZON_CLIENT_LOG_PATH = "logs/amazon_client_log.txt";

    /**
     * @return string
     */
    protected function getConfigPath()
    {
        return Command::getDataDir() . static::AMAZON_CONFIG_PATH;

    }

    /**
     * @return string
     */
    protected function getLogPath()
    {
        return Command::getDataDir() . static::AMAZON_CLIENT_LOG_PATH;
    }

    /**
     * install
     * @return array
     */
    public function install()
    {
        $amazonLogFilePath = $this->getLogPath();
        if (!file_exists($amazonLogFilePath)) {
            $amazonLogDir = dirname($amazonLogFilePath);
            if (!file_exists($amazonLogDir)) {
                mkdir($amazonLogDir, 0777, true);
            }
            touch($amazonLogFilePath);
        }
        $amazonConfigFilePath = $this->getConfigPath();
        if (!file_exists($amazonConfigFilePath) || !$this->consoleIO->askConfirmation("Config is exist, Use it ?", true)) {
            $amazonLogDir = dirname($amazonConfigFilePath);
            if(!file_exists($amazonLogDir)) {
                mkdir(dirname($amazonConfigFilePath), 0777, true);
            }
            $storeName = $this->consoleIO->ask("Set store name: ");
            $merchantId = $this->consoleIO->ask("Set store merchantId: ");
            $marketplaceId = $this->consoleIO->ask("Set store marketplaceId: ");
            $keyId = $this->consoleIO->ask("Set store keyId: ");
            $secretKey = $this->consoleIO->ask("Set store secretKey: ");
            $serviceUrl = $this->consoleIO->ask("Set store serviceUrl: ");
            $MWSAuthToken = $this->consoleIO->ask("Set store MWSAuthToken: ");
            $data = "
<?php
use rollun\installer\Command;
\$store['$storeName']['merchantId'] = '$merchantId';//Merchant ID for this store
\$store['$storeName']['marketplaceId'] = '$marketplaceId'; //Marketplace ID for this store
\$store['$storeName']['keyId'] = '$keyId'; //Access Key ID
\$store['$storeName']['secretKey'] = '$secretKey'; //Secret Access Key for this store
\$store['$storeName']['serviceUrl'] = '$serviceUrl'; //optional override for Service URL
\$store['$storeName']['MWSAuthToken'] = '$MWSAuthToken'; //token needed for web apps and third-party developers
//Service URL Base
//Current setting is United States
\$AMAZON_SERVICE_URL = 'https://mws.amazonservices.com/';

//Location of log file to use
\$logpath = Command::getDataDir() .'logs/amazon_client_log.txt';

//Name of custom log function to use
\$logfunction = '';

//Turn off normal logging
\$muteLog = false;
";
            file_put_contents($amazonConfigFilePath, $data);
        }
        return [];
    }

    /**
     * Clean all installation
     * @return void
     */
    public function uninstall()
    {
    }

    /**
     * Return string with description of installable functional.
     * @param string $lang ; set select language for description getted.
     * @return string
     */
    public function getDescription($lang = "en")
    {
        return "Create amazon mws client credential config.";
    }

    /**
     * @return bool
     */
    public function isInstall()
    {
        $amazonConfigFilePath = $this->getConfigPath();
        $amazonLogFilePath = $this->getLogPath();
        return (file_exists($amazonConfigFilePath) && file_exists($amazonLogFilePath));
    }
}