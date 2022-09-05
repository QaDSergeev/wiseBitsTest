<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module\WebDriver;

use Facebook\WebDriver\Remote\DriverCommand;

class ChromeWebDriver extends WebDriver
{
    public function makeScreenshotBlob()
    {
        if (!isset($this->webDriver)) {
            $this->debug('WebDriver::_saveScreenshot method has been called when webDriver is not set');
            return false;
        }
        try {

            return base64_decode($this->webDriver->execute(DriverCommand::SCREENSHOT), true);

        } catch (\Exception $e) {

            $this->debug('Unable to retrieve element screenshot from Selenium : ' . $e->getMessage());
        }
        return false;
    }

    public function makeElementScreenshotBlob($selector)
    {
        if (!isset($this->webDriver)) {
            $this->debug('WebDriver::_saveElementScreenshot method has been called when webDriver is not set');
            return false;
        }
        try {

            $webdriverElement = $this->matchFirstOrFail($this->webDriver, $selector);
            return base64_decode($this->webDriver->execute(DriverCommand::TAKE_ELEMENT_SCREENSHOT, [':id' => $webdriverElement->getID()]),true);

        } catch (\Exception $e) {

            $this->debug('Unable to retrieve element screenshot from Selenium : ' . $e->getMessage());
        }

        return false;
    }
}
