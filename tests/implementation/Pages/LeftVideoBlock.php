<?php

namespace Pages;

use Imagick;
use function Codeception\Extension\codecept_log;

class LeftVideoBlock
{
    const movieLocator = "//div[@role='listitem'][.//*[starts-with(string(.),'{videoName}')]]//div[@class='thumb-image__shadow']";
    const moviesBlockLocator  = "//div[@role='list']";
    const posterErrorMessage  = "Постер на видео не изменился !";
    const previewVideoLocator = "video";

    private $topShift = 163;
    private $leftShift = 120;

    private $I;
    private $movieLocator;

    public function __construct(\AcceptanceTester $I)
    {
        $this->I = $I;
        $this->movieLocator = "//div[@role='listitem']//div[@class='thumb-image__shadow']";
    }

    public function saveScreenshot($fileName){

        $this->I->makeScreenshot($fileName);
    }

    public function saveScreenshotBlob(){

        return $this->I->makeScreenshotBlob();
    }

    public function findVideoByName($videoName){

        $this->movieLocator = str_replace("{videoName}", $videoName, self::movieLocator);

        $this->scrollWindowToVideo();
    }

    public function moveMouseOnVideo(){

        $this->I->moveMouseOver($this->movieLocator);
    }

    public function shouldBeStartVideoTrailer(){

        $this->I->assertTrue($this->waitVideoElement(), "У видео нет трейлера !");
        $this->I->assertTrue($this->waitForPlayPreview(), "Проигрывание трейлера не началось !");
    }
    public function shouldBeDeviationInVideoPoster($actualFileName, $expectedFileName){

        $deviation = $this->getImagesDeviation($actualFileName, $expectedFileName, $this->getElementRect());
        $this->I->assertTrue($this->compareDeviation($deviation),self::posterErrorMessage);
    }
    public function shouldBeDeviationInVideoPosterBlobs($actualImageBlob, $expectedImageBlob){

        $deviation = $this->getImagesDeviationByBlob($actualImageBlob, $expectedImageBlob, $this->getElementRect());
        $this->I->assertTrue($this->compareDeviation($deviation),self::posterErrorMessage);
    }

    private function getDeviation(Imagick $actualImage, Imagick $expectedImage, $rect){

        $width  = $rect[0];
        $height = $rect[1];

        $actualImage->cropImage($width, $height, $this->leftShift, $this->topShift);
        $expectedImage->cropImage($width, $height, $this->leftShift, $this->topShift);

        return $actualImage->getImageDistortion($expectedImage, Imagick::METRIC_MEANSQUAREERROR);
    }
    private function getElementRect(){

        $script =
        "var movieElement = document.evaluate(\"".$this->movieLocator."\", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue; ".
        "return movieElement.offsetWidth + ',' + movieElement.offsetHeight";

        $size = $this->I->executeJS($script);

        return explode(",", $size);
    }
    private function compareDeviation($deviation){

        return $deviation > 0.0;
    }
    private function waitVideoElement(){

        try {

            $this->I->waitForJS("return document.querySelector(\"".self::previewVideoLocator."\") !== null;", 10);

            return true;

        } catch (\Exception $ignore){

            return false;
        }
    }
    private function waitForPlayPreview(){

        $script =
        "var element = document.querySelector(\"".self::previewVideoLocator."\"); ".
        "return element.currentTime > 0.1 && ! element.paused && ! element.ended && element.readyState > 2;";

        try {

            $this->I->waitForJS($script, 30);

            return true;

        } catch (\Exception $ignore){

            return false;
        }
    }
    private function scrollWindowToVideo(){

        $this->topShift = 104;
        $endtime = microtime(true) + 5;

        do {

            try {

                $script =
                    "var movieElement = document.evaluate(\"".$this->movieLocator."\",document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue; ".
                    "movieElement.scrollIntoView(); ".
                    "window.scrollBy(0, -".$this->topShift."); " .
                    "return movieElement.getBoundingClientRect().top";

                $top = $this->I->executeJS($script);

            } catch (\Exception $ignore){

                $top = 0;
            }

        } while ($top !== $this->topShift && microtime(true) < $endtime);
    }
    private function getImagesDeviation($actualFileName, $expectedFileName, $rect){

        $pathToActualImage = Utils::getFullPath($actualFileName);
        $pathToExpectedImage = Utils::getFullPath($expectedFileName);

        $actualImage = new Imagick($pathToActualImage);
        $expectedImage = new Imagick($pathToExpectedImage);

        $deviation = $this->getDeviation($actualImage, $expectedImage, $rect);

        $actualImage->writeImage($pathToActualImage);
        $expectedImage->writeImage($pathToExpectedImage);

        return $deviation;
    }
    private function getImagesDeviationByBlob($actualImageBlob, $expectedImageBlob, $size){

        $actualImage = new Imagick();
        $expectedImage = new Imagick();

        // и за чем бросать в output весь imageBlob ? :_(
        $actualImage->readImageBlob($actualImageBlob);
        $expectedImage->readImageBlob($expectedImageBlob);

        return $this->getDeviation($actualImage, $expectedImage, $size);
    }
}