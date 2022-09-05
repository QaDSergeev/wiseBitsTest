<?php

use Pages\Utils;
use Ramsey\Uuid\Uuid;
use Pages\YandexSearchVideoPage;

class YandexVideoPreviewCest
{
    const movieName = "ураган";

    private $fileToRemove = array();

    public function _after()
    {
        foreach ($this->fileToRemove as $file){

            if (file_exists($file)){

                unlink($file);
            }
        }
    }

    public function _failed()
    {
        //sleep(60);
    }

    public function isVideoHavePreviewOnPageCompareImageBlobs(AcceptanceTester $I)
    {
        $yandexSearchVideoPage = new YandexSearchVideoPage($I);
        $yandexSearchVideoPage->navigate(self::movieName);

        $yandexSearchVideoPage->leftVideoBlock()->findVideoByName("Ураган в Москве просто жесть");
        $pageScreenBlob1 = $yandexSearchVideoPage->leftVideoBlock()->saveScreenshotBlob();

        $yandexSearchVideoPage->leftVideoBlock()->moveMouseOnVideo();
        $yandexSearchVideoPage->leftVideoBlock()->shouldBeStartVideoTrailer();

        $pageScreenBlob2 = $yandexSearchVideoPage->leftVideoBlock()->saveScreenshotBlob();
        //лучше не запускать с -vvv :_(
        $yandexSearchVideoPage->leftVideoBlock()->shouldBeDeviationInVideoPosterBlobs($pageScreenBlob1, $pageScreenBlob2);
    }
    public function isVideoHavePreviewOnPageCompareImageFromFiles(AcceptanceTester $I)
    {
        $pageScreenWithPreview = Uuid::uuid4()->toString();
        $pageScreenWithoutPreview = Uuid::uuid4()->toString();
        $this->saveToRemoveFiles($pageScreenWithPreview, $pageScreenWithoutPreview);

        $yandexSearchVideoPage = new YandexSearchVideoPage($I);
        //Так быстрее
        $yandexSearchVideoPage->navigate(self::movieName);

        //Так медленнее
        //$yandexSearchVideoPage->navigate("");
        //$yandexSearchVideoPage->search(self::movieName);
        $yandexSearchVideoPage->leftVideoBlock()->findVideoByName("Ураган в Москве просто жесть");
        $yandexSearchVideoPage->leftVideoBlock()->saveScreenshot($pageScreenWithoutPreview);

        $yandexSearchVideoPage->leftVideoBlock()->moveMouseOnVideo();
        $yandexSearchVideoPage->leftVideoBlock()->shouldBeStartVideoTrailer();

        $yandexSearchVideoPage->leftVideoBlock()->saveScreenshot($pageScreenWithPreview);
        $yandexSearchVideoPage->leftVideoBlock()->shouldBeDeviationInVideoPoster($pageScreenWithoutPreview, $pageScreenWithPreview);
    }
    private function saveToRemoveFiles(... $fileNames){

        foreach ($fileNames as $name){

            array_push($this->fileToRemove, Utils::getFullPath($name));
        }
    }
}