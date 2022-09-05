<?php


namespace Pages;


class YandexSearchVideoPage
{
    const moviesLocator = "//div[@role='listitem']";
    const searchFieldLocator = "//form[@action='/video/search']//input[@type='text']";
    const searchButtonLocator = "//button[@type='submit'][//div[text()='Найти']]";

    private $I;
    private $leftBlock;

    public function __construct(\AcceptanceTester $I)
    {
        $this->I = $I;
        $this->leftBlock = new LeftVideoBlock($I);
    }

    /**
     * @param string $videoName
     */
    public function navigate($videoName){

        $path = $videoName === "" ? "video/" : "video/search?text=$videoName";

        $this->I->amOnPage($path);

        if ($videoName !== ""){

            $this->I->seeInTitle($videoName);
        }

        $this->I->seeInTitle("видео найдено в Яндексе");
        $this->waitLoadPage();
    }

    public function search($videoName){

        $this->I->fillField($this::searchFieldLocator, $videoName);
        $this->I->seeInField($this::searchFieldLocator, $videoName);
        $this->I->click($this::searchButtonLocator);

        $this->waitLoadPage();
    }

    public function leftVideoBlock(){

        return $this->leftBlock;
    }

    private function waitLoadPage(){

        $this->I->waitForJS("return $.active == 0;", 10);
    }
}